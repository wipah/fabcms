<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/10/2017
 * Time: 12:53
 */

set_time_limit(60 * 10);

$this->noTemplateParse = true;

if (!$core->loaded)
    die();

if (!isset($path[3])) {
    echo 'File not passed/found!';
    die();
}

$ID = (int)$path[3];

$query = '
SELECT * 
FROM ' . $db->prefix . 'fabmedia
WHERE enabled = 1 
    AND ID = ' . $ID . '
LIMIT 1';



if (!$result = $db->query($query)) {
    echo 'Query error';
    return;
}

if (!$db->affected_rows) {
    echo 'File not found';
    return;
}

$row = mysqli_fetch_assoc($result);

$file = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'];

if (file_exists($file)) {
    $query = 'INSERT INTO ' . $db->prefix . 'fabmedia_downloads 
              (
                media_ID, 
                user_ID, 
                IP
              )
              VALUES
              (
                ' . $ID . ',
                ' . (is_null($user->ID) === true ? 'null' : $user->ID) . ',
                \'' . $_SERVER['REMOTE_ADDR'] . '\'
              )';

    

    if (!$db->query($query)) {
        $this->noTemplateParse = false;
        echo 'Unable to send the file. We apoligize. ';

        return;
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    $this->noTemplateParse = false;
    echo 'File not found.' . $file;
}