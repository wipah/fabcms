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

$title = $core->in($_POST['title'], true);

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages 
          WHERE title LIKE \'%' . $title . '%\' 
          LIMIT 50';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No pages found.';
    return;
}

while ($row = mysqli_fetch_array($result)) {

    if (!empty($row['internal_redirect'])){
        $redirect = ' ->' . $row['internal_redirect'];
    } else {
        $redirect = '';
    }

    echo '&bull; ' . ( (int) $row['visible'] == 0 ? '<del>' : '') .
    ' <a target="_blank" href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' .
            $row['title'] . '</a>' . ( (int) $row['visible'] == 0 ? '</del>' : '' ) . $redirect;
    echo '<br/>';
}