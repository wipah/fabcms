<?php

if (!$core->loaded)
    die ("Direct call");

CONST PAGINATION = 5;

require_once (__DIR__ . '/lib/class_forum.php');
$fabForum = new \CrisaSoft\FabCMS\forum();

$this->addCSSLink('forum_default',true);

$pattern = '#([0-9]{1,6})\-([a-z0-9\-\_\:\,]{1,255})#';
if (preg_match($pattern, $path[2]) && !preg_match($pattern, $path[3]) ){
    require_once 'op_thread.php';
    return;
}

$pattern = '#([0-9]{1,6})\-([a-z0-9\-\_\:\,]{1,255})#';
if (preg_match($pattern, $path[2]) && preg_match($pattern, $path[3]) ){
    require_once  'op_topic.php';
    return;
}

switch ( $path[2] ) {
    case 'reply-save':
        require_once 'op_ajax_save_reply.php';
        break;
    case 'reply-update-save':
        require_once 'op_ajax_update_reply_save.php';
        break;
    case 'search':
        require_once 'op_search.php';
        break;
    case 'edit-topic':
    case 'new-topic':
        require_once 'op_topic_crud.php';
        break;
    case 'edit-reply':
        require_once 'op_reply_edit.php';
        break;
    case 'lock-topic':
    case 'unlock-topic':
        require_once 'op_ajax_topic_locking.php';
        break;
    case 'cp':
        require_once 'op_cp.php';
        break;
    case 'delete-topic';
        require_once 'op_delete_topic.php';
        break;
    case 'refresh-topic':
        require_once 'op_refresh_topic.php';
        break;
    case 'reply_quote':
        require_once 'op_ajax_reply_quote.php';
        break;
    case 'reply_topic':
        require_once 'op_ajax_topic_quote.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}