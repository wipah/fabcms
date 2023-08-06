<?php
/**
* Created by PhpStorm.
* User: Fabrizio
* Date: 05/12/2016
* Time: 16:16
*/
if (!$core->adminBootCheck())
    die("Check not passed");

set_time_limit ( 600 );

$this->noTemplateParse = true;

$log->write('INFO','WIKI:RebuildPages', 'Start rebuilding pages');
$query = 'SELECT P.ID 
          FROM ' . $db->prefix . 'wiki_pages AS P';


if (!$result = $db->query($query)){
    $log->write('INFO','WIKI:RebuildPages', 'Unable to select pages');
    echo 'Unable to select pages. ' . $query;
    return false;
}

// Truncate table
$query = 'TRUNCATE `' . $db->prefix . 'wiki_outbound_trackback`;';

if (!$db->query($query)){
    $log->write('ERROR','Wiki:TruncateOutboundLinks', 'Unable to truncate outbound links.');
} else {
    $log->write('Info','Wiki:TruncateOutboundLinks', 'Outbound links table has been truncated.');
}

// Truncate table
$query = 'TRUNCATE `' . $db->prefix . 'wiki_pages_files`;';

if (!$db->query($query)){
    $log->write('ERROR','Wiki:TruncateOutboundLinks', 'Unable to truncate outbound links.');
} else {
    $log->write('Info','Wiki:TruncateOutboundLinks', 'Outbound links table has been truncated.');
}

$totalPages = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $ID = $row['ID'];
    $fabwiki->updateFiles($ID, ['noDelete' => true]);
    $fabwiki->createOutboundTrackbacks($ID, ['noDelete' => true]);
    $totalPages++;
}

$log->write('INFO','WIKI:RebuildPages', 'Files and outbound pages are refreshed');
printf("%d pages have been refreshed", $totalPages);