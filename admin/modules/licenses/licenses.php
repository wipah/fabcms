<?php

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']){
    case 'crud':
        require_once 'op_crud.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}