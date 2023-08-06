<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/11/2018
 * Time: 17:29
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']) {
    case 'edit':
        require_once 'op_editor.php';
        break;
    case 'ajaxMediaList':
        require_once 'ajax_media_list.php';
        break;
    case 'gallery':
        require_once 'op_gallery.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}