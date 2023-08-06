<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Fabrizio
 * Date: 20/10/12
 * Time: 9.29
 * To change this template use File | Settings | File Templates.
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = TRUE;

if (!empty($_POST['nameFilter'])){
  $where .= 'U.username LIKE \'%' . $core->in($_POST['nameFilter'],true) . '%\'';
}

if (!empty($_POST['emailFilter'])){
  if (strlen($where) > 0 )
      $where .= ' AND ';
  $email = $core->in($_POST['emailFilter']);
  $where .= 'U.email LIKE \'%' . $email . '%\'';
}

if (!empty($_POST['tagFilter'])){
  if (strlen($where) > 0 )
      $where .= ' AND ';
  $tag = $core->in($_POST['tagFilter']);
  $where .= 'T.tag = \'' . $tag. '\'';
}

if ( (int) $_POST['groupFilter'] !== 0 ){
    $group_ID = (int) $_POST['groupFilter'];

    if (strlen($where) > 0 )
        $where .= ' AND ';

    $where .= 'U.group_ID = \'' . $group_ID . '\'';
}

$query = '
SELECT U.*, 
       G.group_name, 
       G.ID AS group_ID, 
       GROUP_CONCAT(DISTINCT T.tag SEPARATOR ", ") AS tags
FROM      ' . $db->prefix . 'users AS U
LEFT JOIN ' . $db->prefix . 'users_groups AS G
ON U.group_ID = G.ID
LEFT JOIN ' . $db->prefix . 'users_tags AS T
ON T.user_ID = U.ID
';

if (!empty($where)){
  $query .= ' WHERE ' . $where;
}

$query .= 'GROUP BY U.ID;';

if (!$result = $db->query($query)){
  echo 'QUERY ERROR: ' . $query;
  return;
}

echo '
	<table class="table table-bordered table-striped table-condensed" id="tableItems" width="100%">
	<thead>
		<tr>
			<th>ID</th>
			<th>Group</th>
			<th>Tags</th>
			<th>Username</th>
			<th>Name</th>
			<th>Email</th>
			<th>Enabled</th>
			<th>Newsletter</th>
			<th>Reg. date</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>';


while ($row = mysqli_fetch_array($result)){
  echo '
    <tr>
         <td>' . $row['ID'] . '</td>
         <td>' . $row['group_name'] . ' (' . $row['group_ID'] . ')</td>
         <td>' . $row['tags'] .'</td>
         <td>' . $row['username'] . '</td>
         <td>' . $row['name'] . ' ' . $row['username'] . '</td>
         <td>' . $row['email'] . '</td>
         <td>' . $row['enabled'] . '</td>
         <td>' . $row['newsletter'] . '</td>
         <td>' . $row['registration_date'] . '</td>
         <td>
            <a href="admin.php?module=user&op=edit&ID=' . $row['ID'] . '">Edit</a>
         </td>
  </tr>';
}

echo '</tbody>
</table>';