<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/09/2015
 * Time: 12:56
 */

if (!$core->loaded)
    die("No direct access.");

require_once (__DIR__ . '/lib/class_fabmedia.php');

$fabMedia = new \CrisaSoft\FabCMS\FabMedia();

switch ($path[2]){
    case 'search_engine':
        require_once 'op_ajax_search_engine.php';
        break;
    case 'search':
        require_once 'op_search.php';
        break;
    case 'showmedia':
        require_once 'op_showmedia.php';
        break;
    case 'showimage':
        require_once 'op_showimage.php';
        break;
    case 'searchmedia':
        require_once 'op_ajax_search.php';
        break;
    case 'gallery':
        require_once 'op_gallery.php';
        break;
    default:
        include 'op_default.php';
        break;
}