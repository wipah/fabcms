<?php

if (!$core->loaded)
    die ("Direct access");

$template->navBarAddItem($language->get('licenses', 'licensesLicenses'),$URI->getBaseUri() . $this->routed . '/');

$query = 'SELECT * 
          FROM ' . $db->prefix . 'licenses_licenses' . '
          WHERE lang = \'' . $core->shortCodeLang . '\'';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ';
    return;
}

if (!$db->numRows) {
    echo 'No licenses';
    return;
}
$this->addTitleTag( sprintf($language->get('licenses', 'licensesLicenses'), $conf['site']['name'])  );

echo '<h1>Licenses</h1>';

while ($row = mysqli_fetch_assoc($result)) {
    echo '&bull; <a href="' . $URI->getBaseUri() . $this->routed . '/show/' . $row['ID'] . '/">' . $row['name'] . '</a> <br/>';
}