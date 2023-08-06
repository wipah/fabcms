<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/11/2016
 * Time: 12:21
 */

if (!$core->adminBootCheck())
    die("Check not passed");

require_once ($conf['path']['baseDir'] . '/modules/wiki/lib/class_wiki.php');

$fabwiki = new wiki();
$fabwiki->loadConfig();

switch ($_GET['op']){
    case 'categories':
        require_once 'op_categories.php';
        break;
    case 'optimizer':
        require_once 'op_optimizer.php';
        break;
    case 'statistics':
        include 'op_statistics.php';
        break;
    case 'piwik_tag_creator':
        include 'op_piwik_tag_creator.php';
        break;
    case 'editComment':
        include 'op_ajax_edit_comment.php';
        break;
    case 'deleteComment':
        require_once 'op_ajax_delete_comment.php';
        break;
    case 'config':
        include 'op_config.php';
        break;
    case 'maintenance':
        include 'op_maintenance.php';
        break;
    case 'rebuildPages':
        include 'op_ajax_rebuild_pages.php';
        break;
    case 'rebuildSeo':
        include 'op_ajax_rebuild_seo.php';
        break;
    case 'seoStatus':
        require_once 'op_ajax_seo_status.php';
        break;
    case 'rebuildFirstTag':
        include 'op_ajax_rebuild_first_tag.php';
        break;
    case 'rebuildTrackbacks':
        include 'op_ajax_rebuild_trackbacks.php';
        break;
    case 'rebuildStats':
        include 'op_ajax_rebuild_stats.php';
        break;
    case 'getUnlinkedPages':
        include 'op_ajax_unlinked_pages.php';
        break;
    case 'getIngoingLinks':
        include 'op_ajax_check_ingoing_links.php';
        break;
    case 'checkTitle':
        include 'op_ajax_check_title.php';
        break;
    case 'checkOutgoingLink':
        include 'op_ajax_check_outgoing_links.php';
        break;
    case 'searchPages':
        include 'op_ajax_search_page.php';
        break;
    case 'getTrackback':
        include 'op_ajax_get_trackback.php';
        break;
    case 'multilangStatus':
        include 'op_ajax_multilang_status.php';
        break;
    case 'savePage':
        include 'op_ajax_save_page.php';
        return;
    case 'editor':
        include 'op_editor.php';
        break;
    case 'showLatestPages':
        include 'op_ajax_show_latest_pages.php';
        break;
    case 'showGlobalStats':
        include 'op_ajax_global_stats.php';
        break;
    case 'tag':
        include 'op_tag.php';
        break;
    case 'checkout':
        include 'op_checkout.php';
        break;
    case 'analytics':
        require_once 'op_analytics.php';
        break;
    case 'associateImages':
        require_once 'op_ajax_associate_images.php';
        break;
    case 'tagAnalyticsSearch':
        require_once 'op_ajax_analytics_tag_search.php';
        break;
    case 'globalStats':
        require_once 'op_ajax_target.php';
        break;
    case 'planner':
        require_once 'op_planner.php';
        break;
    default:
        include 'op_default.php';
        break;
}