<?php

/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/01/2017
 * Time: 19:53
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

set_time_limit(1200);

echo 'Rebuilding SEO. <br/>';

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages
          WHERE (service_page = 0 OR service_page IS NULL)';
$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

$i = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $ID = $row['ID'];
    // echo '*** Processing page ' . $row['title'] . ' (ID: ' . $ID . ') *** <br/>';

    // Check if page has seo kewyords
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_seo 
              WHERE page_ID = ' . $ID . ';';

    // echo '<pre>' . $query . '</pre>';

    $db->setQuery($query);

    if (!$resultKeywords = $db->executeQuery()) {
        echo $query;
        return;
    }

    if (!$db->numRows) {
        // echo '--> Page ' . $row['title'] . ' has no SEO keyords. Attemping to create by title. <br/>';

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_seo 
                    (page_ID, keyword, `order`) 
                  VALUES (' . $ID . ', 
                          \''  . $core->in($row['title']) . '\', 
                          0)';

        $db->setQuery($query);

        if (!$db->executeQuery('insert')) {
            echo 'Query error. ' . $query;
            return;
        } else {
            // echo '--> Page ' . $row['title'] . ' has one keyword from title:' . $row['title'] . ' <br/>';

        }

        $fabwiki->updateSeo($ID, $row['title']);

    } else {
        // echo 'Page ' . $row['title'] . ' has ' . $db->numRows . ' SEO keyords. <br/>';

        // Delete old references
        /*
        $query = 'DELETE 
                  FROM ' . $db->prefix . 'wiki_pages_seo 
                  WHERE page_ID = ' . $ID;

        $db->setQuery($query);
        echo '--> Deleting old references. <br/>';

        if (!$db->executeQuery('delete')) {
            echo 'Error deleting!!! ' . $query;
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_keywords_delete_query_error',
                'details'   => 'Cannot delete while update keywords. Query error. ' . $query,
            ]);

            return;
        }
        echo '--> References deleted. <br/>';
*/

        while ($rowKeyword = mysqli_fetch_assoc($resultKeywords)) {
            // echo '--> Updating keyword: ' . $rowKeyword['keyword'] . '  <br/>';

            $fabwiki->updateSeo($ID, $rowKeyword['keyword']);

        }
    }
    $fabwiki->updateSeoFirsKeyword($ID);

    $i++;
}

echo sprintf('%d pages updated', $i);