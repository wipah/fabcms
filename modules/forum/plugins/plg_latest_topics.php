<?php

function plugin_latest_topics($dataArray)
{
    global $db;
    global $core;
    global $user;
    global $URI;
    global $conf;
    global $module;
    global $language;
    global $relog;

    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if ( isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    if ( isset($dataArray['limit']) ){
        $limit = 5;
    } else {
        $limit = (int) $dataArray['limit'];
    }

    $where = '';

    if (isset($dataArray['threadID']))
        $where .= 'AND T.thread_ID = ' . (int) $dataArray['threadID'];

    if (isset($dataArray['lang']))
        $where .= 'AND T.language = ' . $core->in($dataArray['lang']);



    switch ($dataArray['order']){
        case 'ID-DESC':
            $order = 'ORDER BY T.ID DESC';
            break;
        case 'ID-ASC':
            $order = 'ORDER BY T.ID ASC';
            break;
        default:
            $order = 'ORDER BY T.ID DESC';
    }

    $query = 'SELECT T.ID AS topic_ID,
                T.topic_title,
                T.topic_trackback,
                TH.ID AS thread_ID,
                TH.thread_trackback
              FROM ' . $db->prefix .'forum_topics AS T
              LEFT JOIN ' . $db->prefix . 'forum_threads AS TH
                ON T.thread_ID = TH.ID
              WHERE T.visible = 1 AND T.locked != 1 ' . $where . '
              ' . $order . '
              LIMIT ' . $limit;

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')) {

        $relog->write(['type'      => '4',
                       'module'    => 'FORUM',
                       'operation' => 'forum_plugin_latest_topics_query_errror',
                       'details'   => 'Unable to select latest topics. ' . $query,
        ]);

        return 'Query error.';
    }

    if (!$db->affected_rows)
        return $language->get('forum', 'pluginLatestTopicNoTopic', null);

    $return = '';

    switch ($dataArray['style']){
        case 'styledDiv':
            while ( $row = mysqli_fetch_assoc($result) ){
                $return .= '<div class="fabForumLatestTopic">
                                <a href="' . $URI->getBaseUri() . '/forum/' . $row['thread_ID'] . '-' . $row['thread_trackback'] . '/' . $row['topic_ID'] . '-' . $row['topic_trackback'] . '/">' . $row['topic_title'] . '</a>
                            </div>';
            }

            break;
        default:
            while ( $row = mysqli_fetch_assoc($result) ){
                $return .= '&bull;<a href="' . $URI->getBaseUri() . '/forum/' . $row['thread_ID'] . '-' . $row['thread_trackback'] . '/' . $row['topic_ID'] . '-' . $row['topic_trackback'] . '/">' . $row['topic_title'] . '</a><br/>';
            }

            break;
    }

    return $return;
}