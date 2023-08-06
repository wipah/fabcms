<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/03/2018
 * Time: 12:45
 */

if (!$core->loaded)
    die("Direct call");

if (!$user->logged)
    die ("You must be logged");

$this->noTemplateParse = false;

echo '<h2>Rebuild trackback tool</h2>';


$query = 'SELECT * 
          FROM ' . $db->prefix . 'fabmedia';
$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo '<pre>' . $query . '</pre>';

    return;
}

if (!$db->numRows) {
    echo 'No row';

    return;
}

$i = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $i++;
    $ID = $row['ID'];
    $trackback = $core->getTrackback($core->in($row['title']));

    $query = 'UPDATE ' . $db->prefix . 'fabmedia 
              SET trackback = \'' . $trackback . '\' 
              WHERE ID = ' . $ID . ' LIMIT 1';

    $db->setQuery($query);
    if (!$db->executeQuery('update')) {
        echo 'Query error. ' . $query;
        die();
    }
}

echo $i . ' rows updated';