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

$this->noTemplateParse = true;

switch ($path[2]){
    case 'search_engine':
        require_once 'op_ajax_search_engine.php';
        break;
    case 'search':
        require_once 'op_search.php';
        break;
    case 'rename':
        require_once 'op_rename.php';
        break;
    case 'select':
        require_once 'op_select.php';
        break;
    case 'searchmedia':
        $template->noTemplateParse = TRUE;
        require_once 'op_ajax_search.php';
        break;
    case 'upload':
        $template->noTemplateParse = TRUE;
        require_once 'op_upload.php';
        break;
    case 'view':
        $template->noTemplateParse = TRUE;
        require_once 'op_view.php';
        break;
    case 'getinfo':
        $template->noTemplateParse = TRUE;
        require_once 'op_getinfo.php';
        break;
    case 'saveinfo':
        $template->noTemplateParse = TRUE;
        require_once 'op_saveinfo.php';
        break;
    case 'deletefile':
        $template->noTemplateParse = TRUE;
        include 'op_deletefile.php';
        break;
    case 'download':
        require_once 'op_download_file.php';
        break;
    case 'init':
    	include 'op_init.php';
    	break;
    case 'add-video':
        require_once 'op_add_video.php';
        break;
    case 'rebuild-trackback':
        require_once 'op_rebuild_trackback.php';
        break;
    case 'admin':
        require_once 'op_admin_default.php';
        break;
    case 'list-video':
        require_once 'op_ajax_video_list.php';
        break;
    default:
        include 'op_default.php';
        break;
}