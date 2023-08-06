 <?php

if (!$core->loaded)
    die ("Direct call");

$regex = '#([0-9]{1,6})\-([a-z_\-\.0-9]{1,255})#is';

if (!preg_match($regex, $path[2], $matches)){
    echo $language->get('forum', 'threadNoThread', null);
    return;
}

$template->sidebar.= $template->simpleBlock($language->get('forum', 'threadSearchSideBar', null), '<!--FabCMS-hook:forumLateralBeforeSearchBox--><form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="" />
                                                   <button class="button" type="submit">' . $language->get('forum', 'threadSearchSideBar', null) . '</button>      
                                             </form><!--FabCMS-hook:forumLateralAfterSearchBox-->');

$template->sidebar .= $template->simpleBlock($language->get('forum', 'sideBarOperations'), '&bull; <a href="' . $URI->getBaseUri() . '/forum/cp/">' . $language->get('forum', 'cpControlPanel') . '</a>');

$thread_ID = (int) $matches[1];
$thread_trackback = $core->in($matches[2]);

$query = 'SELECT * 
          FROM ' . $db->prefix . 'forum_threads 
          WHERE ID = ' . $thread_ID . ' 
            AND thread_trackback = \'' .$thread_trackback . '\'
            AND visible = 1
          LIMIT 1;';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_thread_select_query_error',
                   'details'   => 'Unable to select the threads. ' . $query,
    ]);
    echo 'Query error.';
    return;
}

if (!$db->numRows){
    echo $language->get('forum', 'threadThreadNotFound', null);
    return;
}

$rowThread = mysqli_fetch_assoc($result);

$this->addTitleTag($rowThread['thread_name']);

$template->navBarAddItem('Forum',$URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($rowThread['thread_name'],$URI->getBaseUri() . $this->routed . '/' . $thread_ID . '-' . $thread_trackback . '/' );

echo '
<h1>' . $rowThread['thread_name'] . '</h1>
<div class="fabForum-actionBar">
    <a href="' . $URI->getBaseUri() . $this->routed. '/new-topic/?thread_ID=' . $rowThread['ID'] . '" type="button" class="btn btn-success float-right">' . $language->get('forum', 'threadNewTopicButton', null) . '</a>          
    <div class="clearfix"></div>
</div>';

$query = '
SELECT T.topic_trackback,
       T.thread_ID,
       T.ID,
       T.date_created,
       T.date_latest_update,
       T.tags,
       T.replies,
       T.topic_title,
       T.latest_reply_user_ID,
       T.locked,
       T.pinned,
       U.username,
       T.moved_topic_ID,
       T.moved_topic_trackback,
       T.moved_thread_ID,
       T.moved_thread_trackback,
       UL.username AS username_latest
FROM fabcms_forum_topics AS T
      LEFT JOIN fabcms_users AS U
ON T.user_ID = U.ID
      LEFT JOIN fabcms_users AS UL
ON T.latest_reply_user_ID = UL.ID
      WHERE T.thread_ID = ' . $thread_ID . '
ORDER BY T.PINNED DESC, 
         T.date_latest_update DESC, 
         T.ID DESC';


 $db->setQuery($query);
if (!$result = $db->executeQuery('select')){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_thread_select_topics_query_error',
                   'details'   => 'Unable to select the topcis. ' . $query,
    ]);

    echo 'Query error';
}

if (!$db->numRows){
    echo $language->get('forum', 'threadNoTopic', null);
    return;
}

echo '
<div class="container">
    <div class="row fabForum-topicHeader">
        <div class="col-md-5">' . $language->get('forum', 'threadTopicName', null) . '</div>
        <div class="col-md-2">' . $language->get('forum', 'threadNumberOfReplies', null) . '</div>
        <div class="col-md-5">' . $language->get('forum', 'threadLastReplyBy', null) . '</div>
    </div>';

while ( $row = mysqli_fetch_assoc($result) ) {

    if ( is_null($row['moved_topic_ID']) )
    {
        $theLink = '<a href="' . $URI->getBaseUri() . $this->routed . '/'
            . $thread_ID . '-' . $thread_trackback
            . '/' . $row['ID'] . '-' . $row['topic_trackback'] .'/">' . $row['topic_title'] . '</a>';
    } else {
        $theLink = '[' . $language->get('forum', 'topicTopicMoved', null) . '] ' .
            '<a href="' . $URI->getBaseUri() . $this->routed . '/'
            . $row['moved_thread_ID'] . '-' . $row['moved_thread_trackback']
            . '/' . $row['moved_topic_ID'] . '-' . $row['moved_topic_trackback'] .'/">' . $row['topic_title'] . '</a>';;
    }

    // If user is an admin show the delete topip option
    if ($user->isAdmin){
        $theLink .= '<span onclick="deleteTopic(' . $row['ID'] .');" class="float-right"><img class="img-fluid fabForum-icon32" src="' . $URI->getBaseUri(true) . 'modules/forum/res/icon_delete_256.png" alt="delete"></span>';
    }

    if ( is_null($row['latest_reply_user_ID']) || (int) $row['latest_reply_user_ID'] ==  0 || is_null($row['latest_reply_user_ID']) )
    {
        $lineReply = $language->get('forum', 'threadNoReplyOnThread', null);
    } else {
        $lineReply = sprintf( $language->get('forum', 'threadLastReplyInfo', null), $row['username_latest'], $row['date_latest_update']) ;
    }

    echo '
    <div class="row ' .  ( (int) $row['pinned'] === 1  ? 'fabForum-topicPinned' : 'fabForum-topicEntries' ) . '">
        <div class="col-md-5">
            ' . $theLink . '
        </div>
        <div class="col-md-2">' . $row['replies'] . '</div>
        <div class="col-md-5">' . $lineReply      . '</div>
    </div>';
}

echo '</div>';

$theScript = '
function deleteTopic(topic_ID) {
    if (confirm("Are you sure you want to delete the topic?")) {
        location.href = "' . $URI->getBaseUri() . $this->routed . '/delete-topic/?topic_ID=" + topic_ID;
    }
}';

$this->addScript($theScript);

$stats->write(['IDX' => $thread_ID, 'module' => 'forum', 'submodule' => 'threadView']);