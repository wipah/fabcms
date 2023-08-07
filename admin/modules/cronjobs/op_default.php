<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 23/10/2018
 * Time: 12:40
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$query = 'SELECT * 
          FROM ' . $db->prefix . 'cronjobs';

if (!$result = $db->query($query)){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows){
    echo 'No cronjobs';
    return;
}

echo '
<h2>Cronjob manager</h2>
<table class="table table-condensed table-striped">
    <thead>
      <tr>
        <th>Latest status</th>
        <th>Module</th>
        <th>Operation</th>
        <th>Interval</th>

        <th>Latest check</th>
        
        <th>Next run</th>
        <th>Status</th>
        <th>Operations</th>
      </tr>
    </thead>
    
    <tbody>';

while ($row = mysqli_fetch_assoc($result)){

    switch ((int) $row['enabled']){
        case 0:
            $status = 'Disabled';
            break;
        case 1:
            $status = 'Enabled';
            break;
        default:
            $status = 'Unknown';
            break;
    }

    switch ((int) $row['latest_status']){
        case 0:
            $statusClass = 'alert alert-warning';
            $latestStatus = 'No rows';
            break;
        case 1:
            $statusClass = 'alert alert-success';
            $latestStatus = 'Ok';
            break;
        case 2:
            $statusClass = 'alert alert-warning';
            $latestStatus = 'Error';
            break;
        default:
            $statusClass = 'alert alert-warning';
            $latestStatus = 'No status!';
            break;
    }

    echo '<tr>
            <td class="' . $statusClass . '">'. $latestStatus. '</td>
            <td>'. $row['module'] . '</td>
            <td>'. $row['operation'] . '</td>
            <td>'. $row['interval'] . '</td>

            <td>'. $core->getDateTime($row['latest_check']) . '</td>
            <td>'. $row['next_run'] . '</td>
            
            <td>' . $status . '</td>
            <td>
                <a href="admin.php?module=cronjobs&op=edit&ID=' . $row['ID'] .'">Edit</a>
            </td>          
          </tr>';
}

    echo '
    </tbody>
  
  </table>';