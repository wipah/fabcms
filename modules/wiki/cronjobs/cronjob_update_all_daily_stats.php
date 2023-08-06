<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 01/02/2019
 * Time: 16:55
 */


if (!$core->loaded)
    die ("Direct call");

if (!$additionalData = json_decode($cronjobs->additionalData, true)){
    echo 'JSON error! ' . $cronjobs->additionalData;
    $cronjobs->status = 2;
    $cronjobs->writeLog('Error while decoding JSON. ' . $cronjobs->additionalData);
    return;
}

$period = new DatePeriod(
    new DateTime($additionalData['dateFrom']),
    new DateInterval('P1D'),
    new DateTime($additionalData['dateTo'])
);

echo('Days count is . ' . count($period) . '<br />');
$cronjobs->writeLog('Days count is . ' . count($period));

foreach ($period as $singleDay) {

    echo '*** NEW DAY IS COMING *** <br/>';

    $queryInsert = 'INSERT INTO ' . $db->prefix . 'stats_daily
              (
                date,
                module,
                submodule,
                IDX,
                hits,
                is_bot
              ) VALUES';

    $singleDay = $singleDay->format('Y-m-d');

    echo('Working on ' . $singleDay . '<br />' );
    $cronjobs->writeLog('Working on ' . $singleDay );

    // Delete all references from yesterday
    $query = 'DELETE 
          FROM ' . $db->prefix . 'stats_daily 
          WHERE date = \'' . $singleDay . '\';';
    $db->setQuery($query);

    if (!$db->executeQuery('delete')){
        $cronjobs->status = 2;
        $cronjobs->writeLog("Unable to delete. Query error. $query");
        return;
    }


    $query = 'SELECT IDX, 
                 COUNT(ID) AS hits 
          FROM fabcms_stats 
          WHERE module = \'wiki\' 
            AND submodule = \'pageView\' 
            AND is_bot != 1 
            AND `date` >= \'' . $singleDay . ' 00:00:00\' AND `date` <= \'' . $singleDay . ' 23:59:59\' 
          GROUP BY IDX';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')){
        $cronjobs->status = 2;
        $cronjobs->writeLog("Unable to select HITS. Query error. $query");

        echo '<pre>Query error:' . $query . '</pre>';
        return;
    }

    if (!$db->affected_rows){
        $cronjobs->status = 0;
        $cronjobs->writeLog('No data on . ' . $singleDay );

        echo 'No data on ' . $singleDay . '<br/>';
        continue;
    }



    while ($row = mysqli_fetch_assoc($result)){
        $queryInsert .= '(
    \''. $singleDay . '\',
    \'wiki\',
    \'pageview\',
    \'' . $row['IDX'] . '\',
    \'' . $row['hits'] . '\',
    0
    ),';
    }

    $queryInsert = substr($queryInsert, 0, -1);

    $db->setQuery($queryInsert);
    if (!$db->executeQuery('insert')){
        $cronjobs->status = 2;
        $cronjobs->writeLog('Query error on last update . ');
        echo '<pre>' . $queryInsert . '</pre>';
    } else {
        $cronjobs->status = 1;
        $cronjobs->writeLog('Stats were refreshed. <br/>' );
        echo 'Stats were refreshed.';
    }

    echo '<pre>' . $queryInsert  .'</pre>';
}

$relog->write(['module' => 'wiki', 'operation' => 'cronjob_update_daily', 'details' => $cronjobs->getLog()]);