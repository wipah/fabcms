<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/03/2017
 * Time: 15:20
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']){
    case 'hooks':
        require_once 'op_hooks.php';
        break;
    case 'banner':
        require_once 'op_banner.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}