<?php

/**
 * Expected options are:
 * limit: default is 6
 * tags: tag1, tag2, tag3
 * subtype: image, video, file
 * subtype:
 *
 * @param $options
 *
 * @return string
 */
function plugin_showthumbs($options)
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

    $query = 'SELECT MASTER.type,
                     MASTER.subtype,
                     MASTER.ID AS master_ID,
                     MASTER.filename,
                     MASTER.extension,
                     MEDIA.ID as media_ID,
                     MEDIA.lang,
                     MEDIA.user_ID,
                     MEDIA.trackback,
                     MEDIA.title
              FROM ' . $db->prefix . 'fabmedia_masters AS MASTER
              LEFT JOIN ' . $db->prefix . 'fabmedia AS MEDIA
              ON MEDIA.master_ID = MASTER.ID
    ';

    if (isset($options['limit'])) {
        $limit = ' LIMIT ' . (int) $options['limit'];
    } else {
        $limit = ' LIMIT 6';
    }

    if (isset($options['orderBy'])) {
        $orderBy = ' ORDER BY ' . $core->in($options['orderBy'], true);

        if (isset($options['orderType']))
            $orderBy .= ' ' . $core->in($options['orderType'], true);
    }

    $whereFilter = '';

    if (isset($options['subType']))
        $whereFilter .= ' AND MASTER.subtype = \'' . $core->in($options['subType'], true) . '\' ';

    if (isset($options['tags'])) {
        $tags = explode(', ', $options['tags']);
        $whereFilter . '(';
        foreach ($tags as $singleTag) {

            $singleTag = $core->in($singleTag, true);

            $whereFilter .= 'tag = \'' . $singleTag . '\' OR
                         tag LIKE \', ' . $singleTag . ',%\' OR
                         tag LIKE \'%, ' . $singleTag . ',\' OR
                         tag LIKE \'%, ' . $singleTag . ',%\'';
        }
        $whereFilter . ')';
    }

    $query .= ' WHERE MEDIA.enabled = 1 
                  AND MEDIA.global_available = 1
               
               ' . $whereFilter . ' ' . $orderBy . ' ' . $limit;

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')) {
        echo 'Query error! <pre>' . $query . '</pre>';

        return;
    }

    if (!$db->affected_rows) {
        echo 'No images.';

        return;
    }

    switch ($options['visualization']) {
        case 'rows':
        default:
            $thumbs = showRows($result, $options);
    }

    return $thumbs;

}

function showRows($result, $options)
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

        $return .= '
        <div class="col-md-' . $rowAttribute . '" style="padding:12px; border: 12px solid white;">
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