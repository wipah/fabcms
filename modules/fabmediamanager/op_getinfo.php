<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/09/2015
 * Time: 20:56
 */

if (!$core->loaded)
    die();

if (!$user->logged)
    die("No direct access.");

$this->noTemplateParse = true;

$fabMedia->customButtons[]  =   explode('||', htmlentities($_POST['customButton']));
$fabMedia->module           =   $core->in($_POST['fabMediaModule'], true);

$filterKeyword              =   $core->in($_POST['fabMediaFilterKeyword']);

if (empty($_POST['ID'])) {
    echo 'No ID passed.';

    return;
}

$ID = (int) $_POST['ID'];

if (!empty($_POST['type'])) {
    $type = ' AND type = \'' . $core->in($_POST['type']) . '\'';
}

// Build the query
$query = '
SELECT F.*,
       F.ID AS media_ID, 
       M.ID AS master_ID,
       F.filename,
       F.extension,
       F.type,
       F.subtype,
       F.size,
       F.upload_date,
       V.ID AS video_ID,
       V.*
FROM ' . $db->prefix . 'fabmedia_masters AS M
LEFT JOIN ' . $db->prefix . 'fabmedia AS F
    ON M.ID = F.master_ID
LEFT JOIN ' . $db->prefix . 'fabmedia_videos AS V
    ON V.fabmedia_ID = F.ID
WHERE F.ID = \'' . $ID . '\' ' . $type . '
LIMIT 1;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. The query is: ' . $query;
    return;
}

if (!$db->affected_rows) {
    echo 'No image found! Aborting.: ' . $query;
    return;
}

$row = mysqli_fetch_assoc($result);

// Build license
$query = 'SELECT * 
          FROM ' . $db->prefix . 'licenses_licenses 
          WHERE lang = \'' . $core->shortCodeLang . '\';';
$db->setQuery($query);
if (!$resultLicenses = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No license found.' . $query;
    return;
}

$selectLicenses = '<select class="form-control-sm input-sm" id="fabmediamanager_licenses">';
while ($rowLicenses = mysqli_fetch_assoc($resultLicenses)){
    $selectLicenses .= '<option ' . ( (int) $row['license_ID'] === (int) $rowLicenses['ID'] ? 'selected="selected"' : '') . ' value="' . $rowLicenses['ID'] . '">' . $rowLicenses['name'] . '</option>';
}
$selectLicenses .= '</select>';

switch ($row['type']) {
    case 'image':
        // If the file is an image build all the button for the relative variants (size, and quality)
        $imagePath = $row['filename'];

        $extension = strtolower($row['extension']);
        $pos = strrpos($imagePath, '.' . $extension);

        if ($extension        == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif' || $extension == 'webp') {
            $imageFinalMQPath = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
            $imageFinalLQPath = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

            $pathOriginal     = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'];
            $pathMQ           = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath;
            $pathLQ           = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath;
        }

        switch ($fabMedia->module) {
            case 'wiki':
                $sendToEditor = '

                    <span class="float-right">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="simpleImage">
                            <label class="form-check-label" for="simpleImage">SimpleImage</label>
                        </div>
                    </span>
                    
                    <span class="btn btn-default float-right" 
                          aria-label="Send FabCode" 
                          onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . utf8_encode($row['filename']) . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                        <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/send_hq.png" alt="GQ" /><br/>(' . human_filesize(filesize($pathOriginal)) . ')
                    </span>
                    
                    <span class="btn btn-default float-right" 
                          aria-label="Send FabCode" 
                          onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath . '\', \'' . utf8_encode($row['filename']) . '\', \'' . $row['media_ID'] . '\');"> 
                        <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/send_mq.png" alt="GQ" /><br/>(' . human_filesize(filesize($pathMQ)) . ')
                    </span>
          
                    <span class="btn btn-default float-right" 
                          aria-label="Send FabCode" 
                          onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '\', \'' . utf8_encode($row['filename']) . '\', \'' . $row['media_ID'] . '\');"> 
                        <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/send_lq.png" alt="GQ" /><br/>(' . human_filesize(filesize($pathLQ)) . ')
                    </span>';
                break;
            case 'forum-topic':
            case 'forum':
                $sendToEditor = '
                      <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                        <span class="glyphicon glyphicon-file" aria-hidden="true"></span> Send to editor (' . human_filesize(filesize($pathMQ)) . ')
                      </button>';
                break;
            default:
                $sendToEditor = '
                    <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                    <span class="glyphicon glyphicon-file" aria-hidden="true"></span>Send to editor GQ (' . human_filesize(filesize($pathOriginal)) . ')
                    </button>
          
                      <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                        <span class="glyphicon glyphicon-file" aria-hidden="true"></span>Send to editor MQ (' . human_filesize(filesize($pathMQ)) . ')
                      </button>
                    
                      <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                        <span class="glyphicon glyphicon-file" aria-hidden="true"></span>Send to editor LQ (' . human_filesize(filesize($pathLQ)) . ')
                      </button>';
                break;
        }

        break;
    case 'video':
        $sendToEditor = '
         <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="sendFileToEditor(\'' . $row['type'] . '\', null, null, ' . $row['media_ID'] . ', \'' . $row['provider_ID'] . '\');">
            <span class="glyphicon glyphicon-file" aria-hidden="true"></span>Send video to editor 
         </button>';
        break;
}

