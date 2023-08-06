<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/06/2019
 * Time: 11:13
 */

if (!$core->loaded)
    die ("Direct access");

if (!isset($path[3])){
    echo 'No license ID passed';
    return;
}

$license_ID = (int) $path[3];

$query = 'SELECT * 
          FROM ' . $db->prefix . 'licenses_licenses 
          WHERE ID = ' . $license_ID . ' 
            AND lang = \'' . $core->shortCodeLang. '\'
          LIMIT 1';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo 'Query error. ' . $query;
    return;
}

$template->navBarAddItem($language->get('licenses', 'licensesMenu'),$URI->getBaseUri() . $this->routed . '/');

if (!$db->numRows){
    echo 'No license';
    return;
}

$row = mysqli_fetch_assoc($result);

$this->addTitleTag( sprintf($language->get('licenses', 'licenseShowSingle'), $row['name'] , $conf['site']['name'] ));
$template->navBarAddItem($row['name']);

echo '<h1>' .  $row['name'] .'</h1> ' . $row['description'];

