<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/11/2016
 * Time: 15:22
 */

$GLOBALS['fabMediaContext'] = 'Wiki';

if (!$core->adminBootCheck())
    die("Check not passed");

require_once ($conf['path']['baseDir'] . '/modules/wiki/lib/class_wiki.php');
$fabwiki = new wiki();

$this->addJsFile($URI->getBaseUri(true) . 'modules/fabmediamanager/js/plupload/plupload.full.min.js', false);
$this->addJsFile($URI->getBaseUri(true) . 'modules/fabmediamanager/js/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js', false);

echo '<link rel="stylesheet" type="text/css" href="' . $URI->getBaseUri(true)  . 'modules/fabmediamanager/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css">';

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Editor', 'admin.php?module=wiki&op=editor');

$template->sidebar.= $template->simpleBlock('Quick op.', '&bull;<a href="admin.php?module=wiki">Wiki</a> <br/>&bull;<a href="admin.php?module=wiki&op=editor">Editor</a> <br/> ');

$template->sidebar .= $template->simpleBlock('Statistics', '<div id="statistics"></div>');

// Loads buttons
foreach (glob( $conf['path']['baseDir'] . '/modules/wiki/editor_plugins/*.php' ) as $filename) {
    require_once $filename;
}
 $editorCustomButtons = '';
foreach ($editorPlugins['buttons'] AS $pgName => $pgContent) {

    $pgToolbar .= $pgName . ' | ';
    $pgContent = str_replace(PHP_EOL, '', $pgContent);
    $editorCustomButtons .= '
        editor.ui.registry.addButton(\'' . $pgName .'\', {
            text: \''.  $pgName. '\',
            onAction: () => {
                tinymce.activeEditor.execCommand(\'InsertHTML\', false, \'' . $pgContent . '\');
            }
        });
    ';
}

// Get latest 10 pages
$query = 'SELECT P.ID, 
                 P.title,
                 P.language 
          FROM ' . $db->prefix . 'wiki_pages AS P
          ORDER BY P.ID DESC
          LIMIT 10';

if (!$result = $db->query($query)) {
    $latestPages = '<pre>' . $query . '</pre>';
} else{
    if (!$db->affected_rows){
        $latestPages = 'No latest pages';
    } else {
        while ($row = mysqli_fetch_assoc($result)){
            $latestPages .= '&bull; <a target="_blank" href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['title'] . ' (' . $row['language'] . ')</a> <br/>';
        }
    }
}

$template->sidebar .= $template->simpleBlock('Latest pages', $latestPages);

// Get custom files extension
$query = '
SELECT * 
FROM ' . $db->prefix . 'fabmedia_custom_filetypes
WHERE module = \'wiki\'
    AND enabled = 1;';

$result = $db->query($query);
$allowableExtensions =
    '{title : "Image files", extensions : "jpeg,jpg,gif,png"},
	 {title : "Zip files"  , extensions : "zip"}';

if ($db->affected_rows){
    $allowableExtensions .= ',' . "\r";
    while ($row = mysqli_fetch_array($result)){
        $customFiles .= '{title : "' . $row['extension'] .' files", extensions : "' . $row['extension'] . '"}, ' . "\r";
    }

    $customFiles = substr($customFiles,0, -2);
}

