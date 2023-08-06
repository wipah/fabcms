<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/06/2017
 * Time: 12:02
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->sidebar.= $template->simpleBlock('Quick op.', '&bull;<a href="admin.php?module=wiki">Wiki</a> <br/>
                                                 &bull;<a href="admin.php?module=wiki&op=editor">Editor</a> <br/> 
                                                 &bull;<a href="admin.php?module=wiki&op=categories&command=new">New category</a> <br/> 
                                                 ');


$query = '
SELECT M.ID AS master_ID, D.name, D.ID, D.lang  
  FROM ' . $db->prefix . 'wiki_categories_masters AS M
LEFT JOIN ' . $db->prefix . 'wiki_categories_details AS D
ON D.master_ID = M.ID';

if (!$result = $db->query($query)){
    echo 'Query error.';
    return;
}


echo '<h1>Categories</h1>';

if (!$db->affected_rows){
    echo 'No categories has been saved. <a href="admin.php?module=wiki&op=categories&command=new">Click here</a> to add a category.';
    return;
}

echo '<table class="table">
    <thead>
      <tr>
        <th>Master ID</th>
        <th>Lang</th>
        <th>Name</th>
        <th>Operations</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_array($result)){
    echo ' <tr>
            <td>' . $row['master_ID'] . '</td>
            <td>' . $row['lang'] . '</td>
            <td>' . $row['name'] . '</td>
            <td> 
                <a href="admin.php?module=wiki&op=categories&command=edit&master_ID=' . $row['master_ID'] . '&ID=' . $row['ID'] . '">Edit</a> |  
                <a href="admin.php?module=wiki&op=categories&command=new&master_ID=' . $row['master_ID'] . '">Add a language</a>
            </td>
      </tr>';
}
echo '</tbody>
</table>';