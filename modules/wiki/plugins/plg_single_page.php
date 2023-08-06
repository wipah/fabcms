<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/09/2017
 * Time: 21:57
 */

function plugin_single_page($dataArray){
    global $db;
    global $core;
    global $user;
    global $URI;
    global $conf;
    global $module;
    global $fabwiki;
    global $relog;

    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    if (!isset($dataArray['ID']) || empty($dataArray['ID'])){
        return 'ID NOT PASSED';
    }
    $ID = (int) $dataArray['ID'];

    $where = '';

    if (!empty($fabwiki->publishedID)) {
        $excludedID = implode(', ', $fabwiki->publishedID);
        $where .= ' AND P.ID NOT IN (' . $excludedID . ') ';
    }

    $query = 'SELECT P.title, 
                     P.ID, 
                     P.trackback, 
                     P.image, 
                     P.metadata_description, 
                     PT.tag
              FROM ' . $db->prefix . 'wiki_pages AS P
              LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS PT
              ON PT.ID = 
              (
                SELECT MIN(ID)
                FROM ' . $db->prefix . 'wiki_pages_tags
                WHERE page_ID = P.ID
                LIMIT 1
              )
              WHERE P.ID = ' .  $ID .' 
                AND P.visible = 1 '
        . $where . '
              LIMIT 1';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')){

        $relog->write(['type'      => '4',
                       'module'    => 'WIKI',
                       'operation' => 'wiki_single_page_select',
                       'details'   => 'Cannot select a single page. Query error. ' . $query,
        ]);

        return 'Query error.';
    }

    if (!$db->affected_rows){
        return'No row!';
    }

    $row = mysqli_fetch_assoc($result);

    return '

<article class="fabCmsArticle">
    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
        <h3>' . $row['title'] . '</h3>
    </a>

    <div class="FabCms-Wiki-item">
        <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
            <span class="FabCms-Wiki-notify-badge">' . $row['tag'] . '</span>
            <img src="' . $URI->getBaseUri() . $row['image'] . '" alt="" style="max-height: 300px;" class="img-fluid">
        </a>
    </div>

    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
        <span class="FabCms-Wiki-articleSmallText">' . $row['metadata_description'] . '</span>
    </a>
</article>';
}
