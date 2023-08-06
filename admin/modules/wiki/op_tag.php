<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/03/2017
 * Time: 17:09
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['command']){
    case 'edit':
    case 'new':
        require_once 'op_tag_editor.php';
        break;
    case 'delete':
        require_once 'op_tag_delete.php';
        break;
    default:
        require_once 'op_tag_default.php';
        break;
}
