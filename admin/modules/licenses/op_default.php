<?php

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Licenses', 'admin.php?module=licenses');

echo '<div class="float-right">
        <a href="admin.php?module=licenses&op=crud">Add new license</a>
      </div>';

$query = 'SELECT MASTER.ID AS master_ID,
          LICENSES.ID,
          LICENSES.lang,
          LICENSES.name,
          LICENSES.allow_derivate_works,
          LICENSES.allow_share,
          LICENSES.mandatory_credits
          FROM ' . $db->prefix .'licenses_master AS MASTER
          LEFT JOIN ' . $db->prefix .'licenses_licenses AS LICENSES
            ON LICENSES.master_ID = MASTER.ID;';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No licenses';
    return;
}

echo '
<table class="table table-striped table-bordered table-condensed">
<thead>
    <tr>
      <th>Master ID#</th>
      <th>ID</th>
      <th>Language</th>
      <th>Name</th>
      <th>Derivate works</th>
      <th>Share</th>
      <th>Mandayory credits</th>
      <th>Operations</th>
    </tr>
</thead>
<tbody>
';

while ($row = mysqli_fetch_assoc($result)){
    echo '<tr>
            <td>' . $row['master_ID'] . '</td>
            <td>' . $row['ID'] . '</td>
            <td>' . $row['lang'] . '</td>
            <td>' . $row['name'] . '</td>
            <td>' . ( (int) $row['allow_derivate_works'] === 1  ? 'Yes' :  'No' ) . '</td>
            <td>' . ( (int) $row['allow_share'] === 1  ? 'Yes' :  'No' ) . '</td>
            <td>' . ( (int) $row['mandatory_credits'] === 1  ? 'Yes' :  'No' ) . '</td>
            <td>
                <a href="admin.php?module=licenses&op=crud&ID=' . $row['ID'] .'">Edit</a> | 
                <a href="admin.php?module=licenses&op=crud&master_ID=' . $row['master_ID'] .'">Add new language</a>
            </td>      
          </tr>';
}

echo '</tbody>
</table>';