<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/11/2017
 * Time: 15:49
 */

if (!$core->loaded)
    die("Direct call");

if (!is_writable(__DIR__)) {
    echo 'Directory ' . __DIR__ . ' is not writable. Aborting.';

    return;
}

if (file_exists('cronjob.lock')) {
    echo 'Cronjob is locked';
    return;
}
echo '&bull; Checking for new replies. <br/>';

$query = 'SELECT 
            S.ID as subscription_ID,
            T.topic_title,
            T.topic_trackback,
            T.ID AS topic_ID 
          FROM ' . $db->prefix . 'forum_subscriptions AS S
          LEFT JOIN ' . $db->prefix . 'forum_topics AS T
          ON S.topic_ID = T.ID
          WHERE status = 1';


$db->setQuery($query);

if (!$result = $db->executeQuery($query)) {
    $cronjobs->status = 2;
    $cronjobs->writeLog('Query error. ' . $query);
    return;
}

if (!$db->affected_rows) {
    $cronjobs->writeLog('Now row. ' . $query);
    return;
}

while ($row = mysqli_fetch_array($result)) {

    // Update the subscription
    $query = 'UPDATE ' . $db->prefix . 'forum_subscription 
              SET status = 1
              WHERE ID = ' . $row['subscription_ID'] . '
              LIMIT 1;';

    $db->setQuery($query);
    if (!$db->executeQuery('update')) {
        $cronjobs->status = 2;
        $cronjobs->writeLog('Unable to change the status of the subscription. ' . $query);
    } else {
        if (!$db->affected_rows) {
            $cronjobs->writeLog("No affected rows while changing status. $query");

        } else {
            $cronjobs->writeLog("Subscription updated. $query");
        }
    }
}