<?php

if (!$core->loaded)
    die ("Direct call detected");

$this->noTemplateParse = false;

$query = "SELECT G.ID,
                 G.cover_ID,
                 G.title,
                 G.trackback,
                 F.filename,
                 F.enabled,
                 F.title AS cover_title,
                 F.extension,
                 M.user_ID
          FROM {$db->prefix}fabmedia_galleries_galleries AS G
          LEFT JOIN {$db->prefix}fabmedia AS F 
            ON G.cover_ID = F.ID
          LEFT JOIN {$db->prefix}fabmedia_masters AS M 
            ON M.ID = F.master_ID
          WHERE G.visible = 1
          AND G.lang = '{$core->shortCodeLang}'
          ORDER BY G.order;";

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Internal error.';
    $relog->write(['type'      => '4',
                   'module'    => 'media',
                   'operation' => 'fabmedia_manager_gallery_default_query_error',
                   'details'   => 'Query error while selecting galleries. ' . $query,
    ]);

    return;
}

$template->sidebar .= $template->simpleBlock($language->get('media', 'lateralSearchBox'), '
        <form action="' . $URI->getBaseUri() . 'search/simple/' . '" method="post">
            <input name="search" class="form-control" >
            <button class="button button-info float-right" type="submit">Ricerca</button>
        </form>
        ');


$template->navBarAddItem('MediaManager',$URI->getBaseUri() . 'media/' );
$template->navBarAddItem($language->get('media', 'galleryShowImagesMenuGallery', null), $URI->getBaseUri() . 'media/gallery/');

if (!$db->numRows) {
    echo 'No galleries were found';
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

    echo '<div class="col-md-4" style="background-color: #bbdeff; padding:12px; border: 12px solid white;">
            <a href="' . $URI->getBaseUri() . $this->routed . '/gallery/' . $row['ID'] . '-' . $row['trackback'] . '/">
                <img 
                    class="lazy img-fluid" 
                    data-src="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '" 
                    alt="' . htmlentities($row['title']) . '">
                <strong>' . $row['title'] . '</strong>
            </a>
          </div>';

    if ($i === 3) {
        $i = 0;
        echo '</div> <!-- closing row-->';
        $rowOpen = false;
    }
}

if ($rowOpen)
    echo '</div>';