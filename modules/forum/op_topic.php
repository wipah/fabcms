<?php

if (!$core->loaded)
    die("direct");

$regex = '#([0-9]{1,6})\-([a-z_\-\.0-9]{1,255})#is';

if (!preg_match($regex, $path[2], $matches)){
    echo 'No thread';
    return;
}

$thread_ID = (int) $matches[1];
$thread_trackback = $core->in($matches[2]);

// Check if we should use recaptcha

if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1
     && $fabForum->getReplyTopicCountByUser($user->ID) < 5) {
    $this->addJsFile('https://www.google.com/recaptcha/api.js', false, true);
    $useRecaptacha = true;
}


if (!preg_match($regex, $path[3], $matches)){
    echo 'No post';
    return;
}

if ($fabForum->userBanned == true){
    echo '<div class="topicBanInfo">
            Dear user, you have been banned from this forum. The ban started on ' . $fabForum->banStart . ' and will finish ' . $fabForum->banEnd . '. <br> The reason is as follow:  ' . $fabForum->banReason . '
          </div>';
}

$topic_ID = (int) $matches[1];
$topic_trackback = $core->in($matches[2]);

if (isset($path[4])){
    $currentPage = (int) $path[4];
} else {
    $currentPage = 1;
}

if ($user->logged)
    $fabForum->setTopicSubscription($topic_ID, $user->ID, true);

foreach ($fabForum->parsers AS $parser){
    require_once __DIR__ . '/parsers/parser_' . $parser . '.php';
}

$query = '
SELECT *, 
  T.user_ID as topic_user_ID, 
  U.username AS topic_username, 
  SUBSCRIPTION.status AS subscription_status,
  IF (ban_end_date >= NOW(), 1, 0) AS ban_status, T.locked AS topic_locked
FROM ' . $db->prefix . 'forum_topics AS T
LEFT JOIN ' . $db->prefix . 'users AS U
    ON U.ID = T.user_ID
LEFT JOIN ' . $db->prefix . 'forum_threads AS TH
    ON T.thread_ID = TH.ID
LEFT JOIN ' . $db->prefix . 'forum_user_stats AS S
    ON S.user_ID = T.user_ID
LEFT JOIN ' . $db->prefix . 'forum_user_config AS UC
    ON UC.user_ID = T.user_ID
LEFT JOIN ' . $db->prefix . 'forum_bans AS B
    ON T.user_ID = B.user_ID
LEFT JOIN ' . $db->prefix . 'forum_signatures AS SIGNATURE
    ON T.user_ID = SIGNATURE.user_ID
LEFT JOIN ' . $db->prefix . 'forum_subscriptions AS SUBSCRIPTION
    ON SUBSCRIPTION.topic_ID = T.ID    
WHERE T.ID = ' . $topic_ID . '
    AND T.topic_trackback = \'' . $topic_trackback. '\'
    AND T.visible = 1
    AND TH.visible = 1
    AND TH.ID = ' . $thread_ID . '
    AND TH.thread_trackback = \'' . $thread_trackback . '\'
LIMIT 1';


if (!$result = $db->query($query)){
    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_select_topics_query_error',
                   'details'   => 'Unable to select the topics. ' . $query,
    ]);

    echo 'Query error while select topics';
    return;
}

if (!$db->affected_rows){

    $relog->write(['type'      => '3',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_select_topics_no_topic',
                   'details'   => 'No topics were found. ' . $query,
    ]);

    echo $language->get('forum', 'topicNoTopic', null);
    return;
}

$row = mysqli_fetch_assoc($result);

if (!is_null($row['moved_topic_ID'])) {
    $redirect = $URI->getBaseUri() . $this->routed . '/' .
        $row['moved_thread_ID'] . '-' . $row['moved_thread_trackback'] . '/' . $row['moved_topic_ID'] . '-' . $row['moved_topic_trackback'] . '/';

    $this->noTemplateParse = true;
    header('Location: ' . $redirect);
    return;
}

// Check if user has a valid subscription for this forum
if ($user->logged && (int)$row['subscription_status'] === 1)
{
    $fabForum->setTopicSubscription($topic_ID, $user->ID);
}

// If topic is moved then redirect
$fabForum->topicLocked = (int) $row['topic_locked'];

