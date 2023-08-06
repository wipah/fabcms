<?php

switch ($path[3]){
    case 'video':
        require_once 'op_admin_video.php';
        break;
    case 'course':
        require_once 'op_admin_course.php';
        break;
    default:
        require_once 'op_admin_default.php';
        break;
}