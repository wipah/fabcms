<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/09/2017
 * Time: 09:37
 */

if (!$core->loaded)
    die("Direct call");

$this->noTemplateParse = true;

if (!isset($_POST['reply_ID'])){
    echo '<!--error->Reply ID was not passed';
    return;
}
$reply_ID = (int) $_POST['reply_ID'];

// Check if topic is not locked.
if (true === $fabForum->isReplyUnderTopicLocked($reply_ID) && $user->isAdmin === false){
    return '<!--error--> The topic is locked.';
}

$message = $_POST['message'];

if (empty($message)){
    echo '<!--error-->Message is empty';
    return;
}

$message = $fabForum->cleanHtmlCode($message);

$query = '
UPDATE ' . $db->prefix . 'forum_replies
SET reply = \'' . $message . '\',
    is_edited = 1
    ' . ( $user->isAdmin ? ', visible = ' . (int) $_POST['visible'] : '' ) . '
WHERE ID = ' . $reply_ID .
    ($user->isAdmin === false ? ' AND user_ID = ' . $user->ID . ' ' : '') . '
LIMIT 1';

$db->setQuery($query);

if (!$db->executeQuery('update')){
    echo '<!--error-->' . $query;
} else {
    echo '<div class="alert alert-success">
            <strong>' . $language->get('forum', 'topicUpdateOKShort') . '!</strong>' . $language->get('forum', 'topicSaveReplyOk') . '.
          </div>';
}