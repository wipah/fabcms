<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/03/2017
 * Time: 15:21
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$query = 'SELECT * 
          FROM ' . $db->prefix . 'sense_hooks';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->numRows){
    echo 'No hooks';
    return;
}

$template->navBarAddItem('FabSense', 'admin.php?module=fabsense');
$template->navBarAddItem('Homepage');

echo '
<h2>FabSense homepage</h2>

<table class="table table-bordered table-sm table-hover">
    <thead class="">
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
                <a href="admin.php?module=fabsense&op=banner&command=new&hook_ID=' . $row['ID'] . '">Create banner</a>
            </td>
            
         </tr>';
}

echo '</tbody>
</table>

<div class="FabCMS-optionBox clearfix">
    <a class="btn btn-primary float-right" href="admin.php?module=fabsense&op=hooks&command=new">New hook</a>
</div>';