<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/10/2015
 * Time: 00:45
 */

if (!$core->loaded)
    die();

if (!$user->isAdmin)
	die("No direct access.");

$ID = (int) $_POST['ID'];

// Get some basic info
$query = '
SELECT * 
FROM ' . $db->prefix . 'fabmedia 
WHERE
    ID = ' . $ID . ' 
    AND user_ID = ' . $user->ID . ' 
LIMIT 1;';


if (!$result = $db->query($query)) {
    echo '
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">Query error</h3>
            </div>
            <div class="panel-body">Query error while selecting the file. <pre>' . $query . '</pre></div>
        </div>';

    return;
}

if (!$db->affected_rows) {
    echo '
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Not found</h3>
            </div>
            <div class="panel-body">Cannot delete. No media was found. Media ID is ' . $ID . '</div>
        </div>';

    return;
}
$row = mysqli_fetch_assoc($result);

$filename = $row['filename'];
$type = $row['type'];
$extension = $row['extension'];

// Delete the file
$imagePath = $conf['path']['baseDir'] . 'fabmedia/' . $user->ID . '/' . $filename;

if (!unlink($imagePath)){
    echo 'Cannot delete the main file ' . $imagePath . '. ';
}

$query = 'DELETE FROM ' . $db->prefix . 'fabmedia 
          WHERE ID = \'' . $ID . '\' 
          LIMIT 1;';


if (!$db->query($query)){
    echo '
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">Query error</h3>
            </div>
            <div class="panel-body">Query error while deleting file from the database. <pre>' . $query . '</pre></div>
        </div>';
    return;
}

if ($type === 'image') {

    $pos = strrpos($imagePath, '.' . $extension);

    $imageThumbPath = substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
    $imageThumbMQPath = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
    $imageThumbLQPath = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

    if (!unlink($imageThumbPath))
        echo 'Cannot delete [1] ' . $imagePath . '. ';

    if (!unlink($imageThumbMQPath))
        echo 'Cannot delete [2] ' . $imageThumbMQPath . '. ';

    if (!unlink($imageThumbLQPath))
        echo 'Cannot delete [3] ' . $imageThumbLQPath . '. ';

    $query = 'DELETE 
              FROM ' . $db->prefix . 'fabmedia_images 
              WHERE file_ID = \'' . $ID . '\' 
              LIMIT 1;';

    if (!$db->query($query)){
        echo '
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">Query error</h3>
            </div>
            <div class="panel-body">Query error while deleting the image from the database. <pre>' . $query . '</pre></div>
        </div>';
        return;
    }
} elseif ($type === 'video') {
    $query = 'DELETE FROM ' . $db->prefix . 'fabmedia_videos
          WHERE fabmedia_ID = \'' . $ID . '\' 
          LIMIT 1;';

    $videoPathThumb = $conf['path']['baseDir'] . 'fabmedia/' . $user->ID . '/video_' . $row['user_ID'] . '.jpg';
    unlink($videoPathThumb);

    $db->query($query);
}

echo 'Deleted.
<script type="text/javascript">
    $("#fabMediaFile_' . $ID . '").hide("slow");
</script>
';