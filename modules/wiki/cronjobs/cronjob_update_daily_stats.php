<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 18/10/2018
 * Time: 11:57
 */

if (!$core->loaded)
    die ("Direct call");

$yesterday = date('Y-m-d',strtotime("-1 days"));;

$cronjobs->writeLog('*** START AT ' . date('Y-m-d h:i:s') .' ***');
$cronjobs->writeLog("*** Checking for date $yesterday ***");

// Delete all references from yesterday
$query = 'DELETE 
          FROM ' . $db->prefix . 'stats_daily 
          WHERE date = \'' . $yesterday . '\';';

$db->setQuery($query);

if (!$db->executeQuery('delete')){
    $cronjobs->status = 2;
    $cronjobs->writeLog("Unable to delete. Query error.");

    saveLog();

    return;
}

$query = 'SELECT IDX, 
                 COUNT(ID) AS hits 
          FROM ' . $db->prefix . 'stats 
          WHERE module = \'wiki\' 
            AND submodule = \'pageView\' 
            AND is_bot != 1 
            AND `date` >= \'' . $yesterday . ' 00:00:00\' AND `date` <= \'' . $yesterday . ' 23:59:59\' 
          GROUP BY IDX';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    $cronjobs->status = 2;
    $cronjobs->writeLog("Unable to select. Query error. <br/>Error is: " . $db->lastError . '<br/>Query is: ' . $query);

    echo '<pre>Query error:' . $query . '</pre>';

    saveLog();

    return;
}

if (!$db->numRows){
    $cronjobs->writeLog( "*** NO DATA *** " . $queryInsert);
    $cronjobs->status = 0;
    echo 'No data.';

    saveLog();

    return;
}

$cronjobs->writeLog('SELECT QUERY IS: ' . $query);

$queryInsert = 'INSERT INTO ' . $db->prefix . 'stats_daily
              (
                date,
                module,
                submodule,
                IDX,
                hits,
                is_bot
              ) VALUES
';


while ($row = mysqli_fetch_assoc($result)){
    $queryInsert .= '(
    \''. $yesterday . '\',
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
    $cronjobs->writeLog( "Unable to insert. Query error." . $queryInsert);
    echo '<pre>' . $queryInsert . '</pre>';
} else {
    $cronjobs->status = 1;
    $cronjobs->writeLog('*** All done ***');
    echo 'Stats were refreshed.';
}

$cronjobs->writeLog('*** END AT ' . date('Y-m-d h:i:s') .' ***');
saveLog();

function saveLog()
{
    global $relog;
    global $cronjobs;

    $relog->write(['module' => 'wiki', 'operation' => 'cronjob_update_daily', 'details' => $cronjobs->getLog()]);
}
