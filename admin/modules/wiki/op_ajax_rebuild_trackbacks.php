<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/01/2017
 * Time: 19:53
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

set_time_limit ( 1200 );

$query = 'SELECT * FROM ' . $db->prefix . 'wiki_pages';

if (!$result = $db->query($query)){
    echo 'Query error. ' . $query;
    return;
}

$i = 0;
while ($row = mysqli_fetch_assoc($result)){
    $ID = $row['ID'];
    $title = html_entity_decode($row['title']);
    $query = 'UPDATE ' . $db->prefix .'wiki_pages
              SET trackback = \'' . $core->getTrackback($title)  . '\'
              WHERE ID = ' . $ID . '
              LIMIT 1;';

    if (!$db->query($query)){
        echo $query;
        return;
    }
    $i++;
}

sprintf('%d pages updated', $i);