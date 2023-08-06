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

$where = '';

if (!empty($_POST['type'])) {
    $where .= '( type IN (';

    foreach ($_POST['type'] as $type) {
        $where .= (int)$type . ', ';
    }

    $where = substr($where, 0, -2) . ')';

    $where .= ')';
}


if (!empty($_POST['module'])) {
    if (!empty($where))
        $where .= ' AND ';

    $where .= '( module IN (';

    foreach ($_POST['module'] as $module) {
        $where .= '\'' . $core->in($module) . '\', ';
    }

    $where = substr($where, 0, -2) . ')';
    $where .= ')';
}

if (!empty($_POST['fromDate'])) {
    if (!empty($where))
        $where .= ' AND ';

    $where .= 'date >= \'' . $core->in($_POST['fromDate']) . ' 00:00:00\'';
}


if (!empty($_POST['toDate'])) {
    if (!empty($where))
        $where .= ' AND ';

    $where .= 'date <= \'' . $core->in($_POST['toDate']) . ' 23:59:59\'';
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'relog ' . (!empty($where) ? 'WHERE ' . $where : '') . ' 
          ORDER BY ID ASC';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;

    return;
}

if (!$db->affected_rows) {
    echo 'No rows';

    return;
}


echo '<table id="tableRelog" class="table table-bordered table-striped table-condensed">
    <thead>
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Module</th>
        <th>Operation</th>
        <th>User ID</th>
        <th>Page</th>
        <th>Operations</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($result)) {

    switch ((int)$row['type']) {
        case 0:
            $bgColor = '#a3c6ff';
            break;
        case 1:
            $bgColor = '#bafff0';
            break;
        case 2:
            $bgColor = '#bafff0';
            break;
        case 3:
            $bgColor = '#f9f898';
            break;
        case 4:
            $bgColor = '#ea9269';
            break;

    }

    echo '<tr>
        <td>' . $row['date'] . '</td>
        <td style="background-color: ' . $bgColor . '">' . $row['type'] . '</td>
        <td>' . $row['module'] . '</td>
        <td>' . $row['operation'] . '</td>
        <td>' . $row['user_ID'] . '</td>
        <td>' . $row['page'] . '</td>
        <td><span onclick="show(' . $row['ID'] . ');">Show</span></td>
      </tr>';
}

echo '
    </tbody>
  </table>';