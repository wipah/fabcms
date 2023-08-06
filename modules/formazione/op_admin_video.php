<?php

switch ($path[3]) {
    case 'editor':
        require_once 'op_admin_video_editor.php';
        break;
    default:
        require_once 'op_admin_video_default.php';
        break;
}