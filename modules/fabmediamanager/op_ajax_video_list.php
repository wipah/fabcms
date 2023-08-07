<?php
if (!$core->loaded)
    die("not loaded");

if (!$user->logged)
    die ("Not logged");

$this->stopTemplateParse = true;

$query = '
SELECT 
    MASTER.ID AS master_ID,
    MEDIA.ID AS media_ID,
    VIDEO.ID AS video_ID,
    MEDIA.title AS title,
    MEDIA.enabled AS enabled,
    VIDEO.length 
FROM ' . $db->prefix . 'fabmedia AS MEDIA
LEFT JOIN ' . $db->prefix . 'fabmedia_videos AS VIDEO
    ON VIDEO.fabmedia_ID = MEDIA.ID
LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS MASTER
    ON MEDIA.master_ID = MASTER.ID';



if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No rows.';
    return;
}

echo '<table class="table table-responsive table-bordered table-striped table-hover">
<thead>
    <tr>
        <th>MASTER ID</th>
        <th>MEDIA ID</th>
        <th>VIDEO ID</th>
        <th>Title</th>
        <th>Length</th>
        <th>Enabled</th>
    </tr>
</thead>
<tbody>';

$totalMinutes = 0;
$totalSeconds = 0;
while ($row = mysqli_fetch_assoc($result)){

    $fragment = explode(':', $row['length']);
    $totalSeconds += $fragment[2];
    $totalMinutes += $fragment[1];
    $totalMinutes += $fragment[0] * 60;


    echo '<tr>
            <td>' . $row['master_ID'] . '</td>
            <td>' . $row['media_ID'] . '</td>
            <td>' . $row['video_ID'] . '</td>
            <td>' . $row['title'] . '</td>
            <td>' . $row['length'] . '</td>
            <td>' . $row['enabled'] . '</td>
            
          </tr>';
}

$totalMinutes = floor($totalMinutes + ($totalSeconds / 60));
echo '
    <tfoot>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>' . $totalMinutes . '</td>
        <td></td>
    </tr>
</tfoot>
    </tbody>
</table>';