<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 29/03/2018
 * Time: 12:16
 */

if (!$core->loaded)
    die ("Direct call");

$date = new DateTime();
$date->modify("-1 day");
$yesterday = $date->format("Y-m-d");

// Get all wiki pages
$query = 'SELECT ID, 
                 title, 
                 stats_last_refresh 
          FROM ' . $db->prefix . 'wiki_pages 
          WHERE service_page != 1';

          /* AND (stats_last_refresh IS NULL OR stats_last_refresh <= \'' . $yesterday . '\') */

echo $query . '<br/>' . PHP_EOL;



if (!$resultSelect = $db->query($query)) {
    $cronjobs->status = 2;
    $cronjobs->writeLog('Query error.');

    echo $query;
    return;
}

if (!$db->affected_rows) {
    $cronjobs->status = 2;
    $cronjobs->writeLog( 'No pages.');
    return;
}

while ($row = mysqli_fetch_array($resultSelect)) {

    $query = 'SELECT SUM(hits) AS hits 
              FROM ' . $db->prefix . 'stats_daily
              WHERE module    =     \'wiki\'
                AND submodule =     \'pageView\' 
                AND IDX       =     ' . $row['ID'] . ';';
    

    if (!$resultHit = $db->query($query)) {
        $cronjobs->status = 2;
        $cronjobs->writeLog('Query error.');

        echo $query;
        return;
    }

    if (!$db->affected_rows) {
        $hit = 0;
    } else {
        $rowHit = mysqli_fetch_assoc($resultHit);
        $hits   = $rowHit['hits'];
    }

    $cronjobs->log = '[ID ' . $row['ID'] . '] - ' . $row['title'] . ' - has ' . $hits . ' hits  <br/>' . PHP_EOL;

    if ( (int) $hits > 0) {

        $query = '
              UPDATE ' . $db->prefix . 'wiki_pages 
              SET hits               = ' . $hits . ', 
                  stats_last_refresh = \'' . $yesterday.'\'
              WHERE ID = ' . $row['ID'] . '
              LIMIT 1';

        

        if (!$db->query($query)) {
            echo $query;

            $cronjobs->status = 2;
            $cronjobs->writeLog('Unable to update the stats.' . $query);
            return;
        }
    }

}