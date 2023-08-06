<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/07/2018
 * Time: 09:11
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBar[] = '<a href="admin.php?module=user">User</a>';
$template->navBar[] = '<a href="admin.php?module=user&op=groups">Groups</a>';

$query = '
SELECT * 
FROM ' . $db->prefix . 'users_groups 
';

$db->setQuery($query);

if (!$result = $db->executeQuery()){
    echo 'Query error.';
    return;
}

echo '<h2>Group list</h2>';


if (!$db->numRows){
    echo 'No row';
} else {
    echo '
        <table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Group name</th>
                <th>Group type</th>
                <th>Operations</th>
              </tr>
            </thead>
         <tbody>';

    while ($row = mysqli_fetch_array($result)) {


        if ( (int) $row['ID'] > 3 ) {
            $operations = '<a href="admin.php?module=user&op=groups&command=edit&ID=' . $row['ID'] . '">Edit</a>';
        } else {
            $operations = ' [System group]';
        }

        switch ( (int) $row ['group_type']){
            case 1:
                $group = 'Admin';
                break;
            case 2:
                $group = 'Registered';
                break;
            case 3:
                $group = 'Guest';
                break;
            default:
                $group = 'NA';
                break;
        }

        echo '
          <tr>
            <td>' . $row['ID'] . '</td>
            <td>' . $row['group_name'] . '</td>
            <td>' . $group . ' (' . $row['group_type'] . ')</td>
            <td>' . $operations . '</td>
          </tr>';
    }

    echo '</tbody>
    </table>';
}

echo '<div class="FabCMS-optionBox clearfix">
	<a class="btn btn-primary float-right" href="admin.php?module=user&op=groups&command=new">New group</a>
</div>';