<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 16/12/2016
 * Time: 11:53
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$language = $core->in($_POST['language'], true);
$title = $core->in($_POST['title']);

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages 
          WHERE title = \'' . $title . '\'
          AND language = \'' . $language . '\'
          LIMIT 1;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo 'Query error';
}

$row = mysqli_fetch_array($result);

if ($db->numRows) {
    echo $row['ID'];
} else {
    echo '0';
}