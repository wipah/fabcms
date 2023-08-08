<?php
namespace CrisaSoft\FabCMS;

class forum
{
    public $topicLocked;
    public $threadID;
    public $threadTrackback;
    public $threadName;
    public $userBanned;
    public $banStart;
    public $banEnd;
    public $banReason;
    public $isSubscribed;
    public $config = [];
    public $parsers = [];
    public $highlightCalled;

    function __construct()
    {
        global $core;
        global $db;
        global $user;

        $this->loadConfig();
        $this->checkIfUserIsBanned();
        $this->loadParsers();
    }

    function loadParsers(){
        global $db;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'forum_parsers 
                  WHERE enabled = 1
                  ORDER BY `order` ASC';

        

        if (!$result = $db->query($query)){
            die ("Unable to load parsers");
        }

        while ($row = mysqli_fetch_assoc($result)){
            $this->parsers[] = $row['parser'];
        }

    }

    function execParsers($content){

        foreach ($this->parsers AS $parser){
            $content = call_user_func('forum_parser_' . $parser, $content);
        }

        return $content;
    }

    function loadConfig()
    {
        global $core;
        global $db;
        global $relog;
        $query = ' SELECT * FROM ' . $db->prefix . 'forum_config';

        

        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_config_load_config_query_errro',
                           'details'   => 'Unable to load configuration. ' . $query,
            ]);

            die("Query error while loading config.");
        }
        if (!$db->affected_rows)
            return;

        while ($row = mysqli_fetch_assoc($result)) {

            $this->config [$row['param']] = $row['value'];
        }
    }

    function checkIfUserIsBanned()
    {
        global $db;
        global $core;
        global $user;
        global $relog;

        // Check if user is logged and banned
        if ($user->logged) {

            $query = 'SELECT * 
                      FROM ' . $db->prefix . 'forum_bans 
                      WHERE user_ID = ' . $user->ID . '
                      AND ban_end_date >= NOW()
                      LIMIT 1;';

            
            if (!$result = $db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'forum_check_if_user_is_banned_query_error',
                               'details'   => 'Unable to check if the user is banned. ' . $query,
                ]);

                echo 'Query error in checkIfUserIsBanned()';

                return;
            }

            if (!$db->affected_rows) {
                $this->userBanned = false;

                return;
            }

            $row = mysqli_fetch_assoc($result);

            $this->userBanned = true;
            $this->banStart = $row['ban_start_date'];
            $this->banEnd = $row['ban_end_date'];
            $this->banReason = $row['ban_reason'];
        }

    }

    function updateTopicCount($thread_ID)
    {
        global $db;
        global $core;
        global $relog;

        $thread_ID = (int)$thread_ID;

        $query = 'SELECT COUNT(ID) AS total 
                  FROM ' . $db->prefix . 'forum_topics 
                  WHERE thread_ID = ' . $thread_ID . ' 
                    AND visible = 1';

        
        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_topic_count_query_error',
                           'details'   => 'Query error while count topics. ' . $query,
            ]);

            echo 'Query error while counting topics';

            return;
        }

        if (!$db->affected_rows) {
            $total = 0;
        } else {
            $row = mysqli_fetch_assoc($result);
            $total = $row['total'];
        }

        $query = 'UPDATE ' . $db->prefix . 'forum_threads 
                  SET topics_count = ' . $total . ' 
                  WHERE ID = ' . $thread_ID . ' 
                  LIMIT 1';

        
        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_topic_count_update_query_error',
                           'details'   => 'Query error while updating count. ' . $query,
            ]);

            return false;
        } else {
            return true;
        }
    }

    function isTopicLocked($topic_ID)
    {
        global $db;
        global $relog;

        $topic_ID = (int)$topic_ID;

        if (!isset($topic_ID))
            return true;

        $query = 'SELECT locked 
                  FROM ' . $db->prefix . 'forum_topics 
                  WHERE ID = ' . $topic_ID . ' LIMIT 1';

        
        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_is_topic_locked_query_errror',
                           'details'   => 'Query error while checking if topic is locked. ' . $query,
            ]);


            echo 'Query error while checking for topic lock.';

            return true;
        }

        if (!$db->affected_rows)
            return true;

        $row = mysqli_fetch_assoc($result);

        if ((int)$row['locked'] == 1) {
            return true;
        } else {
            return false;
        }

    }

    function postReply($topic_ID, $message)
    {
        global $user;
        global $db;
        global $relog;

        $topic_ID = (int)$topic_ID;

        if ($this->isTopicVisible($topic_ID) === false) {
            $relog->write(['type'      => '3',
                           'module'    => 'FORUM',
                           'operation' => 'forum_post_reply_on_non_visible_post',
                           'details'   => 'Trying to post a reply on a non visible post. ',
            ]);
            return -2;
        }

        $query = 'INSERT INTO ' . $db->prefix . 'forum_replies 
                  (
                    user_ID, 
                    date, 
                    topic_ID, 
                    reply, 
                    visible, 
                    IP
                  )
                  VALUES
                  (
                      \'' . $user->ID . '\',
                      NOW(),
                      ' . $topic_ID . ',
                      \'' . $message . '\',
                     1,
                      \'' . $_SERVER['REMOTE_ADDR'] . '\'
                  )';

        

        if (!$db->query($query)) {
            $relog->write(['module'    => 'FORUM',
                           'operation' => 'post_reply_query_error',
                           'details'   => 'Unable insert the reply. ' . $query,
                           'type'      => 3,
                ]
            );

            return -1;
        } else {
            $last = $db->insert_id;
            $this->updatePostCount($user->ID);
            $this->updateReplyCount($topic_ID);

            $this->setTopicSubscription($topic_ID, $user->ID, false, true);
            $paginated = $this->getPaginatedURIByPostID($topic_ID, $last);

            $query = 'UPDATE ' . $db->prefix . 'forum_topics 
                      SET latest_reply_user_ID = ' . $user->ID . ',
                      date_latest_update = NOW() 
                      WHERE ID = ' . $topic_ID . ' LIMIT 1;';

            

            if (!$db->query($query)) {

                $relog->write(['module'    => 'FORUM',
                               'operation' => 'post_reply_update_topics_error',
                               'details'   => 'Unable to update topics last reply. ' . $query,
                               'type'      => 3,
                    ]
                );

                return ['status' => -1, $message => 'Query error while updating topic last reply'];
            }

            $relog->write(['module'    => 'FORUM',
                           'operation' => 'post_reply_update_topics_ok',
                           'details'   => 'Reply posted',
                           'type'      => 1,
                ]
            );
        }
        return ['status' => 1, $message => 'OK', 'page' => $paginated];

    }

    function getPaginatedURIByPostID (int $topic_ID, int $post_ID) : string
    {
        global $db;
        global $relog;
        global $URI;

        $query = '
        SELECT TOPIC.ID AS topic_ID     ,
               TOPIC.topic_trackback    ,
               THREAD.ID AS thread_ID   ,
               THREAD.thread_trackback  ,
        (SELECT COUNT(ID) AS position
        FROM ' . $db->prefix . 'forum_replies AS RCOUNT
        WHERE RCOUNT.ID <= ' . $post_ID . '
        AND RCOUNT.visible = 1
        AND RCOUNT.topic_ID = ' . $topic_ID . ') AS position
        FROM ' . $db->prefix . 'forum_topics AS TOPIC
        LEFT JOIN '. $db->prefix . 'forum_threads AS THREAD
            ON THREAD.ID = TOPIC.thread_ID
        WHERE TOPIC.ID = ' . $topic_ID .'; 
        ';

        

        if (!$result = $db->query($query)){
            $relog->write(['module'    => 'FORUM',
                           'operation' => 'post_get_paginated_URI_error',
                           'details'   => 'Query error. ' . $query,
                           'type'      => 4,
                ]
            );

            return -1;

        }

        if (!$db->affected_rows){
            return -2;
        }

        $row = mysqli_fetch_assoc($result);

        if ((int) $row['position'] <= PAGINATION){
            $pagination = '/';
        } else {
            $pagination = '/' . ceil($row['position'] / PAGINATION) . '/';
        }

        $paginated = $URI->getBaseUri() . 'forum/' . $row['thread_ID'] . '-' . $row['thread_trackback'] .
                                         '/' . $topic_ID . '-' . $row['topic_trackback'] . $pagination . '#reply-' . $post_ID;


        return $paginated;
    }

    function isTopicVisible($topic_ID)
    {
        global $db;
        global $core;
        global $relog;

        $topic_ID = (int)$topic_ID;

        $query = 'SELECT ID FROM ' . $db->prefix . 'forum_topics WHERE ID = ' . $topic_ID . ' AND visible = 1 LIMIT 1';

        
        $result = $db->query($query);

        if (!$db->affected_rows) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_is_topic_visible_query_errror',
                           'details'   => 'Query error while checking if topic is visible. ' . $query,
            ]);
            return false;
        } else {
            return true;
        }

    }

    function updatePostCount($user_ID)
    {
        global $db;
        global $relog;

        $user_ID = (int)$user_ID;

        $query = '
        SELECT (SELECT COUNT(R.ID) 
                FROM ' . $db->prefix . 'forum_replies AS R
                LEFT JOIN ' . $db->prefix . 'forum_topics AS T
                ON R.topic_ID = T.ID
                WHERE R.user_ID = ' . $user_ID . '
                AND T.visible = 1 
                AND R.visible = 1 ) AS replies_count,
               (SELECT COUNT(ID) FROM ' . $db->prefix . 'forum_topics WHERE user_ID = ' . $user_ID . '  AND visible = 1 ) AS topics_count';

        
        if (!$result = $db->query($query)) {
            echo('Query error!!! ' . $query);

            return;
        }

        $row = mysqli_fetch_assoc($result);

        $topics     = $row['topics_count'];
        $replies    = $row['replies_count'];

        $query = 'SELECT ID FROM ' . $db->prefix . 'forum_user_stats WHERE user_ID = ' . $user_ID . ' LIMIT 1';
        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_post_count_query_errror',
                           'details'   => 'Unable to get post count. ' . $query,
            ]);
            echo 'Query error in updatePostCount()';

            return;
        }

        if ($db->affected_rows) {
            $row = mysqli_fetch_assoc($result);

            $row_ID = $row['ID'];

            $query = 'UPDATE ' . $db->prefix . 'forum_user_stats 
                      SET reply_count = ' . $replies . ',  
                        topic_count = ' . $topics . '  
                      WHERE ID = ' . $row_ID . ' 
                        AND user_ID = ' . $user_ID . ' 
                      LIMIT 1';
            
            if (!$db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'forum_update_post_count_update_stats_query_errror',
                               'details'   => 'Unable to update the stats. ' . $query,
                ]);
                echo 'Query error in updatePostCount(). ';
            }
        } else {
            $query = 'INSERT INTO ' . $db->prefix . 'forum_user_stats 
                      (
                        user_ID, 
                        reply_count, 
                        topic_count
                      )
                      VALUES
                      (
                        ' . $user_ID . ',
                        ' . $replies . ',
                        ' . $topics . '
                      );';
            
            if (!$db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'forum_update_post_count_insert_stats_query_errror',
                               'details'   => 'Unable to insert the stats. ' . $query,
                ]);

                echo 'Query error in updatePostCount(). ';
            }
        }
    }

    function getReplyTopicCountByUser ($user_ID){
        global $db;
        global $relog;
        $user_ID = (int) $user_ID;

        $query = 'SELECT (reply_count + topic_count) AS total_count
                  FROM ' . $db->prefix . 'forum_user_stats
                  WHERE user_ID = ' . $user_ID . '
                  LIMIT 1';

        
        if (!$result = $db->query($query)){
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_get_reply_count_query_errror',
                           'details'   => 'Unable to get the reply count. ' . $query,
            ]);

            return -1;
        }

        if (!$db->affected_rows)
            return 0;

        $row = mysqli_fetch_assoc($result);

        return (int) $row['total_count'];

    }
    function refreshTopic($topic_ID)
    {
        global $db;
        global $relog;

        $this->updateReplyCount($topic_ID);

        $query = 'SELECT R.ID as reply_ID,
                         R.user_ID,
                         R.date
                  FROM ' . $db->prefix . 'forum_replies AS R
                  WHERE topic_ID = ' . $topic_ID . '
                    AND visible = 1
                  ORDER BY ID DESC
                  LIMIT 1';

        

        if (!$result = $db->query($query)) {

            $relog->write(['module'    => 'FORUM',
                           'operation' => 'refresh_topic_get_reply_error',
                           'details'   => 'Query error while getting latest reply. ' . $query,
                           'type'      => 3,
                ]
            );

            return -1;
        }

        if (!$db->affected_rows) {
            $relog->write(['module'    => 'FORUM',
                           'operation' => 'post_reply_update_topics_no_topic',
                           'details'   => 'No topic',
                           'type'      => 2,
                ]
            );

            return;
        }

        $row = mysqli_fetch_assoc($result);

        $query = 'UPDATE ' . $db->prefix . 'forum_topics 
                 SET 
                    latest_reply_user_ID    =   ' . $row['user_ID'] . ',
                    date_latest_update      =  \'' . $row['date'] . '\'
                 LIMIT 1;
                 ';

        
        if (!$db->query($query)) {

            $relog->write(['module'    => 'FORUM',
                           'operation' => 'post_reply_update_topics_error',
                           'details'   => 'Unable to update the topic. ' . $query,
                           'type'      => 3,
                ]
            );

            return -2;
        }

        return true;
    }

    function parseContent($content)
    {
        global $relog;

        $regex = '#\[file ([0-9]{1,6})\]#im';

        $content = preg_replace_callback($regex,
            function ($matches) {
                global $db;
                global $URI;
                global $user;
                global $relog;
                $file_ID = $matches[1];

                $query = 'SELECT * 
                          FROM ' . $db->prefix . 'fabmedia_fabmedia 
                          WHERE ID = ' . $file_ID . ' 
                            AND enabled = 1
                          LIMIT 1';

                

                if (!$result = $db->query($query)) {
                    $relog->write(['type'      => '4',
                                   'module'    => 'FORUM',
                                   'operation' => 'forum_parse_content_fabmedia_query_errror',
                                   'details'   => 'Unable to parse the content on fabmedia. ' . $query,
                    ]);
                    return 'Query error!';
                } else {
                    if (!$db->affected_rows) {
                        return 'File not exists';
                    } else {
                        if ($user->logged) {
                            $row = mysqli_fetch_assoc($result);

                            return '<a href="' . $URI->getBaseUri() . '/fabmediamanager/download/' . $file_ID . '/">' . $row['filename'] . '.' . $row['extension'] . '</a>';
                        } else {
                            return 'You must be logged to download this file. Please <a href="' . $URI->getBaseUri() . 'user/">register or login</a>.';
                        }

                    }
                }
            }

            , $content);

        return $content;
    }

    function updateReplyCount($topic_ID)
    {
        global $db;
        global $relog;

        $topic_ID = (int)$topic_ID;
        $query = 'SELECT COUNT(ID) AS replies 
                  FROM ' . $db->prefix . 'forum_replies 
                  WHERE topic_ID = ' . $topic_ID . '
                  AND visible = 1';
        

        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_reply_count_query_errror',
                           'details'   => 'Unable to count replies. ' . $query,
            ]);

            echo 'Query error while updating topic stats.';
        }

        if ($db->affected_rows) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['replies'];

            $query = '
            UPDATE ' . $db->prefix . 'forum_topics
            SET replies = ' . $count . '
            WHERE ID = ' . $topic_ID . '
            LIMIT 1;
            ';

            
            if (!$db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'forum_update_reply_count_update_query_errror',
                               'details'   => 'Unable to update the reply count. ' . $query,
                ]);
                echo 'Error. ' . $query;
            }
        }

        return;
    }

    function setTopicSubscription($topic_ID, $user_ID, $mustBeAlreadySubscribed = true, $removeNotifyStatus = false)
    {
        global $core;
        global $db;
        global $relog;

        $query = 'UPDATE ' . $db->prefix . 'forum_subscriptions 
                  SET latest_check_date = NOW() 
                  ' . ($removeNotifyStatus === true ? ', notify_sent = 0 ' : '') . '
                  WHERE user_ID = ' . $user_ID . '
                  AND topic_ID = ' . $topic_ID . '
                  LIMIT 1';
        


        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_subscription_query_errror',
                           'details'   => 'Unable to update the subscription. ' . $query,
            ]);

            echo 'Query error while updating subscription.';

            return;
        }

        if (!$db->affected_rows && $mustBeAlreadySubscribed === false) {
            $query = 'INSERT INTO ' . $db->prefix . 'forum_subscriptions 
            (
                user_ID,
                topic_ID,
                latest_check_date,
                notify_sent,
                status
            )
            VALUES
            (
                ' . $user_ID . ',
                ' . $topic_ID . ',
                NOW(),
                0,
                1
            )
            ';

            
            if (!$result = $db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'forum_update_subscription_insert_query_errror',
                               'details'   => 'Unable to create a new subscription. ' . $query,
                ]);

                echo 'Query error while inserting subscription.';
            }
        }
    }

    function deleteTopic($topic_ID)
    {
        global $db;
        global $core;
        global $relog;

        $topic_ID = (int)$topic_ID;

        $query = 'SELECT user_ID, thread_ID 
                  FROM ' . $db->prefix . 'forum_topics 
                  WHERE ID = ' . $topic_ID . ' LIMIT 1;';

        

        if (!$resultTopic = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'delete_topic_select',
                           'details'   => 'SELECT topic. ' . $query]);

            return -1;
        }


        if (!$db->affected_rows) {
            $relog->write(['type'      => '3',
                           'module'    => 'FORUM',
                           'operation' => 'delete_topic_no_topic',
                           'details'   => 'SELECT topic, no topic found. ' . $query]);

            return -2;
        }


        $rowTopic = mysqli_fetch_assoc($resultTopic);
        $usersReplies = $rowTopic['user_ID'] . ', ';

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'forum_replies 
                  WHERE topic_ID = ' . $topic_ID . ';';

        

        if (!$resultReplies = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'delete_topic_select_replies',
                           'details'   => 'SELECT replies. ' . $query]);

            return -3;
        }


        // Delete replies
        if ($db->affected_rows) {

            while ($rowReplies = mysqli_fetch_assoc($resultReplies)) {
                $usersReplies .= $rowReplies['user_ID'] . ', ';
            }

            $usersReplies = substr($usersReplies, 0, -2);

            $query = 'DELETE FROM ' . $db->prefix . 'forum_replies 
                      WHERE ID IN (' . $usersReplies . ')';
            

            if (!$db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'FORUM',
                               'operation' => 'delete_topic_delete_replies',
                               'details'   => 'Delete replies topic. ' . $query]);

                return -4;
            }

            $usersRepliesArray = explode(', ', $usersReplies);

            $usersRepliesCount = count($usersRepliesArray);
            for ($i = 0; $i < $usersRepliesCount; $i++) {
                $this->updatePostCount($usersRepliesArray[$i]);
            }
        }

        // Delete main topic
        $query = 'DELETE FROM ' . $db->prefix . 'forum_topics 
                  WHERE ID = ' . $topic_ID . ' LIMIT 1';

        
        if (!$db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'delete_topic_delete_main_topic',
                           'details'   => 'Delete main topic. ' . $query]);

            return -5;
        }

        $relog->write(['type'      => '1',
                       'module'    => 'FORUM',
                       'operation' => 'delete_topic',
                       'details'   => 'Topic . ' . $topic_ID . ' deleted.']);

        $this->updateLastTopic($rowTopic['thread_ID']);
        $this->updateTopicCount($rowTopic['thread_ID']);

        return true;

    }

    function updateLastTopic($thread_ID)
    {
        global $db;
        global $core;
        global $relog;

        $thread_ID = (int)$thread_ID;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'forum_topics 
                  WHERE thread_ID = ' . $thread_ID . ' 
                  AND visible = 1 
                  ORDER BY ID 
                  DESC LIMIT 1';

        
        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_latest_topic_find_latest_query_errror',
                           'details'   => 'Unable to select latest topic. ' . $query,
            ]);

            echo 'Query error in updateLastTopic().';

            return;
        }

        if (!$db->affected_rows) {

            $query = 'UPDATE ' . $db->prefix . 'forum_threads 
                      SET latest_topic_ID = NULL,
                          latest_topic_user_ID = NULL
                      WHERE ID = ' . $thread_ID . '
                      LIMIT 1
                      ';
        } else {
            $row = mysqli_fetch_assoc($result);

            $query = 'UPDATE ' . $db->prefix . 'forum_threads 
                      SET latest_topic_ID = ' . $row['ID'] . ',
                          latest_topic_user_ID = ' . $row['user_ID'] . '
                      WHERE ID = ' . $thread_ID . '
                      LIMIT 1
                      ';
        }

        
        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_update_latest_topic_query_errror',
                           'details'   => 'Unable to select latest topic. ' . $query,
            ]);

            echo 'Query error in updateLastTopic().';
        }

    }

    function renderAuthorBox($data)
    {
        global $core;
        global $URI;

        if (isset($data['avatar'])) {
            switch ((int)$data['avatar_type']) {
                case 1:
                    $profileImage = $URI->getBaseUri(true) . 'modules/forum/assets/custom_avatars/' . $core->in($data['avatar'], true);
                    break;
                case 0:
                default:
                    $profileImage = $URI->getBaseUri(true) . 'modules/forum/res/generic_profile.png';
            }
        } else {
            $profileImage = $URI->getBaseUri(true) . 'modules/forum/res/generic_profile.png';
        }

        $return = '
        
        <div style="">
            <img class="img-fluid" style="max-width:128px;" src="' . $profileImage . '" alt="user profile photo"><br/>
            <span style="font-weight: bolder">' . $data['username'] . '</span> ' . ((int)$data['banStatus'] === 1 ? '[BANNED]' : '') . '- 
            <span style="font-style: italic;">' . ((int)$data['replyCount'] + (int)$data['topicCount']) . ' post</span><br/>
        </div>';

        return $return;
    }

    function cleanHtmlCode($code)
    {
        global $user;
        global $core;

        /* Strips any style inside tags */
        if (!$user->isAdmin) {
            $regex = '/(<[^>]+) style([\s\ ]{1,})?=([\s\ ]{1,})?".*?"/im';
            $code = preg_replace($regex, '$1', $code);
        }

        $code = $core->in($code);

        $code = str_ireplace('<script', '&lt;script', $code);
        $code = str_ireplace('<link', '&lt;link', $code);
        $code = str_ireplace('<iframe', '&lt;iframe', $code);
        $code = str_ireplace('<frame', '&lt;frame', $code);
        $code = str_ireplace('<object', '&lt;object', $code);
        $code = str_ireplace('<embed', '&lt;embed', $code);
        $code = str_ireplace('<applet', '&lt;applet', $code);
        $code = str_ireplace('<style', '&lt;style', $code);

        return $code;
    }

    function isReplyUnderTopicLocked($reply_ID)
    {
        global $core;
        global $db;
        global $relog;

        $reply_ID = (int)$reply_ID;

        $query = '
        SELECT T.locked
        FROM ' . $db->prefix . 'forum_topics AS T
        LEFT JOIN ' . $db->prefix . 'forum_replies AS R
        ON R.topic_ID = T.ID
        WHERE R.ID = ' . $reply_ID . ' LIMIT 1;';

        
        if (!$result = $db->query($query)) {


            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_is_reply_under_topic_locked_query_errror',
                           'details'   => 'Unable to select the topic. ' . $query,
            ]);

            die("Query error while checking lock status");
        }

        if (!$db->affected_rows) {
            return true;
        } else {
            $row = mysqli_fetch_assoc($result);
            if (is_null($row['locked']) || (int)$row['locked'] === 0) {
                return false;
            } else {
                return true;
            }
        }
    }
}