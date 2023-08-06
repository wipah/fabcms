<?php
if (!$core->adminLoaded)
    die("Not loaded from admin");

$this->noTemplateParse = true;

$query = '
SELECT ROUND(AVG(STATS.words), 2) average_words,
ROUND(AVG(STATS.`tables`), 2) average_tables,
ROUND(AVG(STATS.images), 2) average_images
FROM ' . $db->prefix . 'wiki_pages PAGES
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics STATS
ON STATS.page_ID = PAGES.ID
WHERE PAGES.visible = 1
AND PAGES.service_page != 1
AND (LENGTH(PAGES.internal_redirect) = 0)
';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error.' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No stats';
    return;
}

$row = mysqli_fetch_assoc($result);
$averageWords   = $row['average_words'];
$averageTables  = $row['average_tables'];
$averageImages  = $row['average_images'];

$query = '
SELECT COUNT(PAGES.ID)  AS total_pages,
		 SUM(STATS.words)  AS total_words,
		 MONTH(PAGES.creation_date) AS month,
		 YEAR(PAGES.creation_date) AS year
FROM ' . $db->prefix . 'wiki_pages PAGES
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS STATS
    ON STATS.page_ID = PAGES.ID
WHERE PAGES.visible = 1 
    AND PAGES.service_page != 1
    AND (
	 LENGTH(PAGES.internal_redirect) = 0 OR
	 PAGES.internal_redirect IS NULL
	)
	AND PAGES.creation_date <= LAST_DAY( NOW() - INTERVAL 1 MONTH)
	AND PAGES.creation_date >= LAST_DAY( NOW() - INTERVAL 2 YEAR)
	GROUP BY YEAR(PAGES.creation_date), 
	MONTH(PAGES.creation_date)
ORDER BY total_pages DESC 
LIMIT 1;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error.' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No stats';
    return;
}

$rowAvg = mysqli_fetch_assoc($result);

$query = '
SELECT COUNT(PAGES.ID)  AS total_pages,
		 SUM(STATS.words)  AS total_words
FROM ' . $db->prefix . 'wiki_pages PAGES
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS STATS
    ON STATS.page_ID = PAGES.ID
WHERE PAGES.visible = 1 
    AND PAGES.service_page != 1
    AND (
	 LENGTH(PAGES.internal_redirect) = 0 OR
	 PAGES.internal_redirect IS NULL
	)
	AND PAGES.creation_date BETWEEN \'' . (date('Y-m-01')) .'\' AND NOW()
ORDER BY total_pages DESC 
LIMIT 1';

$db->setQuery($query);

if (!$result = $db->executeQuery('select'))
{
    echo 'Query error.' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No stats';
    return;
}

$rowToday = mysqli_fetch_assoc($result);

$query = '
SELECT COUNT(PAGES.ID)  AS total_pages,
		 SUM(STATS.words)  AS total_words
FROM ' . $db->prefix . 'wiki_pages PAGES
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS STATS
    ON STATS.page_ID = PAGES.ID
WHERE PAGES.visible = 1 
    AND PAGES.service_page != 1
    AND (
	 LENGTH(PAGES.internal_redirect) = 0 OR
	 PAGES.internal_redirect IS NULL
	)
	AND PAGES.creation_date < \'' . (date('Y-m-01')) .'\';';

$db->setQuery($query);

if (!$result = $db->executeQuery('select'))
{
    echo 'Query error.' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No stats';
    return;
}

$rowGlobal = mysqli_fetch_assoc($result);

echo '
<div class="table-responsive">
<table class="table table-condensed table-bordered">
<thead>
    <tr>
        <th>Type</th>
        <th>Target</th>
        <th>Current</th>
        <th>% </th>
    </tr>
</thead>
<tbody>
    <tr>
        <td>Words</td>
        <td>' . $rowAvg['total_words'] . '</td>
        <td>' . $rowToday['total_words'] . '</td>
        <td>' . round($rowToday['total_words'] / $rowGlobal['total_words'] * 100, 4) . '%</td>
    </tr>
    <tr>
        <td>Pages</td>
        <td>' . $rowAvg['total_pages'] . '</td>
        <td>' . $rowToday['total_pages'] . '</td>
        <td>' . round($rowToday['total_pages'] / $rowGlobal['total_pages'] * 100, 4) . '%</td>
    </tr>
    
</tbody>
</table>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th> / </th>
                <th>Words</th>
                <th>Tables</th>
                <th>Images</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>AVG</td>
                <td>' . $averageWords  . '</td>
                <td>' . $averageTables  . '</td>
                <td>' . $averageImages  . '</td>
            </tr>
        </tbody>
    </table>
</div>';