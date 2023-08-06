<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 09/02/2016
 * Time: 19:19
 */

if (!$core->loaded)
    die();

$this->noTemplateParse = false;

// Get the image data
$imageData = $path['3'];
if (!preg_match('/([0-9]{1,})-([a-z0-9\W\w_\-\:\.]{1,})/', $imageData, $matches)) {
    echo 'No image found';

    return;
}

$ID         =   (int) $matches[1];
$trackback  =   $core->in($matches[2], true);

if (strlen($trackback) < 1) {

    $relog->write(['type'      => '2',
        'module'    => 'media',
        'operation' => 'madia_image_not_found_no_trackback',
        'details'   => 'Image not not found. ID: ' . $ID,
    ]);

    header("HTTP/1.0 404 Not Found");
    echo '<!--FabCMS-hook:media-showImage404NotFound-->';
    return;
}

// Check if the user is seeing a gallery
if (isset($_GET['gallery_ID']))
{

    $gallery_ID = (int) $_GET['gallery_ID'];

    // Get the current position of the image using the "order" field
    $query = '
    SELECT ITEMS.order, 
           GALLERY.title
    FROM ' . $db->prefix . 'fabmedia_galleries_items ITEMS
    LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_galleries GALLERY
        ON GALLERY.ID = ITEMS.gallery_ID 
    WHERE ITEMS.gallery_ID = ' . $gallery_ID . ' 
        AND ITEMS.image_ID = ' . $ID . '
        AND GALLERY.lang = \'' . $core->shortCodeLang . '\'
        
    LIMIT 1;';

    $db->setQuery($query);
    if (!$result = $db->executeQuery('select')){
        echo 'Query error while getting gallery info. ' . $query;
        return;
    }

    if (!$db->affected_rows){
        echo 'No image found';
        return;
    }
    $currentImage = mysqli_fetch_assoc($result);

    // Adds the canonical URI
    $this->head .= '<link rel="canonical" href="' . $URI->getBaseUri() .'media/showimage/' . $ID . '-' . $trackback .'/" />';

    // Previous
    $query = '
    SELECT GALLERY.ID gallery_ID, 
	  ITEMS.`order`,
      MEDIA.user_ID,     
	  ITEMS.image_ID, 
	  MEDIA.title, 
	  MEDIA.filename,
      MEDIA.extension,
      MEDIA.trackback
    FROM ' . $db->prefix . 'fabmedia_galleries_galleries GALLERY
    LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_items ITEMS
	  ON ITEMS.gallery_ID = GALLERY.ID
    LEFT JOIN ' . $db->prefix . 'fabmedia MEDIA
	  ON MEDIA.ID = ITEMS.image_ID
    LEFT JOIN ' . $db->prefix . 'fabmedia_masters MASTERS
	  ON MEDIA.master_ID = MASTERS.ID 	
    WHERE ITEMS.`order` < ' . $currentImage['order'] . '
    AND GALLERY.ID = ' . $gallery_ID . '
    AND GALLERY.visible = 1
    AND ITEMS.visible = 1
    AND MEDIA.enabled = 1
    AND MEDIA.global_available = 1
    ORDER BY ITEMS.`order` DESC
    LIMIT 1';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')){
        echo 'Query error while checking for previous item.' . $query;
        return;
    }

    if (!$db->affected_rows){
        $previous = '---';
    } else {
        $result = mysqli_fetch_assoc($result);

        $previous = '<a href="' . $URI->getBaseUri() . $this->routed . '/showimage/' . $result['image_ID'] . '-' . $result['trackback'] . '/?gallery_ID=' . $gallery_ID . '">
                        <img style="max-height:110px;" 
                             class="lazy img-fluid" 
                             data-src="' .  $fabMedia->getThumbnailPath($result['filename'], $result['extension'], $result['user_ID']) . '" 
                             alt="' . htmlentities($result['title']) . '"/> <br/>' .
                             $language->get('media', 'showImageGalleryPrevious', null) .
                    '</a>';
    }

    // Next
    $query = '
    SELECT GALLERY.ID gallery_ID, 
	  ITEMS.`order`,
      MEDIA.user_ID,     
	  ITEMS.image_ID, 
	  MEDIA.title, 
	  MEDIA.filename,
      MEDIA.extension,
      MEDIA.trackback
    FROM ' . $db->prefix . 'fabmedia_galleries_galleries GALLERY
    LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_items ITEMS
	  ON ITEMS.gallery_ID = GALLERY.ID
    LEFT JOIN ' . $db->prefix . 'fabmedia MEDIA
	  ON MEDIA.ID = ITEMS.image_ID
    LEFT JOIN ' . $db->prefix . 'fabmedia_masters MASTERS
	  ON MEDIA.master_ID = MASTERS.ID 	
    WHERE ITEMS.`order` > ' . $currentImage['order'] . '
    AND GALLERY.ID = ' . $gallery_ID . '
    AND GALLERY.visible = 1
    AND ITEMS.visible = 1
    AND MEDIA.enabled = 1
    AND MEDIA.global_available = 1
    ORDER BY ITEMS.`order` ASC
    LIMIT 1';


    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')){
        echo 'Query error while checking for previous item.' . $query;
        return;
    }

    if (!$db->affected_rows){
        $next = '---';
    } else {
        $result = mysqli_fetch_assoc($result);

        $next = '<a href="' . $URI->getBaseUri() . $this->routed . '/showimage/' . $result['image_ID'] . '-' . $result['trackback'] . '/?gallery_ID=' . $gallery_ID . '">
                        <img style="max-height:110px;" 
                             class="lazy img-fluid" 
                             data-src="' .  $fabMedia->getThumbnailPath($result['filename'], $result['extension'], $result['user_ID']) . '" 
                             alt="' . htmlentities($result['title']) . '"/><br/>' . $language->get('media', 'showImageGalleryNext', null) .
                 '</a>';
    }

    $galleryBar = '<div class="row fabmediaShowImageGalleryBar" style="max-height: 150px;">
                    <div class="col-md-3 fabmediaShowImageGalleryBarPrevious">' . $previous . '</div>
                    <div class="col-md-6">'. $currentImage['title'] . '</div>
                    <div class="col-md-3 fabmediaShowImageGalleryBarNext">' . $next . '</div>
                   </div>';
}

