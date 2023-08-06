<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/12/2016
 * Time: 11:22
 */
if (!$core->adminBootCheck())
    die("Check not passed");
$this->noTemplateParse = true;

if (!isset($_POST['master_ID'])){
    echo 'No master ID passed';
    return;
}

$master_ID = (int) $_POST['master_ID'];
$language = $core->in($_POST['language'], true);

$query = 'SELECT 
          M.ID AS master_ID,
          P.title,
          P.ID,
          P.trackback, 
          P.language
FROM ' . $db->prefix . 'wiki_pages AS P
LEFT JOIN ' . $db->prefix  . 'wiki_masters AS M
ON P.master_ID = M.ID
WHERE M.ID = ' . $master_ID .'
AND language != \'' . $language . '\'
';

$db->setQuery($query);

if(!$result = $db->executeQuery('select')){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->numRows){
    echo 'No pages. <br/>';
} else {
    while ($row = mysqli_fetch_array($result)){
        echo '[' . $row['language'] . '] <a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['title'] . '</a> <br/>';
    }
}

echo '<a href="admin.php?module=wiki&op=editor&master_ID=' . $master_ID . '">Add language</a>';