echo '  <form id="fabMediaInfoForm" method="post">

        <input  type="hidden" id="ID" value="' . $_POST['ID'] . '" />
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class=" nav-link active" aria-selected="true" data-toggle="tab" role="tab" id="tabGeneral" href="#tabPanelGeneral">General</a>
            </li>
            <li class="nav-item">
            
                <a class=" nav-link"  aria-selected="false" data-toggle="tab" role="tab" id="tabVideo" href="#tabPanelVideo">Video</a>
                </li>';

if ($fabMedia->module === 'wiki') {
    echo '  
                <li class="nav-item">
            
                <a class=" nav-link" aria-selected="false" data-toggle="tab" role="tab" id="tabPages" href="#tabPanelPages">Pages</a>
                </li>
           
';
}


$selectLanguage = '<select id="FabMediaLanguage" name="FabMediaLanguage">';
foreach ($conf['langAllowed'] as $lang ) {
    $selectLanguage .= '<option ' . ($row['lang'] === $lang ? 'selected' : '') . ' value="' . $lang . '">' . $lang . '</option>';
}
$selectLanguage .= '</select>';

echo '
        
         </ul>
    
         <div class="tab-content" id="nav-tabContent">
  
              <div id="tabPanelGeneral" class="tab-pane fade show active " role="tabpanel" aria-labelledby="tabPanelGeneral">
    
                <div class="row">
                
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="master_ID">Master ID</label>
                            <input value="' . utf8_encode($row['master_ID']) . '" type="text" class="form-control-sm input-sm" id="master_ID" disabled="disabled">
                        </div>
                        
                        <div class="form-group">
                            <label class="control-label" for="media_ID">Media ID</label>
                            <input value="' . utf8_encode($row['media_ID']) . '" type="text" class="form-control-sm input-sm" id="media_ID" disabled="disabled">
                        </div>
                        
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="filename">Filename</label>
                            <input value="' . utf8_encode($row['filename']) . '" type="text" class="form-control-sm input-sm" id="filename" disabled="disabled"> -
                            <a onclick="renameFile(' . $row['media_ID'] . ', \'' . utf8_encode($row['filename']) . '\');">Rename</a> -
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label" for="uploadDate">Upload</label>
                            <input value="' . $row['upload_date'] . '" type="text" class="form-control-sm input-sm" id="uploadDate" disabled="disabled">
                        </div>
                    </div>
                
                </div>
                
                <hr />
                
                <div class="row">
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="type">Type</label>
                            <input value="' . $row['type'] . '" type="text" class="form-control-sm input-sm" id="fabmediamanager_type" disabled="disabled">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="subtype">Subtype</label>
                            <input value="' . $row['subtype'] . '" type="text" class="form-control-sm input-sm" id="fabmediamanager_subtype" placeholder="Placeholder text">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="FabMediaLanguage">Lang</label> <br/>
                            ' . $selectLanguage . '
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="checkbox">
                            <label class="control-label">
                                <input ' . ( (int)$row['modified'] === 1 ? "checked=\'checked\'" : "") . ' id="fabmediamanager_modified" type="checkbox" value="1">                                                                                                                                                                                                Modified
                            </label>
                        </div>
  
                        <div class="checkbox">
                            <label class="control-label">
                                <input ' . ( (int) $row['enabled'] === 1 ? 'checked="checked"' : "") . 'id="fabmediamanager_enabled" type="checkbox" value="1">Enabled
                            </label>
                        </div>
                    
                        <div class="checkbox">
                            <label class="control-label">
                                <input ' . ( (int) $row['indexable'] === 1 ? 'checked="checked"' : "") . 'id="fabmediamanager_indexable" type="checkbox" value="1">Indexable
                            </label>
                        </div>

                        <div class="checkbox">
                            <label class="control-label">
                                <input ' . ( (int) $row['global_available'] === 1 ? "checked=\'checked\'" : "") . ' id="fabmediamanager_globalAvailable" type="checkbox" value="1"> Global avaliable
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                    
                                    <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="fabmediamanager_license">License</label>
                           ' . $selectLicenses.'
                        </div>
                    </div>
                
                                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="author">Author</label>
                            <input value="' . $row['author'] . '" type="text" class="form-control-sm" id="fabmediamanager_author" placeholder="Placeholder text">
                        </div>
                    </div>
                </div>
                    </div>
                </div>
    
                <hr />
    
                <div class="row">
                
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="fabmediamanager_title">Title</label>
                            <input style="width: 95%" value="' . $row['title'] . '" type="text" class="form-control-sm" id="fabmediamanager_title" placeholder="Title">
                        </div>
                    </div>
                
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="tags">Tags</label>
                            <input style="width: 95%" value="' . $row['tags'] . '" type="text" class="form-control-sm" id="fabmediamanager_tags" placeholder="Tags used">
                        </div>
                    </div>
                  
                </div>
                    
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="fabmediamanager_description">Description</label>
                            <textarea class="" rows="4" id="fabmediamanager_description">' . $row['description'] . '</textarea>
                        </div>
                    </div>
                </div>
                
              </div>              
             
              <div id="tabPanelVideo" class="tab-pane fade" role="tabpanel" aria-labelledby="tabPanelVideo">
    
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="filename">Video ID</label>
                            <input value="' . $row['video_ID'] . '" type="text" disabled class="form-control-sm input-sm" id="fabMediaVideoID">
                        </div>
                    </div>
                
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="filename">Length</label>
                            <input value="' . $row['length'] . '" type="time" class="form-control-sm input-sm" id="FabmediaLength">
                        </div>
                    </div>
                
                </div>
                
              </div>
              ';

