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
require_once ($conf['path']['baseDir'] . 'lib/seo/class_seo.php');
set_time_limit(2400);

echo 'Rebuilding SEO. <br/>';

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages
          WHERE (service_page = 0 OR service_page IS NULL)';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;
    return;
}

$i = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $ID = $row['ID'];
    echo '] Page ' . $row['title'] . ' (ID: ' . $ID . ')<br/>';

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_seo 
              WHERE page_ID = ' . $ID;

    $resultKeywords = $db->query($query);
    $keywords = [];
    if (!$db->numRows) {
        $keywords[] = $core->in($row['title']);
    } else {
        while ($rowKeywords = mysqli_fetch_assoc($resultKeywords)) {
            $keywords[] = $rowKeywords['keyword'];
        }
    }

    $fabwiki->updateSeoKeywords($ID, $row['content'], $row['metadata_description'], $keywords);
    $fabwiki->updateSeoFirsKeyword($ID);

    unset($keywords);
    $i++;
}

echo sprintf('%d pages updated', $i);