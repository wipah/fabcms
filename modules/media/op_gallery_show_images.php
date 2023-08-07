<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/04/2018
 * Time: 10:44
 */

if (!$core->loaded)
    die("Direct access");

$this->noTemplateParse = false;

$query = "SELECT G.description,
                 G.meta_description,
                 G.title,
                 G.ID,
                 G.trackback,
                 U.ID AS user_ID,
                 U.username,
                 F.filename,
                 F.extension,
                 FI.width,
                 FI.height
          FROM {$db->prefix}fabmedia_galleries_galleries AS G
          LEFT JOIN {$db->prefix}users AS U 
            ON U.ID = G.user_ID
          LEFT JOIN {$db->prefix}fabmedia AS F 
            ON F.ID = G.cover_ID
          LEFT JOIN {$db->prefix}fabmedia_images AS FI 
            ON FI.file_ID = F.ID            
		  LEFT JOIN {$db->prefix}fabmedia_masters AS FM
				ON FM.ID = F.master_ID
          WHERE G.ID = $gallery_ID LIMIT 1";


if (!$result = $db->query($query)) {
    $relog->write(['type'      => '4',
                   'module'    => 'media',
                   'operation' => 'media_gallery_select_gallery_query_error',
                   'details'   => 'Query error while selecting gallery.' . $query,
    ]);

    return;
}

if (!$db->affected_rows) {
    echo 'No gallery was found.';

    return;
}

$rowGallery = mysqli_fetch_assoc($result);

$template->navBarAddItem('MediaManager',$URI->getBaseUri() . 'media/' );
$template->navBarAddItem( $language->get('media', 'galleryShowImagesMenuGallery', null),$URI->getBaseUri() . 'media/gallery/');
$template->navBarAddItem($rowGallery['title']);

echo '<h1>' . htmlentities($rowGallery['title']) . '</h1>';

$template->sidebar .= $template->simpleBlock($language->get('media', 'lateralSearchBox'), '
        <form action="' . $URI->getBaseUri() . 'search/simple/' . '" method="post">
            <input name="search" class="form-control" placeholder="' . $language->get('media', 'showSearchBoxSearchPlaceholder') . '">
            <button class="button button-info float-right" type="submit">Ricerca</button>
        </form>
        ');

/* SEO */
$this->addTitleTag($rowGallery['title']);
$this->addMetaData('description', $row['meta_description']);

echo '<h1>' . $rowGallery['title'] . '</h1>
<div style="padding:4px; background-color: #EAEAEA">' . $rowGallery['description'] . '</div>';

$query = "SELECT I.image_ID,
                 F.filename,
                 F.extension,
                 M.user_ID,
                 F.trackback,
                 F.title
          FROM {$db->prefix}fabmedia_galleries_items AS I
          LEFT JOIN {$db->prefix}fabmedia AS F
            ON I.image_ID = F.ID
          LEFT JOIN {$db->prefix}fabmedia_masters AS M
            ON M.ID = F.master_ID
          WHERE I.visible = 1 
                AND F.enabled = 1 
                AND I.gallery_ID = $gallery_ID
          ORDER BY I.order";


if (!$result = $db->query($query)) {
    echo 'Unable to show images.';
    $relog->write(['type'      => '4',
                   'module'    => 'media',
                   'operation' => 'media_gallery_select_image_query_error',
                   'details'   => 'Query error while selecting images.' . $query,
    ]);

    return;
}

if (!$db->affected_rows) {
    echo 'No images were found';
    return;
}

$i = 0;
$rowOpen = true;

while ($row = mysqli_fetch_assoc($result)) {
    if ($i === 0) {
        echo '<div class="row">';
        $rowOpen = true;
    }

    $i++;

    $pos = strrpos($row['filename'], '.' . $row['extension']);

    $imageFinalLQPath = substr_replace($row['filename'], '_lq.' . $row['extension'], $pos, strlen('.' . $row['extension']));

    echo '<div class="col-md-3" style="background-color: #bbdeff; padding:12px; border: 12px solid white;">
            <a href="' . $URI->getBaseUri() . $this->routed . '/showimage/' . $row['image_ID'] . '-' . $row['trackback'] . '/?gallery_ID=' . $gallery_ID . '">
                <img 
                    class="lazy img-fluid" 
                    data-src="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '" 
                    alt="' . htmlentities($row['title']) . '">
                <strong>' . $row['title'] . '</strong>
            </a>
          </div>';

    if ($i === 4) {
        $i = 0;
        echo '</div> <!-- closing row-->';
        $rowOpen = false;
    }
}

if ($rowOpen)
    echo '</div>';


// Check for the logo
if (file_exists($conf['path']['baseDir'] . 'templates/logo.png')){
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.png';
}

if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpg')){
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.jpg';
}

if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpeg')){
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.jpeg';
}

$imagePath = str_replace("'", "", $rowGallery['filename']);
$extension = str_replace("'", "", $rowGallery['extension']);
$imageFinalLQPath   = $URI->getBaseUri(true) . 'fabmedia/' . $rowGallery['user_ID'] . '/'. substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

$schemaImages = '"image": [ "' .$imageFinalLQPath.'" ],';

/* Open graph */
if ($conf['uri']['useHTTPS']){
    $this->addMeta('og:image', $imageFinalLQPath );
    $this->addMeta('og:image:secure_url', $imageFinalLQPath );
} else {
    $this->addMeta('og:image', $imageFinalLQPath );
}

$this->addMeta('og:image:type', 'image/' . $extension);
$this->addMeta('og:image:alt', $rowGallery['title']);
$this->addMeta('og:image:width', $rowGallery['width']);
$this->addMeta('og:image:height', $rowGallery['height']);

echo PHP_EOL .'<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type"           :   "Article",
  "author"          :   "' . $rowGallery['username'] . '",
  "mainEntityOfPage": {
    "@type"         :   "WebPage",
    "@id"           :   "' . $URI->getBaseUri() . $this->routed . '/gallery/' . $gallery_ID . '-' . $rowGallery['trackback'] . '/"
  },
  "headline"        :   "' . $rowGallery['title'] . '",
  ' . $schemaImages . '
  "publisher"     : {   "@type" : "organization",  
                        "name" :    "' . $conf['organization'] . '", 
                        "logo" :  {
                            "@type" : "ImageObject",
                            "url"   : "' . $logoBrand . '" 
                        }
                    },
  /* "datePublished"   :   "' . $fabwiki->creationDate . '",
  "dateModified"    :   "' . $fabwiki->updateDate . '", */
  "description"     :   "' . str_replace('"', '', $rowGallery['meta_description']) . '"
}
</script>';

$stats->write(['IDX' => $gallery_ID, 'module' => 'media', 'submodule' => 'showGallery']);