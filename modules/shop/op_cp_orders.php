<?php


switch ($path[4]){
    case 'view':
        require_once 'op_cp_orders_view.php';
        break;
    default:
        require_once 'op_cp_orders_default.php';
        break;
}