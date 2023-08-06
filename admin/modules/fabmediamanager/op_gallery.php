<?php
if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['command']) {
    case 'galleryEditor':
        require_once 'op_gallery_editor.php';
        break;
    default:
        require_once 'op_gallery_default.php';
        break;
}