if (isset($_GET['ID'])) {
    $ID = (int) $_GET['ID'];

    $query = 'SELECT 
                    P.title,
                    P.category_ID,
                    P.status_ID,
                    P.title_alternative,
                    P.use_file,
                    P.language,
                    P.content,
                    P.keywords,
                    P.internal_redirect,
                    P.metadata_description,
                    P.short_description,
                    P.visible_from_date,
                    P.visible_to_date,
                    P.visible,
                    P.additional_data,
                    P.visible,
                    P.no_editor,
                    P.no_index,
                    P.no_search,
                    P.full_page,
                    P.no_banner,
                    P.no_comment,
                    P.no_toc,
                    P.no_similar_pages,
                    P.no_info,
                    P.no_linking_pages,
                    P.no_title,
                    P.service_page,
                    P.image,
                    P.image_ID,
                    P.featured_video_ID,
                    P.notes,
                    M.ID AS master_ID,
                    L.ID AS license_ID,
                    L.name AS license_name 
              FROM ' . $db->prefix . 'wiki_pages AS P
              LEFT JOIN ' . $db->prefix . 'wiki_masters AS M
                ON P.master_ID = M.ID
              LEFT JOIN ' . $db->prefix . 'licenses_licenses AS L
                ON L.ID = P.license_ID
              WHERE P.ID = ' . $ID . '
              LIMIT 1';

    if (!$result = $db->query($query)) {
        echo '<pre>Query error: ' . $query . '</pre>';
        return;
    }

    if (!$db->affected_rows) {
        echo 'No page';
        return;
    }

    $row = mysqli_fetch_assoc($result);

    // Get tags associated
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_tags 
              WHERE page_ID = ' . $ID . ' 
              ORDER BY ID ASC;';

    if (!$resultTags = $db->query($query)) {
        echo 'Query error while selecting tags';
        return;
    } else {
        $tags = '';
        while ($rowTags = mysqli_fetch_array($resultTags)){
            $tags .= $rowTags['tag'] . ', ';
        }
        $tags = substr($tags, 0, -2);
    }

    // Get internal tags associated
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_internal_tags 
              WHERE page_ID = ' . $ID . ' 
              ORDER BY ID ASC;';

    if (!$resultTags = $db->query($query)) {
        echo 'Query error while selecting internal tags';
        return;
    } else {
        $internalTags = '';
        while ($rowTags = mysqli_fetch_array($resultTags)) {
            $internalTags .= $rowTags['tag'] . ', ';
        }
        $internalTags = substr($internalTags, 0, -2);
    }


    // Get keywords associated
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_keywords 
              WHERE page_ID = ' . $ID . ' ORDER BY `ID` ASC;';

    if (!$resultKeywords = $db->query($query)){
        echo 'Query error while selecting keywords.' . $query;
        return;
    } else {
        $keywords = '';
        while ($keywordTags = mysqli_fetch_array($resultKeywords)){
            $keywords .= $keywordTags['keyword'] . ', ';
        }
        $keywords = substr($keywords, 0, -2);
    }

    // Get seo keywords
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_seo 
              WHERE page_ID = ' . $ID . ' 
              ORDER BY ' . $db->prefix . 'wiki_pages_seo.order;';

    

    if (!$resultKeywords = $db->query($query)){
        echo 'Query error while selecting SEO keywords.' . $query;
        return;
    } else {
        $seoKeywords = '';
        while ($keywordTags = mysqli_fetch_array($resultKeywords)){
            $seoKeywords .= $keywordTags['keyword'] . ', ';
        }
        $seoKeywords = substr($seoKeywords, 0, -2);
    }

    echo $seoKeywords;

    $query = 'SELECT date
              FROM ' . $db->prefix . 'stats
              WHERE is_bot = 1
                AND module = \'wiki\'
                AND submodule = \'pageView\'
                AND (bot LIKE \'%google%\' OR bot LIKE \'%mediapartner%\')
                ORDER BY ID DESC
              LIMIT 1;';

    

    if (!$resultGoogle = $db->query($query)){
        echo $query;
        return;
    }

    if (!$db->affected_rows){
        $googleLast = 'No hit';
    } else {
        $rowGoogle = mysqli_fetch_assoc($resultGoogle);
        $googleLast = $rowGoogle['date'];
    }

    echo '<script type="text/javascript">
            var stopSave = false;
            var crudType = "update";
            var editor;
            var ID = ' . ( (int) $_GET['ID'] ) . ';
            var noEditor = ' . ( (int) $row['no_editor'] === 1 ? 'true' : 'false'  ) . ';
            
            var titleOriginal  = "' . $row['title'] . '";
            $(function () {
                updateMultilangStatus( ' . $row['master_ID'] . ', "' . $row['language'] . '");
            });
          </script>';
} else {
    echo '<script type="text/javascript">
            var titleOriginal = "";
            var stopSave = false;
            var noEditor = false;
            var editor;
            
            crudType = "insert";
            ID = null;
          </script>';
}


// Get all the license associated
$query = 'SELECT * 
          FROM ' . $db->prefix . 'licenses_licenses
          WHERE lang = \'' . $core->shortCodeLang . '\';';


if (!$resultLicenses = $db->query($query)){
    echo 'Query error: ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'Please set licenses first.';
    return;
}

$licenseSelect = '<select class="form-select" id="license">';
while ($rowSelect = mysqli_fetch_array($resultLicenses)){
    $licenseSelect .= '<option ' . ($rowSelect['ID'] === $row['license_ID'] ? 'selected="selected"' : '')  . '  value="' . $rowSelect['ID'] . '">' . $rowSelect['name'] . '</option>';
}
$licenseSelect .= '</select>';

$template->sidebar .= $template->simpleBlock('Global stats', '<div id="globalStats">Stats are being generated</div>');

$template->sidebar .= $template->simpleBlock('SEO keywords', '<div><input id="seoKeywords" value="' . $seoKeywords . '"><div id="seoStatus"></div></div>');
$template->sidebar .= $template->simpleBlock('Article image', '
    <div id="divArticleImage">
        <div id="divArticleImageContainer">
            <img class="img-fluid" src="' . $URI->getBaseUri(true) . $row['image'] . '" />
        </div>
        <input type="text" id="articleImageID" value="' . $row['image_ID'] . '"/>
        <input type="text" id="articleImage" value="' . $row['image'] . '"/>
    </div>');

$template->sidebar .= $template->simpleBlock('Search engine scan', '<strong>Google hit</strong>: ' . $googleLast);

$this->addJsFile('https://cdn.tiny.cloud/1/uhoydmhzumww33r04peevya5z8riovh2l8jyf0dbcgarstnr/tinymce/6/tinymce.min.js', true);
/*
 * Category select
 */
$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_categories_details';



if (!$resultCategory = $db->query($query)){
    echo 'Query error.';
    return;
}

if (!$db->affected_rows){
    echo 'No row. Please configure categories first.';
    return;
}

$categorySelect = '<select class="form-select" id="category">';
while ($rowCategory = mysqli_fetch_array($resultCategory)){
    $categorySelect .= '<option ' . ( (int) $rowCategory['ID'] === (int) $row['category_ID'] ? 'selected="selected"' : '')  . '  value="' . $rowCategory['ID'] . '">' . $rowCategory['name'] . '</option>';
}
$categorySelect .= '</select>';

$languageSelect = '<select class="form-select" onchange="checkTitle();" id="language">';

foreach ($conf['langAllowed'] as $language){
    $languageSelect .= '<option value="' . $language . '" ' . ($language === $row['language'] ? 'selected="selected"' : '') . '>' . $language . '</option>';
}

$languageSelect .= '</select>';

/*
 * Status
 */
$query = 'SELECT * FROM ' . $db->prefix . 'wiki_pages_status';

if (!$resultStatus = $db->query($query)){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No rows on status';
    return;
}
$statusSelect = '<select class="form-select" id="status">';
while ( $rowStatus = mysqli_fetch_assoc($resultStatus) ) {
    $statusSelect .= '<option value="' . $rowStatus['ID'] . '" ' . ( (int) $row['status_ID'] === (int) $rowStatus['ID'] ? ' selected ' : '' ) . '>' . $rowStatus['status'] . '</option>';
}
$statusSelect .= '</select>';


/*
 * STATS
 */

if (isset($_GET['ID']))
{
    $query = '
SELECT SUM(DAILY.HITS) AS total, 
       YEAR(DAILY.date) as year, 
       MONTH(DAILY.date) AS month
FROM ' . $db->prefix . 'stats_daily DAILY
WHERE DAILY.IDX = ' . $ID . '
AND DAILY.date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
AND DAILY.is_bot!= 1
GROUP BY DAILY.IDX, 
      YEAR(DAILY.date), 
      MONTH(DAILY.date);';

    

    if (!$resultStats = $db->query($query)){
        echo 'Query error! <pre>' . $query . '</pre>';
        return;
    }

    if (!$db->affected_rows){
        $tabStats .= 'No statistics';
    } else {
        $statsMax = 0;
        $statsJsData = 'var data = [';

        while ($rowStats = mysqli_fetch_assoc($resultStats)) {
            if ($statsMax < $rowStats['total'])
                $statsMax = $rowStats['total'];

            $statsJsData .= '
                 {
                 label: "' . $rowStats['month'] . '-' . substr( $rowStats['year'],-2) . '",
				 value: ' .  $rowStats['total']  . ',
				 style: "rgba(255, 120, 120, 0.5)"
				 },';
        }

        $statsJsData .= '];
   			  
    var range = {min:0, max: ' . $statsMax . '};
    var CHART_PADDING = 20;
    var wid;
    var hei;
    
    var chartYData = [
                  {label:"' .  $statsMax . '", value:' . $statsMax . '},';

        $statsStep = $statsMax / 10;

        for ($i = 1; $i < 10; $i++ ) {
            $statsValue = floor($statsMax - ($statsStep * $i));
            $statsJsData .= '{label:"' . $statsValue . '", value:' . $statsValue . '},' . PHP_EOL;
        }

        $statsJsData .= '];';
    }
} else {
    $tabStats = '<br/>No stats';
}



echo '
<script type="text/javascript">
' . $statsJsData . '

    var isModified = false;
    var richEditor;
    
    window.onbeforeunload = function() {
        return isModified ? "Are you sure you want to navigate away? " + isModified : null;
    }
</script>

<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Main</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Seo</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Stats</a>
  </li>
</ul>

<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">


        <div class="row ">
            <div class="col-md-12">
                <div class="FabCMS-adminDefaultPanel">
                <form class="form-horizontal">
                  
                  <div class="form-group">
                      <div class="row">  
                            <label class="control-label col-sm-1" for="title">Title</label>
                            <div class="col-sm-4">
                              <input type="text" class="form-control form-control-sm triggerModify" id="title" onkeyup="checkTitle();" placeholder="title" value="' . $row['title'] . '">
                            </div>
                            

                            
                            <label class="control-label col-sm-1" for="title">Language</label>
                            <div class="col-sm-1">
                              ' . $languageSelect . '
                            </div>
                
                            <label class="control-label col-sm-1" for="internalRedirect">Redirect</label>
                            <div class="col-sm-3">
                              <input type="text" class="form-control form-control-sm triggerModify" id="internalRedirect" placeholder="Internal redirect" value="' . $row['internal_redirect'] . '">
                            </div>
                             
                            <div class="col-sm-2" id="masterStatus">
                                
                            </div>
                        </div>
                  </div>
                  <hr/>
                  <div class="form-group mt-4">
                    <div class="row">
                        <label class="control-label col-sm-1" for="tags">Tags</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control form-control-sm triggerModify" id="tags" placeholder="tags" value="' . $tags. '">
                        </div>
                     
                        <label class="control-label col-sm-1" for="internalTags">Internal tags</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control form-control-sm triggerModify" id="internalTags" placeholder="Internal tags" value="' . $internalTags. '">
                        </div>
                        
                        <label class="control-label col-sm-1" for="keywords">Keywords</label>
                        <div class="col-sm-4">
                          <input type="text" class="form-control form-control-sm triggerModify" id="keywords" placeholder="keywords" value="' .$keywords. '">
                        </div>
                        <!--
                        <label class="control-label col-sm-1" for="startDate">Visible from</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control form-control-sm triggerModify" id="startDate" placeholder="Start date" value="' . $row['visible_from_date'] . '">
                        </div>
                        
                        <label class="control-label col-sm-1" for="endDate">Visible to</label>
                        <div class="col-sm-2">
                          <input type="text" class="form-control form-control-sm triggerModify" id="endDate" placeholder="End date" value="' . $row['visible_to_date'] . '">
                        </div>
                        -->
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <div class="row">
                        <label class="control-label col-sm-1" for="metadataDescrition">Description</label>
                        <div class="col-sm-10">
                          <textarea onkeydown="countCharDescription();" type="text" class="form-control form-control-sm" id="metaDataDescription" placeholder="Metadata description" >' . $row['metadata_description'] . '</textarea>
                        </div>
                        <div class="col-sm-1" id="countCharDescription"></div>
                     </div>
                  </div>
                  
                  <div class="row mt-4">
                    <div class="col-md-4">       
                          <div class="form-check">
                            <input type="checkbox" class="form-check-input triggerModify"  onpress="highlightVisibility();" id="visible" value="true" ' . ( (int) $row['visible'] === 1 ? 'checked="checked"' : '' ) . 'value="">
                            <label class="form-check-label" for="visible">Visible</label>
                          </div>
                          
                    </div>
                    
                    <div class="col-md-4">     
                       <div class="form-group" id="divGroup">
                            <div class="row">
                                <label class="control-label col-sm-3" for="license">
                                    <a target="_blank" href="admin.php?module=licenses">License</a>
                                </label>
                                <div class="col-sm-8">
                                    ' . $licenseSelect . '
                                </div>
                            </div>     
                      </div>          
                    </div>
                    
                    <div class="col-md-4">     
                       <div class="form-group" id="divGroup">
                            <div class="row">
                                <div class="col-sm-5">
                                    ' . $statusSelect . '
                                </div>
                            </div>     
                      </div>          
                    </div>
                  </div>
                  
                  </div>
                  
                  <div class="row">
                    <div class="col">
                                <button type="button" onclick="jumpToPage(0);" class="btn btn-default float-end" >
                                <i style="font-size: 1.5em" class="bi bi-eye"></i>
                                </button>
                    </div>
                    <div class="col">
                                <button type="button" onclick="jumpToPage(1);" class="btn btn-default float-end" >
                                <i style="font-size: 1.5em" class="bi bi-printer"></i>
                                </button>
                    </div>
                  </div> 
                  
                  <ul class="nav nav-tabs" id="FabWiki" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="contentHome-tab" data-bs-toggle="tab" href="#contentHome" role="tab" aria-controls="contentHome" aria-selected="true">Content</a>
                    </li>
                    <li>
                        <a class="nav-link" id="contentAdvanced-tab" data-bs-toggle="tab" href="#contentAdvanced" role="tab" aria-controls="home" aria-selected="false">Advanced</a>
                    </li>
                    <li>
                        <a class="nav-link" id="contentNotes-tab" data-bs-toggle="tab" href="#contentNotes" role="tab" aria-controls="home" aria-selected="false">Notes</a>
                    </li>
                  </ul>
        
                  <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="contentHome" role="tabpanel" aria-labelledby="contentHome-tab">
                        <p>
                          
                          <div class="row">
                            <div class="col-md-8">
                              <div class="form-group">            
                              <small>Short description</small>
                                <div class="col-sm-12" id="contentWrapper">
                                 <textarea class="form-control form-control-sm" id="short_description" rows="4" style="height:120px;">' . $row['short_description'] . '</textarea>              
                                </div>
                              </div>
                            </div>
                            <div class="col-md-3">
                                <div>
                                    Featured video: <input disabled id="featured_video_ID" class="form-control form-control-sm" value="' . $row['featured_video_ID'] . '" type="text">
                                </div>
                                <div style="max-height: 220px" id="featuredVideoSnippet"></div>
                            </div>
                          </div>
                         <small>Article</small>
                          <div class="form-group">            
                            <div class="col-sm-12" id="contentWrapper">
                             <textarea class="form-control richText" id="content" rows="4" style="height:300px;">' . $row['content'] . '</textarea>              
                            </div>
                          </div>
                  
                        </p>
                    </div>
                    
                    <div class="tab-pane fade" id="contentAdvanced" role="tabpanel" aria-labelledby="contentAdvanced-tab">
                        <h3>Additional data</h3>
<p>               
  Title alternative: 
  <input type="text" class="form-control triggerModify" id="title_alternative" value="' . $row['title_alternative'] . '">
</p>

<p>               
  Use file: 
  <input type="text" class="form-control triggerModify" id="use_file" value="' . $row['use_file'] . '">
</p>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_index" value="true" ' . ( (int) $row['no_index'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_index">No index</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_editor" value="true" ' . ( (int) $row['no_editor'] == 1 ? 'checked="checked"' : '' ) . ' onchange="switchEditor();">
  <label class="form-check-label" for="no_editor">No Editor</label>
</div>

<!-- Ripeti questo blocco per ogni checkbox -->
<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="service_page" value="true" ' . ( (int) $row['service_page'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="service_page">Service page</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_search" value="true" ' . ( (int) $row['no_search'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_search">No Search</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="full_page" value="true" ' . ( (int) $row['full_page'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="full_page">Full page</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_banner" value="true" ' . ( (int) $row['no_banner'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_banner">No banner</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_info" value="true" ' . ( (int) $row['no_info'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_info">No info</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_linking_pages" value="true" ' . ( (int) $row['no_linking_pages'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_linking_pages">No Linking pages</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_comment" value="true" ' . ( (int) $row['no_comment'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_comment">No Comment</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_toc" value="true" ' . ( (int) $row['no_toc'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_toc">No TOC</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_similar_pages" value="true" ' . ( (int) $row['no_similar_pages'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_similar_pages">No similar pages</label>
</div>

<div class="form-check">
  <input class="form-check-input triggerModify" type="checkbox" id="no_title" value="true" ' . ( (int) $row['no_title'] == 1 ? 'checked="checked"' : '' ) . '>
  <label class="form-check-label" for="no_title">No title</label>
</div>

<div class="form-group">            
  <div class="col-sm-12">
    <textarea class="form-control triggerModify" id="additionalData" rows="4">' . $row['additional_data'] . '</textarea>              
  </div>
</div>
                    </div>
        
                    <div class="tab-pane fade" id="contentNotes" role="tabpanel" aria-labelledby="contentAdvanced-tab">
                        <h3>Notes</h3>
                        <textarea class="form-control richText" id="notes" rows="4" style="height:300px;">' . $row['notes'] . '</textarea>   
                    </div>
                </div>
                          
                  <div class="row">
                    <div class="col-sm-6" id="crudStatus"></div>
                    <div class="col-sm-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" value="1" type="checkbox" id="minorUpdate">
                            <label class="form-check-label" for="minorUpdate">Minor update</label>
                        </div>
                    </div>
                    <div class="col-sm-3">
                      
                      <button onclick="save();" type="button" class="btn btn-primary">Update</button>
                    </div>
                  </div>
            </form>
            
            </div>
        </div>
        
        <div class="clearfix">
            <div class="float-right cleared mt-3">
                <span onclick="$(\'#pagePanel\').toggle();">Close/Open</span>
            </div>
        </div>
        
        <div class="row" style="border-top: 4px solid gray; border-bottom: 4px solid gray; padding-top: 8px; padding-bottom: 8px;;">
            <div class="col-md-12">
                <div class="mt-3" id="FabMedia">
                   <p>
                      <div id="FabMediaArea">MEDIAMANAGER</div>
                    </p>
                </div>
            </div>
        </div>
        
        <div id="pagePanel" class="FabCMS-adminDefaultPanel">
            <div class="row">
                
                <div class="col-md-3">
                    <h3>Outgoing links</h3>
                    <div id="outgoingLinks"></div>
                </div>
                
                <div class="col-md-3"><h3>Ingoing links</h3>
                    <div id="ingoingLinks"></div>
                </div>
                
                <div class="col-md-3">
                    <h3>Page search</h3>
                    Title: <input onkeydown="pageSearch();" type="text" id="pageSearchTitle">
                    <div id="pageSearchResult"></div>
                </div>
                
                <div class="col-md-3">
                    <h3>Unlinked pages</h3>
                    <div id="unlinkedPages"></div>
                </div>
                
            </div>
        </div>
        

  </div>
  
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">SEO</div>  
  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
  <h2>Stats</h2> 
  
  <canvas id="pageStat" width="800px" height="500px"></canvas>
  ' . $tabStats . '
  
  </div>

</div>
        ';

$theScript = /** @lang ECMAScript 6 */
    '
$(function() {
    console.log("Enterging post load loop");
    globalStats();
    FabMedia();
    initStats();
    ' . (isset($ID) ? 'seoStatus()' : '') .';

})

function globalStats() 
{
    console.log("Checking for global stats");    
    $.post( "admin.php?module=wiki&op=globalStats", {})
        .done(function( data ) {
            console.log("Got global stats");    
            $("#globalStats").html(data);
        }  
    );
}

function seoStatus() 
{
    console.log("Checking for seo statuss");    
    $.post( "admin.php?module=wiki&op=seoStatus", {ID : ' . (isset($ID) ? $ID : '0') .'})
        .done(function( data ) {
            console.log("Got seo status");    
            $("#seoStatus").html(data);
        }  
    );
}

function FabMedia()
{
    $.ajax({
        type: "POST",
        url: "' . $URI->getBaseUri() . 'fabmediamanager/init/wiki/",
        data: "customButton=name||action||title||icon",
        success: function(msg){
            $("#FabMedia").html(msg);
        }
    });
}

function fabMediaInit(){
	$(function() {
		// Setup html5 version
		$("#html5_uploader").pluploadQueue({
			// General settings
			runtimes : \'html5\',
			url : "' . $URI->getBaseUri() . 'fabmediamanager/upload/wiki/",
			// Removed chunk_size : \'10mb\',
			unique_names : true,
			rename: true,
			multiple_queues: true,
			filters : {
			max_file_size : \'400mb\',
				mime_types: [
                ' . $customFiles . ' 
				]
			},
		});
	});
}

function jumpToPage(type) {
    title = $("#title").val();
    language = $("#language").val();
    
    if (title.length == 0)
        return;
    
    if (type === 1){
        $params = "?printable=true&renderType=1"
    } else {
        $params = "";
    }
    
        
    $.post( "admin.php?module=wiki&op=getTrackback", { title:  title, language: language})
        .done(function( data ) {
             var url = data;
             window.open(url + $params, \'_blank\');
        }  
    );    
}

$( function() {
    
   $( "#startDate, #endDate" ).datepicker({dateFormat: \'yy-mm-dd\'});
    
    checkTitle();
    
    $( ".triggerModify" ).keydown(function() {
        isModified = true;
    });
    
    countCharDescription();
    highlightVisibility();
  } );
  
  $("#visible").click(function(){
    highlightVisibility();
  });
  
  function highlightVisibility(){
      if ($("#visible").is(":checked")) {
         $("#divGroup").removeClass("has-error").addClass("has-success");
      } else {
         $("#divGroup").removeClass("has-success").addClass("has-error");
      }
  }
  
  function pageSearch(){
    pageTitle = $("#pageSearchTitle").val();
    
    if (pageTitle.length == 0)
        return;
          
    $.post( "admin.php?module=wiki&op=searchPages", { title:  pageTitle})
        .done(function( data ) {
            $("#pageSearchResult").html(data);
        }  
    )    
  }
  
  function updateMultilangStatus(master_ID, language){
      $.post( "admin.php?module=wiki&op=multilangStatus", { master_ID:  master_ID, language: language })
        .done(function( data ) {
            $("#masterStatus").html(data);
        }  
      );
  }
  
function save() {  
    if (typeof stopSave !== "undefined") {
        if (stopSave === true){
            alert ("Please check your date.");
            return;
        }
    }
    
    $("#crudStatus").html("<div class=\"alert alert-info\"><strong>Updating!</strong> Page is being update.</div>");
    
    var title       =   $("#title").val();
    var use_file    =   $("#use_file").val();
    var status      =   $("#status").val();
    var license     =   $("#license").val();
    
    var category    =   $("#category").val();
    var title_alternative = $("#title_alternative").val();
    var language = $("#language").val();
    ' .  (isset($_GET['master_ID']) ? 'var master_ID = ' . ((int) $_GET['master_ID']) .';': 'var master_ID = null;') .'
    var internalRedirect = $("#internalRedirect").val();
    var tags = $("#tags").val();
    var internalTags = $("#internalTags").val();
    var keywords = $("#keywords").val();
    var startDate = $("#startDate").val();
    var endDate = $("#endDate").val();
    var metaDataDescription = $("#metaDataDescription").val();
    var image = $("#articleImage").val();
    var image_ID = $("#articleImageID").val();
    var featuredVideo_ID = $("#featured_video_ID").val();
    
    var notes = tinyMCE.get("notes").getContent();
    var shortDescription = tinyMCE.get("short_description").getContent();
    
    var seoKeywords = $("#seoKeywords").val();
    
    if (noEditor === false){
        var content = tinyMCE.get("content").getContent();
    }else {
        var content = editor.getValue();
    }
        
    var additionalData = $("#additionalData").val();
    $("#visible").is(":checked")                ?   visible = true: visible = false;
    $("#no_editor").is(":checked")              ?   no_editor = true: no_editor = false;
    $("#no_info").is(":checked")                ?   no_info = true: no_info = false;
    $("#no_linking_pages").is(":checked")       ?   no_linking_pages = true: no_linking_pages = false;
    $("#no_search").is(":checked")              ?   no_search = true: no_search= false;
    $("#no_index").is(":checked")               ?   no_index = true: no_index = false;
    $("#service_page").is(":checked")           ?   service_page = true: service_page = false;
    $("#full_page").is(":checked")              ?   full_page = true: full_page = false;
    $("#no_banner").is(":checked")              ?   no_banner = true: no_banner = false;
    $("#no_comment").is(":checked")             ?   no_comment = true: no_comment = false;
    $("#no_toc").is(":checked")                 ?   no_toc = true: no_toc = false;
    $("#no_similar_pages").is(":checked")       ?   no_similar_pages = true: no_similar_pages = false;
    $("#no_title").is(":checked")               ?   no_title = true: no_title = false;
    
    $("#minorUpdate").is(":checked")            ?   minor_update = true: minor_update = false;
    
    console.log("Crudtype is " + crudType + "(" + ID + ")");
    
    $.post( "admin.php?module=wiki&op=savePage", { crudType:  crudType,
                                                   ID               : ID,
                                                   master_ID        : master_ID,
                                                   category         : category,
                                                   visible          : visible,
                                                   title            : title,
                                                   status           : status,
                                                   license          : license,
                                                   title_alternative: title_alternative,
                                                   use_file         : use_file,
                                                   language         : language,
                                                   internalRedirect: internalRedirect, 
                                                   tags             : tags, 
                                                   internalTags     : internalTags, 
                                                   keywords         : keywords, 
                                                   startDate        : startDate, 
                                                   endDate          : endDate,
                                                   shortDescription    : shortDescription, 
                                                   seoKeywords       : seoKeywords, 
                                                   metaDataDescription : metaDataDescription, 
                                                   content          : content,
                                                   no_editor        : no_editor,
                                                   no_search        : no_search,
                                                   no_index         : no_index,
                                                   no_info          : no_info,
                                                   no_linking_pages : no_linking_pages,
                                                   no_banner        : no_banner,
                                                   no_comment       : no_comment,
                                                   no_toc           : no_toc,
                                                   no_similar_pages : no_similar_pages,
                                                   no_title         : no_title,
                                                   minor_update     : minor_update,
                                                   service_page     : service_page,
                                                   full_page        : full_page,
                                                   image            : image,
                                                   image_ID         : image_ID,
                                                   featuredVideo_ID : featuredVideo_ID,
                                          
                                                   notes            : notes,
                                                   additionalData   : additionalData })
      .done(function( data ) {
        var result = jQuery.parseJSON(data);
            if (result.status == 200) {
                
                $("#crudStatus").html("<div class=\'alert alert-success\'>OK.</div>");
                isModified = false;
                
                if (typeof result.ID !== \'undefined\') {
                   crudType = "update";
                   console.log("Save result. ID returned s: " + result.ID);
                   ID = result.ID;
                }
            
                if (typeof result.master_ID !== \'undefined\') {
                   console.log("Save result. Master ID: " +result.master_ID + ", language: " + language);
                   updateMultilangStatus(result.master_ID, language);
                }
                
                globalStats();
                seoStatus(); 
      
            } else {
                $("#crudStatus").html("<div class=\'alert alert-warning\'>Warning. " + result.status + ". Report: " + result.description + ".</div>");
            }
       }
       
     );
  }
  
function setAsPageImage(ID, imageUrl)
{
    basePath = "' . $URI->getBaseUri(true) . '";
    imagePath = basePath + imageUrl;
    
    $("#divArticleImageContainer").html("<img class=\'img-fluid\' src=\'" + imagePath + "\' />");
    $("#articleImageID").val(ID);    
    $("#articleImage").val(imageUrl);    
} 

function getOutGoingLinks()
{
    if (noEditor === false) {
        var content = tinyMCE.get("content").getContent();
    } else {
        var content = $("#content").val();
    }
    
    $.post( "admin.php?module=wiki&op=checkOutgoingLink", { content: content })
        .done(function( data ) {
            $("#outgoingLinks").html(data);
        });
}

console.log("Checking editor");
if ( noEditor === false ) {
    console.log("Editor is on");
    initEditor();
} else {
    console.log("Editor is off");
    activateCodeEditor();
    getOutGoingLinks();
}

console.log("Activating short descr editor");

tinymce.init({
          selector: \'#short_description, #notes\',
          height: 250,
          menubar: false,
       	  
          plugins: \'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table contextmenu code help\',
          toolbar: \'insert | undo redo |  styleselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help\',
          content_css: [
            \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
            \'//www.tinymce.com/css/codepen.min.css\']
});

function initEditor() {
    console.log("Called initEditor()");
    
    tinymce.init({
      setup: (editor) => {
            relative_urls: false,
            
            ' . $editorCustomButtons . ' 
          
            editor.ui.registry.addButton(\'FabTable\', {
                text: \'FabTable\',
                onAction: () => {
                    tinymce.activeEditor.execCommand(\'InsertHTML\', false, \'{| tableCaption<br/>|* class="table table-bordered table-striped table-responsive"*|<br/>|* Heading1 || Heding2 || Heding3 *|<br/>|* Cell1 || Cell2 || Cell3 *|<br/>|}\');               
                }
            }),
            
            editor.ui.registry.addButton(\'section\', {
                text: \'Section\',
                onAction: () => {
                    editor.focus();
                    var text = editor.selection.getContent({\'format\': \'html\'});
                    sectionName = prompt("Section name");
                    
                    if (sectionName.length === 0)
                        return;
                    
                    tinymce.activeEditor.execCommand(\'InsertHTML\', false, \'<section class="FabCMS-section" id="\' + sectionName + \'">\' + text + \'</section><p>&nbsp;</p>\');               
                }
            }),
            
            editor.on(\'init\', function(e) {
                getOutGoingLinks();
            
        }),
    
        editor.on(\'change\', function(e) {
            isModified = true;
            getOutGoingLinks();
            var dt = new Date();
            var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
            $("#crudStatus").html("Last change (unsaved) on: " + time );
        });
      },
      
      relative_urls : false,
      selector: \'.richText\',
      element_format: \'xhtml\',
      height: 450,
      theme: \'silver\' ,
      skin: \'oxide\', 
         
      plugins: \'lists codesample code searchreplace visualblocks anchor charmap wordcount\',
      toolbar1: \' undo redo | insert | searchreplace | visualblocks | anchor | charmap | styleselect  | bold italic | alignleft aligncenter alignright alignjustify | subscript superscript | numlist bullist outdent indent | link image\',
      toolbar2: \' preview media | forecolor backcolor emoticons | codesample code| section\',
      toolbar3: \'section | FabTable | ' . $pgToolbar . '\',
      
      image_advtab: true,
   
      content_css: [
        \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
        \'' .  $URI->getBaseUri(true). '/admin/modules/wiki/res/style.css\',
      ],
      
     });
                   
}

function switchEditor()
{
    
    if (noEditor === true) {
        console.log("Activating main editor");
        noEditor = false; 
        editor.toTextArea();
        initEditor();        
    } else {
        console.log("Deactivating main editor");
        noEditor = true;
        tinymce.execCommand(\'mceRemoveEditor\', true, "content");
        activateCodeEditor();
    }
}

function activateCodeEditor() {
    editor = CodeMirror.fromTextArea( document.getElementById("content"), {
        mode:  "text/html",
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: "default elegant"
    });
}

function getIngoingLinks() {
      title = $("#title").val();
      language = $("#language").val();
      
      $.post( "admin.php?module=wiki&op=getIngoingLinks", { title:  title, language: language})
          .done(function( data ) {
            $("#ingoingLinks").html(data);
          }  
      );
}

function checkTitle(){
    title = $("#title").val();
    language = $("#language").val();
    
    document.title = title;
     
    console.log("Fired checktitle");
    
    if (title.length == 0) {
        console.log("Title has zero lenght. Aborting.");
        stopSave = true;
        $("#title").addClass("FabCMS-alert");    
        return;
    } else {
        console.log("Title is: " + title);
        console.log("Title has more than one character. Good.");
        $("#title").removeClass("FabCMS-alert");
    }
    
    $.post( "admin.php?module=wiki&op=getUnlinkedPages", {title:  title, language: language})
        .done(function( data ) {
            console.log("Called getUnlinkedPages");
            $("#unlinkedPages").html(data); 
        }  
    );
    
    if (title === titleOriginal) { // Title is the same as the original title
        $("#title").removeClass("alert alert-warning");
        getIngoingLinks();
        return;
    }
    
    $.post( "admin.php?module=wiki&op=checkTitle", {title:  title, language: language})
        .done(function( data ) {
            
            console.log("Checktitle returns " + data);
            
            if (parseInt(data) === 0) {
                stopSave = false;
                $("#title").removeClass("alert alert-warning");
                getIngoingLinks();
            } else {
                stopSave = true;
                $("#title").addClass("alert alert-warning");
            }
        }  
    );
}

function countCharDescription() {
    countChar = $("#metaDataDescription").val().length;
    $("#countCharDescription").html( countChar );    
}


function initStats(){
	
	var can = document.getElementById("pageStat");
	
	wid = can.width;
	hei = can.height;
	var context = can.getContext("2d");
	context.fillStyle = "#eeeeee";
	context.strokeStyle = "#999999";
	context.fillRect(0,0,wid,hei);
	
	context.font = "8pt Arial-narrow, sans-serif";
	context.fillStyle = "#999999";
	
	context.moveTo(CHART_PADDING,CHART_PADDING);
	context.lineTo(CHART_PADDING,hei-CHART_PADDING);
	context.lineTo(wid-CHART_PADDING,hei-CHART_PADDING);
	
	fillChart(context,chartYData);
	createBars(context,data);
	
}

function fillChart(context, stepsData){ 
	var steps = stepsData.length;
	var startY = CHART_PADDING;
	var endY = hei-CHART_PADDING;
	var chartHeight = endY-startY;
	var currentY;
	var rangeLength = range.max-range.min;
	for(var i=0; i<steps; i++){
		currentY = startY + (1-(stepsData[i].value/rangeLength)) *	chartHeight;
		context.moveTo(CHART_PADDING, currentY );
		context.lineTo(CHART_PADDING*1.3,currentY);
		context.fillText(stepsData[i].label, CHART_PADDING*1.5, currentY+6);
	}
	context.stroke();
	
}

function createBars(context,data){ 
	var elementWidth =(wid-CHART_PADDING*2)/ data.length;
	var startY = CHART_PADDING;
	var endY = hei-CHART_PADDING;
	var chartHeight = endY-startY;
	var rangeLength = range.max-range.min;
	var stepSize = chartHeight/rangeLength;
	context.textAlign = "center";
	for(i=0; i<data.length; i++){
		context.fillStyle = data[i].style;
		context.fillRect(CHART_PADDING + elementWidth*i ,hei-CHART_PADDING - data[i].value*stepSize, elementWidth, data[i].value*stepSize);
		context.fillStyle = "rgba(50, 50, 50, 0.9)";
		context.fillText(data[i].label, CHART_PADDING +elementWidth*(i+.5), hei-CHART_PADDING*1.5);
	}
		
}

function drawBorder(xPos, yPos, width, height, thickness = 2)
{
  ctx.fillStyle=\'#000\';
  ctx.fillRect(xPos - (thickness), yPos - (thickness), width + (thickness * 2), height + (thickness * 2));
}';

$this->addScript($theScript);