$template->navBarAddItem('Forum',$URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($row['thread_name'],$URI->getBaseUri() . $this->routed . '/' . $thread_ID . '-' . $thread_trackback . '/');
$template->navBarAddItem($row['topic_title']);

$template->sidebar.= $template->simpleBlock($language->get('forum', 'topicSearchSideBar', null),'<!--FabCMS-hook:forumLateralBeforeSearchBox--><form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="">
                                                    <button class="button" type="submit">' . $language->get('forum', 'topicSearchSideBar', null) . '</button>      
                                              </form><!--FabCMS-hook:forumLateralAfterSearchBox-->');

$template->sidebar .= $template->simpleBlock($language->get('forum', 'sideBarOperations'), '&bull; <a href="' . $URI->getBaseUri() . '/forum/cp/">' . $language->get('forum', 'cpControlPanel') . '</a>');

echo '
<!--FabCMS-hook:forumTopicBeforeTitle-->
<div class="fabForum-TopicTitle">
    <div class="fabForum-TopicInfo float-right">' . sprintf($language->get('forum', 'topicTopicInfoPostedBy', null), $row['topic_username'], $row['date_created']) . '</div>
    <h1>' . $row['topic_title'] . '</h1>
</div>
<!--FabCMS-hook:forumTopicAfterTitle-->';

$date = date_create($row['date_created']);
$dateFormatted = date_format($date, "m/d/Y \a\\t H:i:s");

if ($user->isAdmin){

    if ( (int) $row['topic_locked'] === 1){
        $modBar = '<span id="topicModLock" onclick="unlockTopic();">Unlock topic</span>';
    } else {
        $modBar = '<span id="topicModLock" onclick="lockTopic();">Lock topic</span>';
    }

    $IP = '[' . $row['IP'] . ']';

    $theScript = '
    function lockTopic() 
    {
        $.post( "' . $URI->getBaseUri() . $this->routed . '/lock-topic/", { ID: ' . $topic_ID . ' })
            .done(function( data ) {
                $("#topicModLock").html("Unlock topic");
        });
    }
    
    function unlockTopic() 
    {
        $.post( "' . $URI->getBaseUri() . $this->routed . '/unlock-topic/", { ID: ' . $topic_ID. ' })
            .done(function( data ) {
                $("#topicModLock").html("Unlock topic");
        });
    }';

    $this->addScript($theScript);
}

// Build pagination
$pagesCount = ceil($row['replies'] /  PAGINATION);
if ($pagesCount > 1){
    $pagination = '<div class="pagination">' . $language->get('forum', 'topicPaginationGoToPage', null) . ': ';

    for ( $i = 1; $i <= $pagesCount; $i++ ) {
        if ($i == $currentPage){
            $pagination .= $i . ' - ';
        } else {
            $pagination .= '<a href="' . $URI->getBaseUri() . $this->routed . '/' . $thread_ID . '-' . $thread_trackback . '/' . $topic_ID . '-' . $topic_trackback . ($i !== 1 ? '/' .$i :'') . '/">' . $i . '</a> - ';
        }

        $pagination = substr($pagination, 0, -2);
    }

    $pagination .= '</div>';
}
$this->addTitleTag($row['topic_title'] . ( !empty($row['tags']) ? ' (' . $row['tags'] . ')' : '' ) );


if (!$user->logged)
    echo '<!--FabCMS-hook:forumTopicUserNotLoggedBeforeTopic-->';

echo '
<div class="topicModBar">' . $modBar . '</div>';

// Check if the current page is 1, so we have to show the original topic
if ($currentPage == 1){


    $topicMessage = $fabForum->execParsers($row['topic_message']);


    echo '
        <!--FabCMS-hook:forumTopicBeforeFirstTopicBox-->
        <div class="container">
            <div class="row fabForum-topic">
                <div class="col-md-3">' . $fabForum->renderAuthorBox( array(
                    'user_ID'     => $row['user_ID'],
                    'username'    => $row['username'],
                    'replyCount'  => $row['reply_count'],
                    'topicCount'  => $row['topic_count'],
                    'banStatus'   => $row['ban_status'],
                    'avatar'      => $row['user_avatar'],
                    'avatar_type' => $row['user_avatar_type'],
                        )
                    )
                    . '</div>
                
                <div class="col-md-9">    
                    <div class="topicHeader">
                        ' . $language->get('forum', 'topicPostedOn', null) . $dateFormatted . ' ' . $IP . '
                        ' . (
                                $user->isAdmin || (int) $row['topic_user_ID'] === $user->ID
                                    ? ' <a class="float-right" href="' . $URI->getBaseUri() . $this->routed .'/edit-topic/?topic_ID=' . $topic_ID . '">
                                            <img class="img-fluid fabForum-icon32" src="' . $URI->getBaseUri(true) . 'modules/forum/res/icon_edit_256.png" alt="Quote">
                                        </a>'
                                    : ''
                            ) .
            (
            $user->isAdmin
                ? ' <span onclick="confirmDeleteTopic(' . $topic_ID . ')" class="float-right">
                        <img class="img-fluid fabForum-icon32" src="' . $URI->getBaseUri(true) . 'modules/forum/res/icon_delete_256.png" alt="delete">
                    </a>'
                : ''
            ) . '
                        
                        <span onclick="quoteBlockTopic(\'' . $topic_ID .'\', \'' . $row['username'] . '\', \'' . $row['date_created'] .'\');" class="float-right">
                        <img class="img-fluid fabForum-icon32" src="' . $URI->getBaseUri(true) . 'modules/forum/res/icon_quote_256.png" alt="Quote">
                        </span>
                    </div>
                    
                    <div class="topicBody"><div id="msgID-T1">'
                    . utf8_encode( $topicMessage  ) . '</div><hr />
                        ' . $row['signature'] . '
                    </div>
                </div>
            </div>
            <!--FabCMS-hook:forumTopicAfteterFirstTopicBox-->';
}