switch ($fabMedia->module) {
    case 'wiki':

        $query = '
        SELECT 
          P.title, 
          P.title_alternative, 
          P.trackback
        FROM ' . $db->prefix . 'wiki_pages_files AS F
            LEFT JOIN ' . $db->prefix . 'wiki_pages AS P
        ON P.ID = F.page_ID
            WHERE F.fabmedia_ID = ' . $ID . '
        AND P.visible = 1';

        $db->setQuery($query);
        if (!$resultWikiPages = $db->executeQuery('select')) {
            echo 'Query error while selecting pages from wiki!';
        } else {
            if (!$db->affected_rows) {
                $pagesFromWiki = $language->get('media', 'showImageWikiHasNoPage');
            } else {
                $pagesFromWiki = '';
                while ($rowWikiPage = mysqli_fetch_assoc($resultWikiPages)) {
                    $pagesFromWiki .= '&bull; <a href="' . $URI->getBaseUri() . 'wiki/' . $rowWikiPage['trackback'] . '/">' . $rowWikiPage['title'] . $rowWikiPage['title_alternative'] . '</a><br/>';
                }
            }
        }

        echo '
              
              <div id="tabPanelPages" class="tab-pane fade" role="tabpanel" aria-labelledby="tabPanelPages">
                <div class="row">               
                    <div class="col-md-12">
                       <h2>Pages</h2>
                      ' . $pagesFromWiki . '
                    </div>
                </div>
              </div>
              
              
              ';

        break;
}


// Permissions
switch ( $fabMedia->module) {
    case 'wiki':
        $enablePageImage = true;
        $enableUpdateFile = true;
        $enableDelete = true;
        break;
    default:
        $enablePageImage = false;
        $enableUpdateFile = true;
        $enableDelete = true;
}

echo '     
             
           <div class="row"> <!-- Start buttons -->
                <div class="col-md-3">
                    <div id="fabMediaUpdate">
                        <small>FabMediaManager</small>
                    </div>
                </div>
    
                <div class="col-md-9 fabcms-FabMedia-ButtonBox">
                     ' . $fabMedia->renderCustomButton() .

    ($enablePageImage === true
        ?
        '
        
        
                     <span class="btn btn-default float-right" aria-label="Send FabCode" onclick="setAsPageImage(' . $row['media_ID'] . ',\'' . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');">
                        <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/image_homepage.png" alt="update" /><br/>Set as page image
                     </span>
                     
            '
        : '') .
    '
                     
                    ' . $sendToEditor .

    ($enableUpdateFile === true
        ?
        '
                     <span class="btn btn-default float-right" aria-label="Update" onclick="updateInfo()">
                       <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/update.png" alt="update" /><br/>
                       Update
                     </span>
                     '
        : '')
    .

    ($enableDelete === true
        ?
        '
                  
                    <span class="btn btn-default float-right" aria-label="Delete" onclick="deleteFile(\'' . $row['media_ID'] . '\')">
                       <img style="width:32px; height: 32px;" src="' . $URI->getBaseUri(true) .'/modules/fabmediamanager/res/delete.png" alt="GQ" /><br/>
                       Delete
                     </span>'
        : '') . '
    
                </div>
            </div> <!-- End buttons -->
    
          
            </div>
        </form>

