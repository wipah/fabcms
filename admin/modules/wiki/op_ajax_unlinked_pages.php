<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/12/2016
 * Time: 12:30
 */

if (!$core->adminBootCheck())
    die("Check not passed");
$this->noTemplateParse = true;

if (empty($_POST['title'])) {
    echo 'No title passed';

    return;
}
$title = $core->in($_POST['title'], true);

if (empty($_POST['language'])) {
    echo 'No language passed';

    return;
}
$language = $core->in($_POST['language'], true);


$query = '
SELECT P.ID, P.title
FROM ' . $db->prefix . 'wiki_pages AS P
WHERE (
        P.content LIKE \'% ' . $title . ' %\' OR
        P.content LIKE \'% ' . $title . '.%\' OR
        P.content LIKE \'% ' . $title . ',%\'   
      )
      
      AND 
      
      (
        P.content NOT LIKE \'%[[' . $title . ']]%\' OR
        P.content NOT LIKE \'%|' . $title . ']]%\' 
      )
        
      AND P.language = \'' . $language . '\'';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo '<pre>' . $query . '</pre>';

    return;
}

if (!$db->numRows) {
    echo 'No unlinked pages';

    return;
}

while ($row = mysqli_fetch_assoc($result)) {
    echo '<a target="_blank" href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['title'] . '</a> - ';
}