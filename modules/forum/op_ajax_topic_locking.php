d<?php


if (!$core->loaded){
    die("Direct entry");
}

$this->noTemplateParse = true;

if (!$user->isAdmin){
    die("Only admins");
}

if (!isset($_POST['ID'])){
    echo 'No ID passed';
    return;
}
$topic_ID = (int) $_POST['ID'];

switch ($path[2]){
    case 'lock-topic':
        $status = 1;
        break;
    case 'unlock-topic':
        $status = 0;
        break;
}

$query = 'UPDATE ' . $db->prefix . 'forum_topics SET locked = ' . $status . ' WHERE ID = ' . $topic_ID . ' LIMIT 1';

if (!$db->query($query)){
    echo 'Error.' . $query;
} else {
    echo 'Update ok.' . $query;
}