$query = 'SELECT 
            FMEDIA.user_ID,
            FMEDIA.filename,
            FMEDIA.extension,
            FMEDIA.upload_date,
            FMEDIA.trackback,
            FMEDIA.tags,
            FMEDIA.description,
            FMEDIA.title,
            FMEDIA.ID,
            FUSER.username,
            LICENSES.name AS license_name,
            LICENSES.ID AS license_ID
          FROM ' . $db->prefix . 'fabmedia AS FMEDIA
          LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS FMASTER
            ON FMASTER.ID = FMEDIA.master_ID
          LEFT JOIN ' . $db->prefix . 'users AS FUSER
            ON FMEDIA.user_ID = FUSER.ID
          LEFT JOIN ' . $db->prefix . 'licenses_licenses AS LICENSES
            ON LICENSES.ID = FMEDIA.license_ID    
          WHERE FMEDIA.ID = \'' . $ID . '\'
            AND FMEDIA.trackback = \'' . $trackback . '\';';
$db->setQuery($query);

if (!$db->executeQuery('select')) {
    echo 'Error';
    return;
}

if (!$db->affected_rows) {
    echo 'No image found.';
    return;
}

$row            =   $db->getResultAsArray();

$user_ID        =   $row['user_ID'];
$filename       =   $row['filename'];
$extension      =   $row['extension'];
$tags           =   $row['tags'];
$description    =   $row['description'];
$ID             =   $row['ID'];
$title          =   $row['title'];
$username       =   $row['username'];
$uploadDate     =   $row['upload_date'];
$licenseName    =   $row['license_name'];
$licenseID      =   $row['license_ID'];

