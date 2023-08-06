<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 03/08/2018
 * Time: 09:55
 */

if (!$isInstalling)
    die ("Not installing");

// Master
$query = 'INSERT INTO ' . $db->prefix . 'wiki_categories_masters (`type`) VALUES (1)';

$db->setQuery($query);

if (!$db->executeQuery('insert')){
    echo 'Cannot insert category master. ' . $query;
    die();
}

foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'wiki_categories_details 
              (
                master_ID, 
                lang, 
                name, 
                description
              ) 
              VALUES 
              (
                  1,
                  \'' . $language . '\',
                  \'Category ' . $language . '\',
                  \'Description ' . $language . '\'
              )';

            $db->setQuery($query);

            if (!$db->executeQuery('insert')){
                echo 'Cannot insert. ' . $query;
                die();
            }
}

foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'wiki_config 
              (
                param, 
                lang, 
                value
              ) 
              VALUES 
              (
                  \'homePageNamespace\',
                  \'' . $language . '\',
                  \'main-page\'
              )';

    $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Cannot insert wiki config. ' . $query;
        die();
    }
}

foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'wiki_config 
              (
                param, 
                lang, 
                value
              ) 
              VALUES 
              (
                  \'nameSpaceSeparator\',
                  \'' . $language . '\',
                  \':\'
              )';

    $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Cannot insert wiki config. ' . $query;
        die();
    }
}

foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'wiki_config 
              (
                param, 
                lang, 
                value
              ) 
              VALUES 
              (
                  \'wikiName\',
                  \'' . $language . '\',
                  \'FabCMS wiki\'
              )';

    $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Cannot insert wiki config. ' . $query;
        die();
    }
}

$i = 1;
foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'wiki_masters 
              (
                creation_date
              ) 
              VALUES 
              (
                  \'' . date('Y-m-d') . '\'
              )';

    $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Cannot insert wiki master. ' . $query;
        die();
    }

    $query = '
    INSERT INTO ' . $db->prefix . 'wiki_pages
    (
    master_ID,
    type_ID,
    category_ID,
    status_ID,
    license_ID,
    creation_user_ID,
    latest_update_user_ID,
    language,
    title,
    trackback,
    creation_date,
    content,
    full_page,
    revision,
    visible
    )
    VALUES
    (
    ' . $i . ', /* Master */
    1, /* Type */
    1, /* Category */
    2, /* Status */
    1,
    1,
    1,
    \'' . $language . '\',
    \'Welcome / Benvenututo\',
    \'welcome-benvenuto\',
    \''. date('Y-m-d') .'\',
    \'<h1>FabCms was installed</h1><p>Welcome</p>\',
    1,
    1,
    1)
    ';

    $db->setQuery($query);

    if (!$db->executeQuery('insert')){
        echo 'Cannot insert welcome page. ' . $query;
        die();
    }
}