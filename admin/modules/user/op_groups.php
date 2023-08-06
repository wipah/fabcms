<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/07/2018
 * Time: 09:10
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['command']){
    case 'new':
    case 'edit':
        require_once 'op_groups_crud.php';
        break;
    default:
        require_once 'op_groups_default.php';
        break;
}