$template->navBarAddItem('FabMedia',$URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($language->get('media', 'showimageShowImage') . ' ' . $title );

$this->addTitleTag($title);
$this->addMetaData('description', htmlentities($description));
$this->addMetaData('robots', 'noindex');

$query = "SELECT F.filename,
                 F.extension,
                 FMASTER.user_ID,
                 FUSER.username,          
                 G.title as gallery_title,
                 G.ID as gallery_ID,
                 G. trackback
          FROM {$db->prefix}fabmedia_galleries_items AS I
          LEFT JOIN {$db->prefix}fabmedia_galleries_galleries AS G 
              ON I.gallery_ID = G.ID
          LEFT JOIN {$db->prefix}fabmedia AS F
              ON G.cover_ID = F.ID
          LEFT JOIN {$db->prefix}fabmedia_masters AS FMASTER
            ON F.master_ID = FMASTER.ID
          LEFT JOIN {$db->prefix}users AS FUSER
            ON FUSER.ID = FMASTER.user_ID        
          WHERE I.visible = 1
              AND I.image_ID = $ID
          ORDER BY G.order";

$db->setQuery($query);

if (!$resultGalleries = $db->executeQuery($query)) {
    echo 'Query error.' . $query;

    $relog->write(['type'      => '4',
                   'module'    => 'media',
                   'operation' => 'media_show_image_gallery_query_error',
                   'details'   => 'Query error while selecting galleries. ' . $query,
    ]);

    return;
}

if ($db->affected_rows) {

    $galleries = '<div class="row">';
    while ($rowGalleries = mysqli_fetch_assoc($resultGalleries)) {
        $pos = strrpos($rowGalleries['filename'], '.' . $rowGalleries['extension']);

        $imageFinalLQPath = $URI->getBaseUri(true) . 'fabmedia/' . $rowGalleries['user_ID'] . '/' . substr_replace($rowGalleries['filename'], '_lq.' . $rowGalleries['extension'], $pos, strlen('.' . $rowGalleries['extension']));


        $galleries .= '<div class="col-sm">
                            <a href="' . $URI->getBaseUri() . $this->routed . '/gallery/' . $rowGalleries['gallery_ID'] . '-' . $rowGalleries['trackback'] . '/">
                            <img 
                                class="lazy img-fluid" 
                                data-src="' . $imageFinalLQPath . '" 
                                alt="' . htmlentities($rowGalleries['gallery_title']) . '" /><br/>
                            ' . $rowGalleries['gallery_title'] . '
                            </a>
                       </div>';
    }
    $galleries .= '</div>';

}

// Look for similar images
$query = 'SELECT 
            F.user_ID,
            F.ID,
            F.trackback,
            F.filename,
            F.extension,
            F.title
          FROM ' . $db->prefix . 'fabmedia AS F
          LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS FMASTER
            ON F.master_ID = FMASTER.ID
          WHERE LENGTH(F.filename) > 0  
          AND LENGTH(F.extension) > 0 AND 
          ';

$tag_array = explode(', ', $tags);
$query .= '(';
foreach ($tag_array as $single_tag) {
    $query .= 'F.tags = \'' . $single_tag . '\'
                   OR F.tags LIKE \'' . $single_tag . ',%\'
                   OR F.tags LIKE \'%,' . $single_tag . ',\'
                   OR F.tags LIKE \'%,' . $single_tag . '\' OR ';
}
$query = substr($query, 0, -3) . ') AND F.enabled = 1 AND F.ID != ' . $ID . ' LIMIT 6';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error.' . $query;
    return;
} else {
    if (!$db->affected_rows) {
        $similarPhoto = $language->get('media', 'showimageNoOtherSimilarPhotos');
    } else {
        $similarPhoto .= '<div class="row">';

        while ($row = mysqli_fetch_array($result)) {
            $similarPhoto .= '
                <div class="col-sm">
                    <a href="' . $URI->getBaseUri() . $this->routed . '/showimage/' . $row['ID'] . '-' . $row['trackback'] . '/">
                        <img class="lazy fabMediaThumbnail img-thumbnail img-fluid"
                             data-src="' . $fabMedia->getThumbnailPath($row['filename'], $row['extension'], $row['user_ID']) . '" 
                             style="width:90px;" 
                             alt="' . htmlentities($row['title']). '"/>
                    </a>
                </div>';
        }

        $similarPhoto .= '</div>';
    }
}



