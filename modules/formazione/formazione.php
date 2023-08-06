<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/03/2016
 * Time: 12:20
 */

if (!$core->loaded)
    die ("No access");

include __DIR__ . '/lib/class_formazione.php';
$formazione = new CrisaSoft\FabCMS\formazione;

switch ($path[2]){
    case 'video':
        include 'op_video.php';
        break;
    case 'corso':
        include 'op_course.php';
        break;
    case 'admin-cp':
        require_once 'op_admin.php';
        break;
    default:
        include 'op_homepage.php';
        break;
}