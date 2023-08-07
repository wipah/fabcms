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

$query = 'SELECT REPLY.reply 
          FROM ' . $db->prefix . 'forum_replies AS REPLY
          LEFT JOIN ' . $db->prefix . 'forum_topics AS TOPIC
          ON TOPIC.ID = REPLY.topic_ID
          LEFT JOIN ' . $db->prefix. 'forum_threads AS THREAD
          ON TOPIC.thread_ID = THREAD.ID
          WHERE REPLY.ID = ' . $reply_ID . ' AND
            REPLY.visible = 1 AND
            TOPIC.visible = 1 AND 
            THREAD.visible = 1 
          LIMIT 1';



if (!$result = $db->query($query)){
    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_reply_quote_select_error',
                   'details'   => 'Unable select the reply. ' . $query,
    ]);

    echo 'Query error';
    return;
}

if (!$db->affected_rows){

    $relog->write(['type'      => '3',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_reply_select_no_reply',
                   'details'   => 'No reply found. ' . $query,
    ]);

    echo 'No reply';
    return;
}

$row = mysqli_fetch_assoc($result);

echo $row['reply'];