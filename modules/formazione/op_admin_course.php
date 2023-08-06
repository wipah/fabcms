<?php

if (!isset($path[4])) {
    require_once 'op_admin_course_default.php';
} else {
    switch ($path[5]) {
        case 'video-order':
            require_once 'op_admin_course_video_order.php';
            break;

    }
}
