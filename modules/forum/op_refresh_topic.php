<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/01/2018
 * Time: 10:25
 */
if (!$core->loaded)
    die ("Direct call");

if (!isset($_GET['topic_ID'])) {
    echo 'Topic ID is missing';

    return;
}

$topic_ID = (int)$_GET['topic_ID'];

if (true === $fabForum->refreshTopic($topic_ID)) {
    echo 'Refreshed';
} else {
    echo 'Error.';
}