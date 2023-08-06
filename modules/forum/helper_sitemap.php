<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/01/2017
 * Time: 11:17
 */

if (!$core->loaded)
    die ("Direct call");

$query = '
SELECT T.ID AS topic_ID, 
       T.topic_trackback, 
       TH.ID AS thread_ID, 
       TH.thread_trackback
FROM ' . $db->prefix . 'forum_topics AS T
LEFT JOIN ' . $db->prefix . 'forum_threads AS TH
    ON TH.ID = T.thread_ID
WHERE T.visible = 1
LIMIT 1';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    $log->write('error','Sitemap:forum','Query error: ' . $query);
    return;
}

if (!$db->affected_rows){
    $log->write('info','Sitemap:forum','No result: ' . $query);
    return;
}

while ($row = mysqli_fetch_assoc($result)){
    $return .= '<url>
    <loc>' . $URI->getBaseUri() . $core->router->getRewriteAlias('forum') . '/' . $row['thread_ID'] . '-' . $row['thread_trackback'] . '/' . $row['topic_ID'] . '-' . $row['topic_trackback'] . '/</loc>';

    $return .= '
</url>';
}

$this->result .= $return;