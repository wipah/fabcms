<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/09/2015
 * Time: 20:56
 */

if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

$this->noTemplateParse = TRUE;
$filterKeyword = $core->in($_POST['fabMediaFilterKeyword']);

var_dump($_POST['fabmediamanager_type']);
if (empty($_POST['ID'])){
    echo 'No ID passed.';
    return;
}
$ID = (int) $_POST['ID'];

if (empty($_POST['fabmediamanager_master_ID'])){
    echo 'No master ID passed.';
    return;
}
$master_ID = (int) $_POST['fabmediamanager_master_ID'];

$lang = $core->in($_POST['fabmediamanager_language'], true);
// Check if the language exists
$query = 'SELECT 
            M.ID 
          FROM ' . $db->prefix . 'fabmedia AS M
          LEFT JOIN ' . $db->prefix. 'fabmedia_masters AS MM
            ON MM.ID = M.master_ID
          WHERE MM.ID = ' . $master_ID . '
            
            AND (M.lang = \'' . $lang . '\' OR M.lang IS NULL OR M.lang= \'\')
          LIMIT 1;';
$db->setQuery($query);

if (!$db->executeQuery('select')) {
    die ($query);
}

if ($db->numRows)
{
    $query = '
        UPDATE ' . $db->prefix . 'fabmedia
        SET
            lang        = \'' . $lang . '\',
            title       = \'' . $core->in($_POST['fabmediamanager_title'], true) . '\',
            trackback   = \'' . $core->getTrackback($core->in($_POST['fabmediamanager_title'])) . '\',
            type        = \'' . $core->in($_POST['fabmediamanager_type'], true) . '\',
            subtype     = \'' . $core->in($_POST['fabmediamanager_subtype'], true) . '\',
            description = \'' . $core->in($_POST['fabmediamanager_description'], false) . '\',
            tags        = \'' . $core->in($_POST['fabmediamanager_tags'], false) . '\',
            license_ID  = \'' . (int) $_POST['fabmediamanager_copyright'] . '\',
            author      = \'' . $core->in($_POST['fabmediamanager_author'], true) . '\',
            enabled     = \'' . ($_POST['fabmediamanager_enabled'] == '1' ? '1' : '0') . '\',
            global_available     = \'' . ($_POST['fabmediamanager_globalAvailable'] == '1' ? '1' : '0') . '\',
            indexable   = \'' . ($_POST['fabmediamanager_indexable'] == '1' ? '1' : '0') . '\',
            modify_date = \'' . date('Y-m-d') . '\',
            modified    = \'1\'
        WHERE ID = \'' . $ID . '\'';

    $db->setQuery($query);

    if (!$db->executeQuery('update')){
        echo 'Error: ' . $query;
    } else {
        echo '[' . date('d-m-Y h:i:s') . '] File updated (lang: ' . $lang . ').';
    }
} else {
     $query = '
        INSERT INTO ' . $db->prefix . 'fabmedia 
        (
            master_ID,
            lang,
            title,
            trackback,
            type,
            subtype,
            tags,
            description,
            license_ID,
            author,
            enabled,
            global_available,
            indexable,
            modify_date,
            modified
        ) VALUES (
            ' . $master_ID . ',
            \'' . $lang. '\',
            \'' .  $core->in($_POST['fabmediamanager_title'], true) . '\',
            \'' .  $core->getTrackback($core->in($_POST['fabmediamanager_title']))  . '\',
            \'' .  ($core->in($_POST['fabmediamanager_type']))  . '\',
            \'' .  ($core->in($_POST['fabmediamanager_subtype']))  . '\',
            \'' .  $core->in($_POST['fabmediamanager_tags'], true)  . '\',
            \'' .  $core->in($_POST['fabmediamanager_description'], true)  . '\',
            ' .  (int) $_POST['fabmediamanager_copyright']  . ',
            \'' .  $core->in($_POST['fabmediamanager_author'], true) . '\',
            ' .  ($_POST['fabmediamanager_enabled'] == '1' ? '1' : '0') . ',
            ' .  ($_POST['fabmediamanager_globalAvailable'] == '1' ? '1' : '0') . ',
            ' .  ($_POST['fabmediamanager_indexable'] == '1' ? '1' : '0') . ',
            \'' .  date('Y-m-d') . '\',
           1
            
        )   
     ';

     $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Error: ' . $query;
    } else {
        if (!$db->numRows){
            echo 'No update. ' . $query;
        }
        echo '[' . date('d-m-Y h:i:s') . '] File created (lang: ' . $lang . ').';
    }
}

// Upload video info
if ($_POST['fabmediamanager_type'] === 'video') {
    $query = '
    UPDATE ' . $db->prefix . 'fabmedia_videos
    SET 
        length = \'' . $core->in($_POST['fabmediamanager_length']) . '\'
    WHERE ID = ' . ( (int) $_POST['fabmediamanager_video_ID']) . '
    LIMIT 1';

    $db->setQuery($query);

    if (!$db->executeQuery('update')){
        echo 'Query error. ' . $query;
    } else {
        echo 'Video updated';
    }

}