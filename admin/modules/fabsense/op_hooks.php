<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/03/2017
 * Time: 15:22
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if ($_GET['command'] === 'edit' || $_GET['command'] === 'new'){
    include 'op_hooks_editor.php';
    return;
}

$template->navBarAddItem('FabSense', 'admin.php?module=fabsense');

$query = 'SELECT * 
          FROM ' . $db->prefix . 'sense_hooks';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows){
    echo 'No hooks. <a href="admin.php?module=fabsense&op=hooks&command=new">Create the first hook</a>.';
    return;
}

echo '
<h2>Latest hooks</h2>
<table class="table table-condensed table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Language</th>
        <th>Hook</th>
        <th>Enabled</th>
        <th>Operation</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_array($result)){
    echo '<tr>
            <td>' . $row['ID'] . '</td>
            <td>' . $row['lang'] . '</td>
            <td>' . $row['hook'] . '</td>
            <td>' . $row['enabled'] . '</td>
            <td>
                <a href="admin.php?module=fabsense&op=hooks&command=edit&ID=' . $row['ID'] . '">Edit</a> -
                <a href="admin.php?module=fabsense&op=banner&command=new&hook_ID=' . $row['ID'] . '">Create banner</a> -
            </td>
         </tr>';
}

echo '</tbody></table>';