echo '
<div class="row">
    <div class="col-md-12">
        <input type="text" id="fabMediaSearchKeyword" onkeyup="updateSearch();">
            <img class="lazy"
                alt="search button" onclick="updateSearch();" 
                data-src="' . $URI->getBaseUri(true) . '/modules/media/res/magnifier.png" class="" />
    </div>
</div>

<div id="searchResultContainer" class="row" style="height: 185px;background-color: #EEE; display:none;">
    <div class="col-md-12">
        <div style="overflow: hidden" id="fabMediaSearchResult"></div>
    </div>
</div>

<div class="row" itemscope itemtype="http://schema.org/ImageObject">
    <div class="col-md-12">
        <h1 itemprop="name">' . $title . '</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <img alt="' . htmlentities($title) . '"
             class="lazy img-fluid" 
             itemprop="contentUrl"  
             data-src="' . $URI->getBaseUri(true) . 'fabmedia/' . $user_ID . '/' . $filename . '" />
     </div>
</div>

<div class="row" style="background-color: #EFEFEF; padding: 4px">
    <div class="col-md-6">
        <span class="glyphicon glyphicon-tags"></span> ' . $tags . '
    </div>
    <div class="col-md-6">
        ' . $language->get('media', 'showImageAuthor') . '<meta itemprop="author" content="' . $username . '">: ' . $username . ' |
        ' . $language->get('media', 'showImageUploadDate') . '<meta itemprop="datePublished" content="' . $uploadDate . '">: ' . $core->getDate($uploadDate) . ' |
        ' . ($licenseID > 0 ?
                             '<a href="' . $URI->getBaseUri() . 'licenses/show/' . $licenseID . '/">' . $language->get('media', 'showImageLicense') . ': ' . $licenseName . '</a>'
                             : '' )
  . '</div>
</div>
' . $galleryBar . '
<div class="row">
    <div class="col-md-12">
        <span itemprop="description">' . $description . '</span>
    </div>
</div>';

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

echo '<div class="row">
            <div class="col-sm">
                <div class="fabCMS-Media-ShowImageFromWiki">
                    <h2>' . $language->get('media', 'showimageSimilarPhotos') . '</h2>' .
                    $similarPhoto . '
                </div>      
            </div>
            <div class="col-sm">
                <div class="fabCMS-Media-ShowImageFromWiki">
                    <h2>' . $language->get('media', 'showImageGalleries') .  '</h2>' .
                     $galleries . '
                </div>
            </div>
        </div>
        
       <div class="fabCMS-Media-ShowImageFromWiki">
        <h2>' . $language->get('media', 'showImageWikiLink') . '</h2>' .
         $pagesFromWiki . ' 
       </div>';

$scriptFragment = '
$( document ).ready(function() {
     updateEffects();
});

var toggleSearchStatus = 0;

function updateSearch() {
    var keywords = $("#fabMediaSearchKeyword").val();

    if (keywords.length == 0)
        return;
    if (toggleSearchStatus == 0){
        toggleSearchStatus = 1;
        $("#searchResultContainer").slideDown("slow");
    }
    $.post( "' . $URI->getBaseUri() . 'media/searchmedia/", { keywords: keywords})
        .done(function( data ) {

            $("#fabMediaSearchResult").html(data);
            updateEffects();
      });
}

function updateEffects() {
    $(".fabMediaThumbnail").mouseover(function(){

      $(this).animate({
        width: 180,
        height: 180
      }, 60 );
    });

    $(".fabMediaThumbnail").mouseout(function(){
      $(this).animate({
        width: 90,
        height: 90
      }, 60 );
    });
}';

$stats->write(['IDX' => $ID, 'module' => 'media', 'submodule' => 'showImage']);

$this->addScript($scriptFragment);