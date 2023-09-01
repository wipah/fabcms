<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 05/12/2016
 * Time: 16:16
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

require_once($conf['path']['baseDir'] . '/modules/wiki/lib/class_wiki.php');
$fabwiki = new wiki();

if (!isset($_POST['crudType'])) {
    echo '{ "status": 501, "description": "No crudtype passed"}';

    return;
}

if (!isset($_POST['title'])) {
    echo '{"status": 503,"description": "No title passed"}';

    return;
}

$title              =   $core->in($_POST['title'], true);
$title_alternative  =   $core->in($_POST['title_alternative'], true);
$trackback          =   $core->getTrackback($core->in($_POST['title']));
$category           =   (int) $_POST['category'];

$license            =   (int) $_POST['license'];
$status             =   (int) $_POST['status'];

$type_ID            =   (int) $_POST['type_ID'];

$language = $core->in($_POST['language'], true);

$internalRedirect = $core->in($_POST['internalRedirect']);
$keywords = $core->in($_POST['keywords'], true);
$startDate = $core->in($_POST['startDate'], true);
$endDate = $core->in($_POST['endDate'], true);

$shortDescription = $core->in($_POST['shortDescription']);
$metaDataDescription = $core->in($_POST['metaDataDescription'], true);
$content = $core->in($_POST['content']);
$notes = $core->in($_POST['notes']);
$image = $core->in($_POST['image']);
$image_ID = (int) ($_POST['image_ID']);
$featuredVideo_ID =  (int) $_POST['featuredVideo_ID'];
$additionalData = $core->in($_POST['additionalData']);
$seoKeywords = $core->in($_POST['seoKeywords']);

$_POST['visible']           === 'true' ? $visible = 1 : $visible = 0;
$_POST['no_editor']         === 'true' ? $noEditor = 1 : $noEditor = 0;
$_POST['no_index']          === 'true' ? $no_index = 1 : $no_index = 0;
$_POST['no_search']         === 'true' ? $no_search = 1 : $no_search = 0;
$_POST['service_page']      === 'true' ? $service_page = 1 : $service_page = 0;
$_POST['full_page']         === 'true' ? $full_page = 1 : $full_page = 0;
$_POST['no_banner']         === 'true' ? $no_banner = 1 : $no_banner = 0;
$_POST['no_comment']        === 'true' ? $no_comment = 1 : $no_comment = 0;
$_POST['no_toc']            === 'true' ? $no_toc = 1 : $no_toc = 0;
$_POST['no_similar_pages']  === 'true' ? $no_similar_pages = 1 : $no_similar_pages = 0;
$_POST['no_info']           === 'true' ? $no_info = 1 : $no_info = 0;
$_POST['no_linking_pages']  === 'true' ? $no_linking_pages = 1 : $no_linking_pages = 0;
$_POST['no_title']          === 'true' ? $no_title = 1 : $no_title = 0;

