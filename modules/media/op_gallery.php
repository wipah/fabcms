<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/03/2018
 * Time: 12:27
 */

if (!$core->loaded)
    die("Direct access");

// Check if a gallery has been passed

if (preg_match('/([\d]{1,9})\-([\w\s\-\_\,\.]{1,255})/i', $path[3], $matches)) {
    $gallery_ID = $matches[1];
    $gallery_trackback = $matches[2];

    require_once 'op_gallery_show_images.php';

    return;
}

switch ($path[3]) {
    default:
        require_once 'op_gallery_default.php';
        break;
}