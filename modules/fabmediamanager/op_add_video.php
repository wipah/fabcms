<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/10/2017
 * Time: 12:38
 */

$this->noTemplateParse = true;

$youtubeID = $core->in($_POST['youtubeID'], true);
$youtubeTitle = $core->in($_POST['youtubeTitle'], true);

$query = 'INSERT INTO ' . $db->prefix . 'fabmedia_masters 
          (
            ID
          ) 
          VALUES 
          (
            NULL
          )';
$db->setQuery($query);
if (!$db->executeQuery('inserto')){
    echo 'Query error. ' . $query;
    return;
}

$insert_ID = $db->lastInsertID;

$query = 'INSERT INTO ' . $db->prefix . 'fabmedia 
          (
            master_ID,
            type,
            subtype,
            enabled, 
            indexable, 
            title
          )
          VALUES
          (
            ' . $insert_ID . ',
            \'video\',
            \'youtube\',
            1,
            1,
            \'' . $youtubeTitle . '\'
          );';

$db->setQuery($query);
if (!$db->executeQuery('insert')) {
    echo 'Query error. ' . $query;

    return;
}

$fabmedia_ID = $db->lastInsertID;

$query = 'INSERT INTO ' . $db->prefix . 'fabmedia_videos 
         (  fabmedia_ID,
            provider, 
            provider_ID
         ) 
            VALUES 
         (
            ' . $fabmedia_ID . ',
            \'youtube\',
            \'' . $youtubeID . '\'
         )
         ';

$db->setQuery($query);
if (!$db->executeQuery('insert')) {
    echo 'Query error. ' . $query;

    return;
}