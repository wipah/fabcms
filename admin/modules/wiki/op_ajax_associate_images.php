<?php
if (!$core->adminBootCheck())
    die("Check not passed");

set_time_limit ( 600 );

$this->noTemplateParse = true;

$log->write('INFO','WIKI:AssociateImages', 'Start associating images');

$query = 'SELECT * FROM
' . $db->prefix . 'wiki_pages AS P
WHERE (nullif(P.image_ID, \' \') IS NULL) OR (nullif(P.image, \' \') IS NULL)';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){
    echo 'Query error: ' . $query;
    return;
}

if (!$db->numRows){
    echo 'No page found!';
    return;
}
$pageUpdated = 0;
$pageSkipped = 0;
while ($row = mysqli_fetch_assoc($result)){
    $page_ID = $row['ID'];

    // Check if exists a multimedia
    $query = 'SELECT * FROM ' . $db->prefix . 'wiki_pages_files AS P 
              WHERE P.page_ID = ' . $page_ID . ' AND `type` = \'image\'
             
              ORDER BY ID DESC
              LIMIT 1';

    $db->setQuery($query);
    if (!$resultPage = $db->executeQuery('select')){
        echo 'Query error ' . $query;
        return;
    }


    if ($db->numRows){
        $rowImage = mysqli_fetch_assoc($resultPage);
        $image_ID = $rowImage['fabmedia_ID'];
        $filename = $rowImage['filename'];

        $query = 'UPDATE ' . $db->prefix . 'wiki_pages
                  SET image = \'' . $filename . '\',
                  image_ID = \'' . $image_ID. '\'
                  WHERE ID = ' . $page_ID . '
                  LIMIT 1;  
                 ';
        $db->setQuery($query);

        if (!$db->executeQuery('update')){
            echo 'Query error: ' . $query;
            return;
        }
        $pageUpdated++;
    } else {
        $pageSkipped++;
    }
}

echo 'Done. ' . $pageUpdated . ' pages were updated, ' . $pageSkipped . ' where skipped.';