<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/06/2017
 * Time: 11:51
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['command']){
    case 'edit':
    case 'new':
        include 'op_categories_editor.php';
        break;
    default:
        include 'op_categories_default.php';
        break;
}