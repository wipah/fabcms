<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/07/2018
 * Time: 10:17
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['command']){
    case 'new':
    case 'edit':
        require_once 'op_banner_crud.php';
        return;
    default:
        echo 'No handler';
        return;
}