$limitFrom = (($currentPage - 1) * PAGINATION);

if ($limitFrom < 0)
    $limitFrom = 0;

/*
 * *****************************
 *
 *      ****************
 *        START REPLIES
 *      ****************
 *
 * **********************
 */

$query = '
SELECT *, R.user_ID AS user_ID, R.ID as reply_ID, 
  IF ( ban_end_date >= NOW(), 1, 0 ) AS ban_status
FROM ' . $db->prefix . 'forum_replies AS R
LEFT JOIN ' . $db->prefix . 'users AS U
    ON U.ID = R.user_ID
LEFT JOIN ' . $db->prefix . 'forum_user_stats AS S
    ON S.user_ID = R.user_ID
LEFT JOIN ' . $db->prefix  . 'forum_user_config AS UC
    ON UC.user_ID = R.user_ID
LEFT JOIN fabcms_forum_bans AS B
    ON R.user_ID = B.user_ID
LEFT JOIN ' . $db->prefix . 'forum_signatures AS SIGNATURE
    ON R.user_ID = SIGNATURE.user_ID
WHERE R.topic_ID = ' . $topic_ID . '
AND R.visible = 1
ORDER BY R.ID ASC
LIMIT ' . $limitFrom . ', ' . PAGINATION . ';';


if (!$result = $db->query($query)){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_replies_select_query error',
                   'details'   => 'Unable to select replies. ' . $query,
    ]);

    echo 'Query error while select replies.';
    return;
}

if ($db->affected_rows) {

    while ($row = mysqli_fetch_assoc($result)) {

        $date = date_create($row['date']);
        $dateFormatted = date_format($date, "m/d/Y \a\\t H:i:s");

        if ($user->isAdmin) {
            $IP = '[' . $row['IP'] . ']';
        }

        $replyMessage = $fabForum->execParsers($row['reply']);

        echo '
            <!--FabCMS-hook:forumTopicBeforeReplyBox-->
            <div class="row fabForum-reply">
            
                <div class="col-md-3">' . $fabForum->renderAuthorBox( array(
                        'user_ID' =>  $row['user_ID'],
                        'username' => $row['username'],
                        'replyCount' => $row['reply_count'],
                        'topicCount' => $row['topic_count'],
                        'banStatus' => $row['ban_status'],
                        'avatar' => $row['user_avatar'],
                        'avatar_type' => $row['user_avatar_type'],
                    )
                )
                . '</div>
                
                <div class="col-md-9">
                    <div class="replyHeader"><a name="reply-' . $row['reply_ID'] . '"></a>
                        ' . $language->get('forum', 'topicPostedOn', null) . $dateFormatted . ' ' . $IP . '
                    
                    ' . (
                    $user->isAdmin || (int) $row['user_ID'] === $user->ID
                        ? ' <a class="float-right" href="' . $URI->getBaseUri() . $this->routed .'/edit-reply/?reply_ID=' . $row['reply_ID'] . '">[ Edit ]</a> | '
                        : ''
                    ) .
                    '
                    
                    <span onclick="quoteBlockReply(\'' . $row['reply_ID'] . '\', \'' . $row['username'] . '\', \'' . $row['date'] .'\');" class="float-right">[ Quote ]</span>
                    
                    </div>
                    <div class="replayBody">
                        <div id="replyID-' . $row['reply_ID'] .'">' . utf8_encode($replyMessage) . '</div>
                        <hr/>
                        ' . $row['signature'] . '
                    </div>
                </div>
            
            </div>
            <!--FabCMS-hook:forumTopicAfterReplyBox-->';
    }
} else {
    echo '<div class="row topicNoReply">
            <div class="col-md-12">' .
                $language->get('forum', 'topicNoReplyAfterTopic', null) . '
            </div>
          </div>';
}

echo $pagination;

if (!$user->logged)
    echo '<!--FabCMS-hook:forumTopicUserNotLoggedBottom-->';

