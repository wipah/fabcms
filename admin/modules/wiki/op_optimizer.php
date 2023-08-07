<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 05/09/2017
 * Time: 10:31
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$output = '<h2>Lenght optimization</h2>';
$query = '
SELECT PAGES.ID, 
       PAGES.title,
       STATS.words,
       (
       	SELECT SUM(DAILY.HITS) total
       	FROM ' . $db->prefix . 'stats_daily DAILY
      	 WHERE DAILY.IDX = PAGES.ID
       	 AND DAILY.module = \'wiki\'
       	 AND DAILY.submodule = \'pageView\'
       	 AND DAILY.is_bot != 1
       	 AND DAILY.date >= (DATE_SUB( NOW() ,INTERVAL 1 MONTH))
       ) lastMonth
FROM ' . $db->prefix . 'wiki_pages PAGES
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics STATS
ON STATS.page_ID = PAGES.ID
WHERE PAGES.visible = 1
AND PAGES.service_page != 1
AND LENGTH(PAGES.internal_redirect) = 0
AND STATS.words < 300
ORDER BY STATS.words ASC';

if (!$result = $db->query($query)){
    $output .= 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    $output .= 'No result';
} else {
    $output .= '<div class="table-responsive">
        <table class="table table-bordered table-striped" id="lenght">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Language</th>
            <th>Words</th>
            <th>Last month</th>
          </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {

        $output .= '<tr>
                    <td><a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['ID'] . '</a></td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['language'] . '</td>
                    <td>' . $row['words'] . '</td>
                    <td>' . $row['lastMonth'] . '</td>
              </tr>';
    }

    $output .= '</tbody>
    </table>
    </div>';
}

$output .= '<h2>Zero visit pages (last month from now)</h2>';
$query = '
SELECT PAGES.ID,
       PAGES.title, 
       PAGES.language,
       PAGES.metadata_description,
	   STATS.words,
	   PAGES.creation_date,
	   PAGES.last_update
FROM ' . $db->prefix .'wiki_pages PAGES
LEFT JOIN ' . $db->prefix .'wiki_pages_statistics AS STATS
ON STATS.page_ID = PAGES.ID
WHERE PAGES.service_page != 1 AND LENGTH(PAGES.internal_redirect) = 0
AND PAGES.visible = 1
AND NOT EXISTS (
	SELECT STATS.ID 
	FROM ' . $db->prefix . 'stats_daily STATS 
	WHERE STATS.IDX = PAGES.ID
	AND STATS.date >= DATE_SUB( NOW(), INTERVAL 1 MONTH)
	) ;';

if (!$result = $db->query($query)){
    $output .= 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    $output .= 'No result';
} else {
    $output .= '<div class="table-responsive"><table class="table table-bordered table-striped" id="zeroVisit">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Lang</th>
            <th>Words</th>
            <th>Description lenght</th>
            <th>Creation date</th>
            <th>Last update</th>
          </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {

        $output .= '<tr>
                    <td><a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['ID'] . '</a></td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['language'] . '</td>
                    <td>' . $row['words'] . '</td>
                    <td>' . strlen($row['metadata_description']) . '</td>
                    <td>' . $row['creation_date'] . '</td>
                    <td>' . $row['last_update'] . '</td>
              </tr>';
    }

    $output .= '</tbody>
    </table>
    </div> ';
}

$output .= '<h2>Pages not updated</h2>';
$query = '
SELECT PAGES.ID, 
       PAGES.trackback, 
	   PAGES.title, 
	   PAGES.`language`, 
	   PAGES.type_ID,
       PAGES.metadata_description,
	   PAGES.last_update,
       SUM(DAILY.HITS) AS total,
       MAX(DAILY.date) AS last_hit,
       STATS.words,
       GROUP_CONCAT(DISTINCT T.tag SEPARATOR \', \') AS tags,
       GROUP_CONCAT(DISTINCT K.keyword SEPARATOR \', \') AS keywords
FROM ' . $db->prefix . 'wiki_pages AS PAGES 
LEFT JOIN ' . $db->prefix . 'stats_daily DAILY
    ON PAGES.ID = DAILY.IDX
    AND DAILY.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    AND DAILY.is_bot != 1
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics STATS
    ON STATS.page_ID = PAGES.ID
LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
    ON T.page_ID = PAGES.ID
LEFT JOIN ' . $db->prefix . 'wiki_pages_keywords AS K
    ON K.page_ID = PAGES.ID
WHERE 
    PAGES.visible = 1 
    AND PAGES.service_page != 1 
    AND LENGTH(PAGES.internal_redirect) = 0
GROUP BY PAGES.ID
ORDER BY PAGES.last_update ASC
LIMIT 200';

if (!$result = $db->query($query)){
    $output .= 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    $output .= 'No result';
} else {
    $output .= '<div class="table-responsive">
        <table class="table table-bordered table-striped" id="notUpdated">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Lang</th>
            <th>Tags</th>
            <th>Keywords</th>
            <th>Words</th>
            <th>Description lenght</th>
            <th>Last update</th>
            <th>Last hit</th>
          </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {

        $output .= '<tr>
                    <td><a href="' . $URI->getBaseUri() . 'admin/admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['ID'] . '</a></td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['language'] . '</td>
                    <td>' . $row['tag'] . '</td>
                    <td>' . $row['keywords'] . '</td>
                    <td>' . $row['words'] . '</td>
                    <td>' . (strlen($row['metadata_description'])) . '</td>
                    <td>' . ($core->getDateTime($row['last_update'])) . '</td>
                    <td>' . ($core->getDate($row['last_hit'])) . '</td>
              </tr>';
    }

    $output .= '</tbody>
    </table>
    </div>';
}

$output .= '<script type="text/javascript">
/*
 $("#lenght, #zeroVisit").DataTable({
     "order": [[ 3, "desc" ]]
   }
 );

 $("#notUpdated").DataTable({"order": [[ 5, "asc" ]]});
 */
</script>';


echo $output;

$output = '
<html>
    <head>
        <title>Wiki optimization</title>
        <style>
        .table {
          border-collapse: collapse;
        width: 100%;
        }
        
        .table, th, td {
          border: 1px solid black;
          padding: 3px;
        }
        </style>
    </head>

<body>' . $output .'</body></html>';

file_put_contents('output.html', $output);