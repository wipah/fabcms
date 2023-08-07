<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 05/12/2016
 * Time: 16:16
 */
if (!$core->adminBootCheck())
    die("Check not passed");

set_time_limit(600);

$this->noTemplateParse = true;

$log->write('INFO', 'WIKI:RebuildPages', 'Start rebuilding stats');
$query = 'SELECT P.ID 
          FROM ' . $db->prefix . 'wiki_pages AS P';

if (!$result = $db->query($query)) {
    $log->write('INFO', 'WIKI:RebuildPages', 'Unable to select pages');
    echo 'Unable to select pages. ' . $query;

    return false;
}

// Truncate table
$query = 'TRUNCATE `' . $db->prefix . 'wiki_pages_statistics`;';

if (!$db->query($query)) {
    $log->write('ERROR', 'Wiki:TruncateOutboundLinks', 'Unable to truncate statistics.');
} else {
    $log->write('Info', 'Wiki:TruncateOutboundLinks', 'Statistics links table has been truncated.');
}

$totalPages = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $ID = $row['ID'];
    $fabwiki->updateStats($ID);
    $totalPages++;
}

$log->write('INFO', 'WIKI:RebuildPages', 'Stats habe been refreshed.');
printf("Stats for %d pages have been refreshed", $totalPages);