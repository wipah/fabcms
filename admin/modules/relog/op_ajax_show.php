<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/04/2018
 * Time: 11:34
 */

if (!$core->loaded || !$user->isAdmin)
    die("Security");

$this->noTemplateParse = true;

if (!isset($_POST['ID'])) {
    echo 'ID not passed';

    return;
}

$ID = (int)$_POST['ID'];

$query = 'SELECT * 
          FROM ' . $db->prefix . 'relog 
          WHERE ID = \'' . $ID . '\' LIMIT 1';

$db->setQuery($query);
if (!$result = $db->executeQuery($query)) {
    echo 'Query error';

    return;
}

if (!$db->numRows) {
    echo 'No rows';

    return;
}

$row = mysqli_fetch_assoc($result);
echo '<h3>' . $row['module'] . ' - ' . $row['operation'] . '</h3>
<strong>Page affected</strong>: ' . $row['page'] . ' <br/>
<strong>IP</strong>: ' . $row['IP'] . '<br/>
<hr/>
' . $row['details'] . '
';
