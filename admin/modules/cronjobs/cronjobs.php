<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 23/10/2018
 * Time: 12:31
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']){
    case 'edit':
    case 'new':
        require_once 'op_editor.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}
