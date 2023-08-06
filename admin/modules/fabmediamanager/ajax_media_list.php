<?php

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$query = 'SELECT MEDIA.ID AS media_ID,
       MASTER.ID master_ID,
       MEDIA.filename,
       MEDIA.type,
       MEDIA.extension,
       MEDIA.subtype,
       MEDIA.size,
       MEDIA.trackback,
       MEDIA.enabled,
       MEDIA.user_ID
FROM ' . $db->prefix .'fabmedia MEDIA
LEFT JOIN ' . $db->prefix .'fabmedia_masters MASTER
ON MASTER.ID = MEDIA.master_ID;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->numRows) {
    echo 'No rows!';
    return;
}

echo '<div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Thumb</th>
                <th>Master ID</th>
                <th>Media ID</th>
                <th>Filename</th>
                <th>Trackback</th>
                <th>Type</th>
                <th>Subtype</th>
                <th>Size</th>
                <th>Enabled</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';

while ($row = mysqli_fetch_assoc($result)) {

    if (
        strlen($row['trackback']) < 1 ||
        strlen($row['filename']) < 1 ||
        strlen($row['subtype']) < 1
       )  {
        $hasError = true;
    } else {
        $hasError = false;
    }
    echo '<tr>';

    if ($row['type'] === 'image') {
        $imagePath = $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'];
        $pos = strrpos($imagePath, '.' . $row['extension']);

        $imagePath = substr_replace($imagePath, '_thumb.' . $row['extension'], $pos, strlen('.' . $row['extension']));
    } else {
        $imagePath = '';
    }

    echo '
            <td style="min-width: 250px; " ' . ($hasError === true ? 'background-color: #FAA' : '') . '">
                <img style="width:50%; height: 50%" src="' . $imagePath . '" />
            </td>
            <td>' . $row['master_ID'] . '</td>
            <td>' . $row['media_ID'] . '</td>
            <td>' . $row['filename'] . '</td>
            <td>' . $row['trackback'] . '</td>
            <td>' . $row['type'] . '</td>
            <td>' . $row['subtype'] . '</td>
            <td>' . human_filesize($row['size'],2) . '</td>
            <td>' . $row['enabled'] . '</td>
            <td><a href="admin.php?module=fabmediamanager&op=edit&ID=' . $row['media_ID'] . '">Edit</a> / <a href="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . '">Download</a></td>
          </tr>';
}

echo '</tbody>
    </table>
</div>';

function human_filesize($bytes, $decimals = 2) {
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0) $sz = 'KMGT';
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
}