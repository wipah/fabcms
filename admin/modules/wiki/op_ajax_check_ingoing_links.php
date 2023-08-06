<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 16/12/2016
 * Time: 15:18
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$language = $core->in($_POST['language'], true);
$title = $core->in($_POST['title'], true);
$title_trackback = $core->in($core->getTrackback($title));

$query = 'SELECT P.ID,
                 P.title, 
                 P.trackback,
                 P.visible 
          FROM ' . $db->prefix . 'wiki_outbound_trackback AS T
          LEFT JOIN ' . $db->prefix . 'wiki_pages AS P
          ON T.page_ID = P.ID
          WHERE T.trackback_page_ID = \'' . $title_trackback . '\'
          AND language = \'' . $language . '\'
          ';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->numRows){
    echo 'No ingoing links!';
    return;
}

while ($row = mysqli_fetch_assoc($result)){
    echo '<span style="color:' . ( (int) $row['visible'] == 1 ? 'green' : 'red' ) . '">'. $row['title'] .  '</span> - ';
}