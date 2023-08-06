<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/02/2019
 * Time: 14:47
 */

if (!$user->logged)
    die("Not logged");

if (!$user->isAdmin)
    die("Not admin");

switch ($path[3]){
    case 'orders':
        require_once 'op_cp_orders.php';
        break;
    case 'item':
        require_once 'op_cp_item.php';
        break;
    default:
        require_once 'op_cp_default.php';
        break;

}