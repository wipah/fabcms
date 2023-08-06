<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/03/2017
 * Time: 10:48
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$query = 'SELECT * FROM ' . $db->prefix . 'connectors';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){
    echo 'Query error';
    return;
}

if (!$db->affected_rows){
    echo 'No handler';
    return;
}

echo '<table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Handler</th>
        <th>Connector</th>
        <th>Action</th>
        <th>Additional data</th>
        <th>Order</th>
        <th>Enabled</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_array($result)){
    echo '<tr>
            <td>' . $row['ID'] . '</td>
            <td>' . $row['handler'] . '</td>
            <td>' . $row['connector'] . '</td>
            <td>' . $row['action'] . '</td>
            <td style="' . (isJson($row['additional_data']) ? 'background-color: #CFC;' : 'background-color:FCC' ) . '">' . $row['additional_data'] . '</td>
            <td>' . $row['order'] . '</td>
            <td>' . $row['enabled'] . '</td>
            <td>Edit - delete</td>

          </tr>';
}

echo '</tbody></table>';

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}