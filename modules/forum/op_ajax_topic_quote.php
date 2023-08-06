<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 24/09/2018
 * Time: 12:00
 */

if (!$core->loaded)
    die ("Direct call");

$this->noTemplateParse = true;

if (!isset($_POST['reply_ID'])){
    echo 'Reply ID was not set';
    return;
}

$reply_ID = (int) $_POST['reply_ID'];

$query = 'SELECT TOPIC.topic_message
          FROM ' . $db->prefix . 'forum_topics AS TOPIC
          LEFT JOIN ' . $db->prefix. 'forum_threads AS THREAD
            ON TOPIC.thread_ID = THREAD.ID
          WHERE TOPIC.ID = ' . $reply_ID . ' AND
            TOPIC.visible = 1 AND 
            THREAD.visible = 1 
          LIMIT 1';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo 'Query error';
    return;
}

if (!$db->numRows){
    echo 'No topic'. $query;
    return;
}

$row = mysqli_fetch_assoc($result);

echo $row['topic_message'];