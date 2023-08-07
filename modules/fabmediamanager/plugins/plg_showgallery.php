<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/01/2019
 * Time: 09:02
 */
function plugin_showgallery($options)
{
    global $core;
    global $db;
    global $fabMedia;
    global $lang;
    global $relog;
    global $user;

    if ($user->isAdmin) {
        $return = $options['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($options['parseInAdmin']) && $core->adminLoaded)
        return $return;


    switch (strtolower(['order']))
    {
        default:
            $order = ' ORDER BY gallery_title ASC';
            break;
    }

    $query = '
    SELECT 
	  MASTER.ID AS master_ID,
	  GALLERIES.ID AS gallery_ID,
	  GALLERIES.title AS gallery_title,
	  ITEMS.user_ID,
	  IMAGES.trackback,
      IMAGES.filename,
      IMAGES.extension
    FROM ' . $db->prefix . 'fabmedia_galleries_masters AS MASTER
      LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_galleries AS GALLERIES
    ON GALLERIES.master_ID = MASTER.ID 
      LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_items AS ITEMS
    ON ITEMS.ID = GALLERIES.cover_ID
      LEFT JOIN ' . $db->prefix . 'fabmedia AS IMAGES
    ON IMAGES.ID = GALLERIES.cover_ID
    LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS MASTER_IMAGE
    ON MASTER_IMAGE.ID = IMAGES.master_ID
    WHERE IMAGES.enabled = 1
    ' . $order . '
    ';

    

    if (!$result = $db->query($query)){
        return 'Query error. ' . $query;
    }

    if (!$db->affected_rows){
        return 'No gallery';
    }

    return showRowsGallery($result, $options);
}

function showRowsGallery($result, $options)
{
    global $URI;

    $return = '';

    if (!isset($options['numRows'])) {
        $numRows = 3;
    } else {
        $numRows = (int) $options['numRows'];
    }

    $rowAttribute = floor(12 / $options['numRows']);

    $i = 0;
    while ($row = mysqli_fetch_assoc($result)) {

        if ($i === 0) {
            $return .= '<div class="row">';
            $rowOpen = true;
        }

        $i++;

        $pos = strrpos($row['filename'], '.' . $row['extension']);

        $imageFinalLQPath = substr_replace($row['filename'], '_lq.' . $row['extension'], $pos, strlen('.' . $row['extension']));

        $return .= '<div class="col-md-' . $rowAttribute . '" style="padding:12px; border: 12px solid white;">
            <a href="' . $URI->getBaseUri() . 'fabmediamanager/showimage/' . $row['media_ID'] . '-' . $row['trackback'] . '/">
                <img class="img-fluid" src="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '" alt="' . $row['title'] . '">
                <strong>' . $row['cover_title'] . '</strong>
            </a>
          </div>';

        if ($i === $numRows) {
            $i = 0;
            $return .= '</div> <!-- closing row-->';
            $rowOpen = false;
        }
    }

    if ($rowOpen)
        $return .= '</div>';

    return $return;
}