<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/01/2018
 * Time: 15:16
 */

if (!$core->loaded)
    die("No way");

if (!$user->isAdmin) {
    echo 'Only admin';
    return;
}

if (!isset($_GET['topic_ID'])) {
    echo 'Topic ID is missing';
}

$topic_ID = (int) $_GET['topic_ID'];


$row = mysqli_fetch_assoc($result);
if (true !== $fabForum->deleteTopic($topic_ID)) {
    echo 'Error';
} else {
    echo '<div class="alert alert-success" role="alert">
  ' . $language->get('forum', 'topicDeleteOk', null) . '
</div>';
    $fabForum->updateReplyCount($topic_ID);

}