<script type="text/javascript">
function renameFile(ID, oldFile) {
    var newName = prompt ("Please, enter a new file", oldFile);

    if (newName != null) {
        $.post( "' . $URI->getBaseUri() . 'fabmediamanager/rename/", {  ID : ID, newName : newName })
        .done(function( data ) {
            if (data !== "ok"){
                alert (data);
            }
        });
    }
}

function deleteFile(ID) {
    if (!confirm("Are you sure you want to delete this file?"))
        return;

    $.post( "' . $URI->getBaseUri() . 'fabmediamanager/deletefile/", {  ID : ID })
    .done(function( data ) {
        $("#fabMediaUpdate").html(data);
    });
}

function sendFileToEditor(type, path, filename, ID, provider_ID = null) {
    switch (type){
        case "image": /* [img src="theSource"|alt==Alt for the image||copyright==FabCMS||description==this is a simple description] */
            if ($("#simpleImage").is(":checked") ) {
                simpleImage = "||simpleImage==1";
            } else {
                simpleImage = "";
            }
            
            tinyMCE.execCommand("mceInsertContent", false,"[$img src=" + path + "|ID=="+ ID + "||description==" + type + "||alt==" + $(\'#fabmediamanager_title\').val() + "||class==img-fluid" + simpleImage + "$]");
            break;  
        case "video": /* [youtube abcde] */
            tinyMCE.execCommand("mceInsertContent", false,"[youtube " + provider_ID + "||ID==" + ID + "]");
            break;
        case "archive":
            tinyMCE.execCommand("mceInsertContent", false,"[file " + ID + "]");            
            break;
        default:
            tinyMCE.execCommand("mceInsertContent", false,"<a data-ID=" + ID + "  href=" + path + ">" + filename + "</a>");
            break;
    }
}

function updateInfo() {
        console.log("Updating info");
    
        fabmediamanager_master_ID = ' . $row['master_ID'] . ';
        fabmediamanager_subtype = $("#fabmediamanager_subtype").val();
            
        if ( $("#fabmediamanager_enabled").prop(\'checked\') ) {
            fabmediamanager_enabled = 1;
        } else {
            fabmediamanager_enabled = 0;
        }

        if ( $("#fabmediamanager_indexable").prop(\'checked\') ) {
            fabmediamanager_indexable = 1;
        } else {
            fabmediamanager_indexable = 0;
        }

        image_ID = $("#ID").val();

        fabmediamanager_title       =   $("#fabmediamanager_title").val();
        fabmediamanager_type       =   $("#fabmediamanager_type").val();
        fabmediamanager_tags        =   $("#fabmediamanager_tags").val();
        fabmediamanager_description =   tinyMCE.get(\'fabmediamanager_description\').getContent();
        fabmediamanager_copyright   =   $("#fabmediamanager_licenses").val();
        fabmediamanager_author      =   $("#fabmediamanager_author").val();
        fabmediamanager_language    =   $("#FabMediaLanguage").val();
        fabmediamanager_indexable    =   $("#fabmediamanager_indexable").val();
        fabmediamanager_enabled    =   $("#fabmediamanager_enabled").val();
        fabmediamanager_globalAvailable    =   $("#fabmediamanager_globalAvailable").val();
        fabmediamanager_length      =   $("#FabmediaLength").val();
        fabmediamanager_video_ID    =   $("#fabMediaVideoID").val();
        
        console.log("Calling page");
    
        $.post( "' . $URI->getBaseUri() . 'fabmediamanager/saveinfo/", {  ID : image_ID,
                                                                          fabmediamanager_language      :   fabmediamanager_language,
                                                                          fabmediamanager_master_ID     :   fabmediamanager_master_ID,
                                                                          fabmediamanager_type          :   fabmediamanager_type,
                                                                          fabmediamanager_subtype       :   fabmediamanager_subtype,
                                                                          fabmediamanager_indexable     :   fabmediamanager_indexable,
                                                                          fabmediamanager_enabled       :   fabmediamanager_enabled,
                                                                          fabmediamanager_globalAvailable       :   fabmediamanager_globalAvailable,
                                                                          fabmediamanager_title         :   fabmediamanager_title,
                                                                          fabmediamanager_tags          :   fabmediamanager_tags,
                                                                          fabmediamanager_description   :   fabmediamanager_description,
                                                                          fabmediamanager_copyright     :   fabmediamanager_copyright,
                                                                          fabmediamanager_author        :   fabmediamanager_author, 
                                                                          fabmediamanager_length        :   fabmediamanager_length, 
                                                                          fabmediamanager_video_ID      :   fabmediamanager_video_ID})
        .done(function( data ) {
            console.log("Done calling page");
            $("#fabMediaUpdate").html(data);
        });
}
</script>';

function human_filesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}