if ( $fabForum->topicLocked !== 1 && $fabForum->userBanned !== 1 && ($user->logged || $user->isAdmin) ) {

    echo '<!--FabCMS-hook:forumTopicBeforeReply-->
        <div class="row replyForm">
            
            <div class="col-md-12">
                <h2>' . $language->get('forum', 'topicReply', null) . '</h2>
                <textarea id="reply"></textarea>
            </div>';

if ( $useRecaptacha) {
    echo '
    <div class="form-group">
        <label class="control-label col-sm-2" for="g-recaptcha">' . $language->get('forum', 'forumRecaptchaCode') . '</label>
        <div class="col-sm-10">
            ' . $core->reCaptchaGetCode() . '
        </div>
    </div>';
}

            echo '
            <button id="postButton" class="button" onclick="savePost();">' . $language->get('forum', 'topicReply', null) .'</button>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div id="replyStatus"></div>
            </div>
        </div>
        <!--FabCMS-hook:forumTopicAfterReply-->
        
        </div> <!--closing container-->';

    $this->addJsFile('//cdn.tinymce.com/4/tinymce.min.js',true);

    $theScript = '
    
    function confirmDeleteTopic(topic_ID){
        if (confirm("Are you sure you want to delete the topic?"))
            location.href = "' . $URI->getBaseUri() . $this->routed . '/delete-topic/?topic_ID=" + topic_ID;
    }
    
    function quoteBlockReply(msgID, author, date) {        
       
        $.post( "' . $URI->getBaseUri(). 'forum/reply_quote/", { reply_ID: msgID})
        
        .done(function( data ) {
            message = "<blockquote id=\'_mce_temp_rob\'><strong class=\'fabForum-citeInfo\'>" + author + " ' . $language->get('forum', 'opTopicQuoteDate', null) . ' " + date + "</strong>";
            message = message + data;
            message = message +  "</blockquote><br/>"
            tinymce.activeEditor.execCommand(\'mceInsertContent\', false, message); 
        });   
    }
    
    function quoteBlockTopic(msgID, author, date) {        
       
        $.post( "' . $URI->getBaseUri(). 'forum/reply_topic/", { reply_ID: msgID})
        
        .done(function( data ) {
            message = "<blockquote id=\'_mce_temp_rob\'><strong class=\'fabForum-citeInfo\'>" + author + " ' . $language->get('forum', 'opTopicQuoteDate', null) . ' " + date + "</strong>";
            message = message + data;
            message = message +  "</blockquote><br/>"
            tinymce.activeEditor.execCommand(\'mceInsertContent\', false, message); 
        });   
    }
    
    function savePost() {
        topic_ID = ' . $topic_ID . ';
        message = tinymce.activeEditor.getContent();
        
        $.post( "' . $URI->getBaseUri() . $this->routed . '/reply-save/", { topic_ID                :   topic_ID, 
                                            ' . ($useRecaptacha === true ? 'grecaptcharesponse      :   grecaptcha.getResponse(),' : '') . '
                                                                            message                 :   message })
            .done(function( data ) {
            if (data.startsWith("<!--error-->")){
                $("#replyStatus").html(data);
            } else {
                $("#replyStatus").html(data);
                eval(data);
                $("#postButton").remove();
            }
        });
    }

    $( document ).ready(function() {
        tinymce.init({
          selector: \'textarea\',
          height: 320,
          theme: \'modern\',
          plugins: [
            \'advlist autolink lists link image charmap hr anchor pagebreak\',
            \'searchreplace wordcount visualchars code \',
            \'media nonbreaking table contextmenu directionality\',
            \'emoticons paste textcolor colorpicker textpattern imagetools codesample toc\'
          ],
          toolbar1: \'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image\',
          toolbar2: \'print preview | forecolor backcolor emoticons | codesample\',
          image_advtab: true,
          content_css: [
            \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
            \'//www.tinymce.com/css/codepen.min.css\'
          ]
         });
    });';
    $this->addScript($theScript);
} else {
    if (!$user->logged){

        if (in_array('forumTopicNoReplyNoUserLogged', $template->hooks)) {
            echo '<!--FabCMS-hook:forumTopicNoReplyNoUserLogged-->';
        } else {
            echo '<div style="margin-top: 24px;" class="well">
                 ' . sprintf($language->get('forum', 'topicCannotReplyUserNotLogged', null), $URI->getBaseUri() . 'user/register/'  ) . '
                 </div>';
        }
    } else {
        echo '<div style="margin-top: 24px;" class="well">
             ' . $language->get('forum', 'topicLocked', null) . '
              </div>';
    }

}

$stats->write(['IDX' => $topic_ID, 'module' => 'forum', 'submodule' => 'topicView']);