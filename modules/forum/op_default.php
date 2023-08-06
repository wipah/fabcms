<?php

if (!$core->loaded)
    die("No way");

if (empty($fabForum->config['forumTitle'])) {
    $this->addTitleTag("Forum");
} else {
    $this->addTitleTag( $fabForum->config['forumTitle']);
}

echo '<h1>Forum</h1><!--FabCMS-hook:forumDefaultAferH1-->';

$template->sidebar.= $template->simpleBlock('Search','<!--FabCMS-hook:forumLateralBeforeSearchBox-->
                                              <form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="">
                                                   <button class="button" type="submit">' . $language->get('forum', 'defaultLateralSearchFind', null) . '</button>      
                                              </form><!--FabCMS-hook:forumLateralAfterSearchBox-->');

// Latest topic
$query = 'SELECT T.ID,
       T.topic_trackback,
       T.topic_title,
       TH.ID as thread_ID,
       TH.thread_trackback,
       TH.thread_name
       
 FROM ' . $db->prefix . 'forum_topics AS T
 LEFT JOIN ' . $db->prefix . 'forum_threads AS TH
 ON TH.ID = T.thread_ID
 WHERE T.visible = 1
   AND TH.visible = 1
ORDER BY T.ID DESC
LIMIT 5';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_default_select_topics_query_error',
                   'details'   => 'Unable to select topics. ' . $query,
    ]);

    echo 'Query error.';
    return;
}

if (!$db->affected_rows){
    $latestTopicSidebar = $language->get('forum', 'defaultNoTopic', null);
} else {
    while ($row = mysqli_fetch_assoc($result)){
        $latestTopicSidebar .= '&bull; <a href="' . $URI->getBaseUri() . $this->routed . '/' . $row['thread_ID'] . '-' . $row['thread_trackback'] . '/' . $row['ID'] . '-' . $row['topic_trackback'] . '/">' . $row['topic_title'] . '</a> <br/>';
    }
}

$template->sidebar.= $template->simpleBlock('Latest topics', $latestTopicSidebar);

$template->sidebar .= $template->simpleBlock($language->get('forum', 'sideBarOperations'), '&bull; <a href="' . $URI->getBaseUri() . '/forum/cp/">' . $language->get('forum', 'cpControlPanel') . '</a>');

// Get all categories
$query = '
SELECT * FROM ' . $db->prefix . 'forum_categories AS C
WHERE C.enabled = 1
ORDER BY C.order ASC
';
$db->setQuery($query);
if (!$resultCategory = $db->executeQuery('select')){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_default_select_categories_query_error',
                   'details'   => 'Unable to select the categories. ' . $query,
    ]);

    echo 'Query error while selecting categories. ';
    return;
}

if (!$db->affected_rows){
    echo $language->get('forum', 'defaultNoCategories', null );
    return;
}

while ($rowCategories = mysqli_fetch_assoc($resultCategory)){
    echo '<div class="container">
            <div class="row">
                <div class="col-md-12 fabForum-category">' . $rowCategories['category_name'] . '</div>
            </div>';

    $query = 'SELECT T.thread_name,
                     T.thread_trackback,
                     T.ID as thread_ID,
                     T.topics_count,
                     T.thread_description, 
                     TOPIC.topic_title,
                     TOPIC.ID AS topic_ID,
                     TOPIC.topic_trackback,
                     TOPIC.date_created,
                     U.username
              FROM ' . $db->prefix . 'forum_threads AS T 
              LEFT JOIN ' . $db->prefix . 'forum_topics AS TOPIC
              ON T.latest_topic_ID = TOPIC.ID
              LEFT JOIN ' . $db->prefix . 'users AS U
              ON T.latest_topic_user_ID = U.ID
              WHERE T.category_ID = ' . $rowCategories['ID'] .' AND T.visible = 1
              GROUP BY T.ID
              ';
    $db->setQuery($query);

    if (!$resultTopics = $db->executeQuery('select')){

        $relog->write(['type'      => '4',
                       'module'    => 'FORUM',
                       'operation' => 'forum_default_select_threads_query_error',
                       'details'   => 'Unable to select threads. ' . $query,
        ]);

        echo 'Query error.';
        return;
    }

    if (!$db->affected_rows) {
        echo $language->get('forum', 'defaultNoForun', null );;
    } else {

        echo '<!-- Start new thread --> 
            <div class="row fabForum-threads fabForum-threadsFieldDesc">
              <div class="col-md-5">' . $language->get('forum', 'defaultThreadName', null ) . '</div>
              <div class="col-md-2"> ' . $language->get('forum', 'defaultTopicCount', null ) . '</div>
              <div class="col-md-5">' . $language->get('forum', 'defaultLatestPost', null ) . '</div>
            </div>';

        while ($rowTopic = mysqli_fetch_array($resultTopics)){
            if ($rowTopic['topic_ID'] === 0 || is_null($rowTopic['topic_ID'])){
                $theLink = $URI->getBaseUri() . $this->routed . '/new-topic/?thread_ID=' . $rowTopic['thread_ID'];
                $latestTopic = sprintf($language->get('forum', 'defaultNoTopicYetInserted', null), $theLink) ;
            } else {
                $latestTopic = '<a href="' . $URI->getBaseUri() . $this->routed . '/' . $rowTopic['thread_ID'] . '-' . $rowTopic['thread_trackback'] . '/' . $rowTopic['topic_ID'] . '-' . $rowTopic['topic_trackback'] . '/">' . $rowTopic['topic_title'] . '</a><br>' . $language->get('forum', 'defaultWrittenBy') . ' ' . $rowTopic['username'] . ' on ' . $rowTopic['date_created'] . '';
            }

            echo '
            <div class="row fabForum-threads-list">
                <div class="col-md-5"><strong>
                    <a href="' . $URI->getBaseUri() . $this->routed . '/' . $rowTopic['thread_ID'] . '-' . $rowTopic['thread_trackback'] . '/">' . $rowTopic['thread_name'] . '</a>
                    </strong><br/>' . $rowTopic['thread_description'] .
                '</div>
                <div class="col-md-2">' . $rowTopic['topics_count'] . '</div>
                <div class="col-md-5">
                ' . $latestTopic . '
                </div>
             </div>';
        }
    }
    echo '</div>';
}
