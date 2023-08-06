<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/03/2017
 * Time: 17:11
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Tag editor', 'admin.php?module=wiki&op=tag');

$query = 'SELECT * 
FROM ' . $db->prefix . 'wiki_tags_menu AS T';

if (!$result = $db->query($query)){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows){
    echo 'No menu.';
}

echo '<table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Language</th>
        <th>Depth</th>
        <th>TAG</th>
        <th>URI</th>
        <th>Name</th>
        <th>Operation</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($result)){
    echo '<tr>
    <td>' . $row['ID'] . '</td>
    <td>' . $row['language'] . '</td>
    <td>' . $row['depth'] . '</td>
    <td>' . $row['tag'] . '</td>
    <td>' . $row['URI'] . '</td>
    <td>' . $row['name'] . '</td>
    <td><a href="admin.php?module=wiki&op=tag&command=edit&ID=' . $row['ID'] . '">edit</a> | <a href="admin.php?module=wiki&op=tag&command=delete&ID=' . $row['ID'] . '">Delete</a></td>
</tr>';
}

echo '</tbody></table>';