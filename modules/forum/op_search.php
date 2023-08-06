<?php

if (!$core->loaded)
    die ("No way");

if (empty($_POST['forumSearch']) && strlen($_POST['forumSearch']) < 3){
    echo 'No key';
    return;
}

$key = $core->in($_POST['forumSearch']);

$template->sidebar.= $template->simpleBlock( $language->get('forum', 'searchSearch', null), '<!--FabCMS-hook:forumLateralBeforeSearchBox--><form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="">
                                                    <button class="button" type="submit">Find</button>      
                                              </form><!--FabCMS-hook:forumLateralAfterSearchBox-->');
$query = '
SELECT T.topic_title,
       T.ID AS topic_ID,
       T.topic_trackback,
       T.date_created,
       T.date_latest_update,
       T.replies,
       T.thread_ID AS thread_ID,
       TH.thread_name,
       TH.thread_trackback,
       U.username,
       ULR.username AS username_latest_reply,
       MATCH(T.topic_title) AGAINST (\'' . $key . '\') AS relevance_topic,
       MATCH(T.topic_message) AGAINST (\'' . $key . '\') AS relevance_topic_test
FROM ' . $db->prefix . 'forum_topics AS T
LEFT JOIN ' . $db->prefix . 'forum_threads AS TH
    ON T.thread_ID = TH.ID
LEFT JOIN ' . $db->prefix . 'users AS U
    ON T.user_ID = U.ID
LEFT JOIN ' . $db->prefix . 'users AS ULR
    ON T.latest_reply_user_ID = ULR.ID  
WHERE 
   TH.visible = 1 AND T.visible = 1
ORDER BY relevance_topic DESC,
         relevance_topic_test DESC
LIMIT 15;';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_search_query_error',
                   'details'   => 'Unable to search the topic. ' . $query,
    ]);

    echo 'Query error.';
}

echo '<h2>' . $language->get('forum', 'searchTopicSearch', null ) . '</h2>';

if (!$db->numRows){
    echo $language->get('forum', 'searchNoTopicResult', null );
}

echo '<table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>' . $language->get('forum', 'searchTopic', null ) . '</th>
        <th>' . $language->get('forum', 'postedBy', null ) . '</th>
        <th>' . $language->get('forum', 'latestReplyBy', null ) . '</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($result)){
    echo '
      <tr>
        <td>
            <a href="' . $URI->getBaseUri() . $this->routed . '/' . $row['thread_ID'] . '-' .$row['thread_trackback'] . '/' . $row['topic_ID'] .'-' . $row['topic_trackback'] . '/">' . $row['topic_title'] . '</a> 
            posted in 
            <a href="' . $URI->getBaseUri() . $this->routed . '/' . $row['thread_ID'] . '-' .$row['thread_trackback'] . '/'  . '">' . $row['thread_name']  . '</a> on ' . $row ['date_created'] .'
        </td>
        <td>' . $row['username'] . '</td>
        <td>' . $row['username_latest_reply']. '</td>
      </tr>';
}

echo '
    </tbody>
  </table>';

echo '<h2>' . $language->get('forum', 'searchReplySearch', null ) . '</h2>';

$query = '
SELECT 
       R.ID AS reply_ID,
       R.user_ID,
       R.topic_ID,
       R.visible,
       R.reply,
       R.date,
       T.topic_title,
       T.ID AS topic_ID,
       T.topic_trackback,
       TH.ID AS thread_ID,
       TH.thread_trackback,
       TH.thread_name,
       U.username,
       U.ID AS user_ID,
       MATCH (R.reply) AGAINST ("' . $key . '")  AS relevance,
       (
         SELECT COUNT(ID) AS position
         FROM ' . $db->prefix . 'forum_replies AS RCOUNT
         WHERE RCOUNT.ID <= R.ID
         AND RCOUNT.visible = 1
         AND RCOUNT.topic_ID = T.ID
       ) AS position
FROM           ' . $db->prefix . 'forum_replies AS R
LEFT JOIN      ' . $db->prefix . 'forum_topics AS T
    ON R.topic_ID = T.ID
LEFT JOIN      ' . $db->prefix . 'forum_threads AS TH
    ON T.thread_ID = TH.ID
LEFT JOIN      ' . $db->prefix . 'users AS U
    ON R.user_ID = U.ID
WHERE R.visible = 1
HAVING relevance > 0
ORDER BY relevance DESC';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_search_select_replies_query_error',
                   'details'   => 'Unable to select replies. ' . $query,
    ]);
    echo 'Query error. ' . $query;
    return;
}

if (!$db->numRows){
    echo $language->get('forum', 'searchNoReplyFound', null);
} else {

    echo '<table class="table table-bordered table-striped">
    
    <thead>
      <tr>
        <th>Topic</th>
        <th>' . $language->get('forum', 'searchReplyData') . '</th>
        <th>' . $language->get('forum', 'searchReply') . '</th>
      </tr>
    </thead>
    
    <tbody>';

    while ($row = mysqli_fetch_assoc($result)){

        if ((int) $row['position'] <= PAGINATION){
            $pagination = '/';
        } else {
            $pagination = '/' . ceil($row['position'] / PAGINATION) . '/';
        }

        echo '
        <tr>
            <td>
                <a href="' . $URI->getBaseUri() . $this->routed .
                             '/' . $row['thread_ID'] . '-' . $row['thread_trackback'] .
                             '/' . $row['topic_ID'] . '-' . $row['topic_trackback'] . $pagination .
                             '#reply-' . $row['reply_ID'] . '">' . $row['topic_title'] . '</a>
            </td>    
            <td>' . $row['date']. '</td>    
            <td>' . $language->get('forum', 'searchPostedBy', null) . $row['username'] . '. <br/>' . substr( strip_tags($row['reply'],1, 200)) .'</td>    
        </tr>';
    }
    echo '</tbody>
        </table>';
}