if (($_POST['crudType']) === 'update') {
    if (!isset($_POST['ID'])) {
        echo '{"status": 502, "description": "ID not passed"}';

        return;
    }

    $ID = (int) $_POST['ID'];

    // Get the old page before updating
    $query = 'SELECT ID, 
                     revision, 
                     content, 
                     latest_update_user_ID, 
                     creation_user_ID 
              FROM ' . $db->prefix . 'wiki_pages 
              WHERE ID = ' . $ID . ' LIMIT 1';

    if (!$result = $db->query($query)) {
        echo '{"status": 504, "description": "Query error while selecting past content. ' . $db->lastError . '"}';

        return;
    }

    if (!$db->affected_rows) {
        echo '{"status": 504, "description": "Past content was not found! ' . $db->lastError . '"}';

        return;
    }

    $row = mysqli_fetch_assoc($result);

    $revision   = $row['revision'];
    $oldContent = $row['content'];
    $page_ID    = $row['ID'];

    if ((int) $row['latest_update_user_ID'] === 0) {
        $user_ID = $row['latest_update_user_ID'];
    } else {
        $user_ID = $row['creation_user_ID'];
    }


    $query = 'UPDATE ' . $db->prefix . 'wiki_pages SET
    title                   =   \'' . $title . '\',
    category_ID             =   \'' . $category . '\',
    title_alternative       =   \'' . $title_alternative . '\',
    status_ID               =   \'' . $status . '\',
    license_ID               =   \'' . $license . '\',
    type_ID                 =   \'' . $type_ID . '\',
    trackback               =   \'' . $trackback . '\',
    language                =   \'' . $language . '\',
    internal_redirect       =   \'' . $internalRedirect . '\',
    visible                 =   ' . $visible . ',
    visible_from_date       =   ' . ($startDate === '' ? 'null' : '\'' . $startDate . '\'') . ',
    visible_to_date         =   ' . ($endDate === '' ? 'null' : '\'' . $endDate . '\'' ) . ',
    ' . ( $_POST['minor_update'] === 'true' ? '' : 'last_update             =   \'' . date('Y-m-d H:i:s') . '\',') .'  
    short_description       =   \'' . $shortDescription . '\',
    metadata_description    =   \'' . $metaDataDescription . '\',
    content                 =   \'' . $content . '\',
    notes                   =   \'' . $notes . '\',
    additional_data         =   \'' . $additionalData . '\',
    no_editor               =   \'' . $noEditor . '\',
    no_index                =   \'' . $no_index . '\',
    no_search               =   \'' . $no_search . '\',
    no_info                 =   \'' . $no_info . '\',
    no_linking_pages        =   \'' . $no_linking_pages . '\',
    no_title                =   \'' . $no_title . '\',
    service_page            =   \'' . $service_page . '\',
    full_page               =   \'' . $full_page . '\',
    no_banner               =   \'' . $no_banner . '\',
    no_comment              =   \'' . $no_comment . '\',
    no_toc                  =   \'' . $no_toc . '\',
    no_similar_pages        =   \'' . $no_similar_pages . '\',
    image                   =   \'' . $image . '\',
    image_ID                =   \'' . $image_ID . '\',
    featured_video_ID       =   \'' . $featuredVideo_ID . '\',
    latest_update_user_ID   =   ' . $user->ID . ',
    cache_expiration        =   \'' . date('y-m-d') . ' 00:01\',
    revision                =   IFNULL(revision, 0) + 1
    WHERE ID                =   ' . $ID . '
    LIMIT 1;';

    if (!$db->query($query)) {
        echo '{"status": 504, "description": "Query error while updating. ' . $db->lastError . '"}';

        return;
    }

    if (!$fabwiki->updateRevision($page_ID, $revision, $oldContent, $user_ID)) {
        echo '{"status": 504, "description": "Query error while creating the revision record. ' . $db->lastError . '"}';

        return;
    }

    $fabwiki->updateTags($ID, $_POST['tags']);
    $fabwiki->updateInternalTags($ID, $_POST['internalTags']);
    $fabwiki->updateKeywords($ID, $_POST['keywords']);

    $fabwiki->createOutboundTrackbacks($ID);
    $fabwiki->updateFiles($ID);
    $fabwiki->updateStats($ID);

    $fabwiki->updateSeoKeywords($ID, $_POST['seoKeywords']);
    echo '{"status": 200,  "ID": ' . $ID . ',  "description": "Ok."}';

    return;

} else {

    // If a master page has not been passed we create a master here
    if (!isset($_POST['master_ID']) || ((int) $_POST['master_ID'] == 0)) {
        $query = 'INSERT INTO ' . $db->prefix . 'wiki_masters 
                  (creation_date) 
                  VALUES 
                  (\'' . date('Y-m-d') . '\')';

        if (!$db->query($query)) {
            echo '{"status": 505,"description": "Unable to create master."}';
        }
        $master_ID = $db->insert_id;
    } else {
        $master_ID = (int) $_POST['master_ID'];
    }

    $query = '
    INSERT INTO ' . $db->prefix . 'wiki_pages (master_ID, 
                                              `language`,
                                               type_ID,
                                               status_ID,
                                               license_ID,
                                               creation_user_ID,
                                               latest_update_user_ID,
                                               category_ID, 
                                               title, 
                                               title_alternative, 
                                               trackback,
                                               short_description,
                                               metadata_description,
                                               creation_date,
                                               visible_from_date,
                                               visible_to_date, 
                                               content,
                                               notes, 
                                               additional_data, 
                                               no_editor, 
                                               no_index, 
                                               no_search, 
                                               no_info, 
                                               no_linking_pages, 
                                               service_page, 
                                               full_page,
                                               no_banner,
                                               no_comment,
                                               no_toc,
                                               no_similar_pages,
                                               no_title,
                                               image,
                                               image_ID,
                                               featured_video_ID,
                                               visible,
                                               revision
                                               )
    VALUES
    (
    \'' . $master_ID . '\',
    \'' . $language . '\',
    \'' . $type_ID . '\',
    \'' . $status . '\',
    \'' . $license . '\',
    \'' . $user->ID . '\',
    \'' . $user->ID . '\',
    \'' . $category . '\',
    \'' . $title . '\',
    \'' . $title_alternative . '\',
    \'' . $trackback . '\',
    \'' . $shortDescription . '\',
    \'' . $metaDataDescription . '\',
    \'' . date('Y-m-d') . '\',
    ' . ($startDate === '' ? 'null' : '\'' . $startDate . '\'' ) . ',
    ' . ($endDate === '' ? 'null' : '\'' . $endDate . '\'' ) . ',
    \'' . $content . '\',
    \'' . $notes . '\',
    \'' . $additionalData . '\',
    \'' . $noEditor . '\',
    \'' . $no_index . '\',
    \'' . $no_search . '\',
    \'' . $no_info . '\',
    \'' . $no_linking_pages . '\',
    \'' . $service_page . '\',
    \'' . $full_page . '\',
    \'' . $no_banner . '\',
    \'' . $no_comment . '\',
    \'' . $no_toc . '\',
    \'' . $no_similar_pages . '\',
    \'' . $no_title . '\',
    \'' . $image . '\',
    \'' . $image_ID . '\',
    \'' . $featuredVideo_ID . '\',
    \'' . $visible . '\',
    1
    );
    ';

    if (!$db->query($query)) {
        echo '{"status": 505,"description": "Unable to store the new page' . $query . '"}';

        return;
    }
    $page_ID = $db->insert_id;
    $fabwiki->updateTags($page_ID, $_POST['tags']);
    $fabwiki->updateInternalTags($page_ID, $_POST['internalTags']);
    $fabwiki->updateKeywords($page_ID, $_POST['keywords']);


    $fabwiki->createOutboundTrackbacks($page_ID);
    $fabwiki->updateFiles($page_ID);
    $fabwiki->updateStats($page_ID);

    $fabwiki->updateSeoKeywords($page_ID, $_POST['seoKeywords']);
    echo '{"status": 200, "description": "Ok.", "master_ID": ' . $master_ID . ',"ID": ' . $page_ID . '}';
}