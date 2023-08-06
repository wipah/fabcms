<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/11/2017
 * Time: 11:40
 */

if (!$core->loaded)
    die("Direct call detected");

require_once __DIR__ . '/lib/class_cronjobs.php';
global $cronjobs;
$cronjobs = new \CrisaSoft\FabCMS\cronjobs();

echo 'Cronjob started on ' . date('d-m-Y h:i:s') . '<br/> ***************** <br/>';

$query = 'SELECT * 
          FROM ' . $db->prefix . 'cronjobs
          WHERE enabled = 1
          AND next_run <= \'' . date('Y-m-d h:i:s') . '\';';

$db->setQuery($query);

$this->noTemplateParse = true;

if (!$resultJobs = $db->executeQuery($query)) {
    echo 'Query error.';
    return;
}

if (!$db->affected_rows) {
    echo 'No row';
    return;
}

while ($rowJobs = mysqli_fetch_array($resultJobs)) {

    $cronjobs->additionalData= $rowJobs['additional_data'];

    $fileName = $conf['path']['baseDir'] . 'modules/' .
        $rowJobs['module'] .
        '/cronjobs/cronjob_' . $rowJobs['operation'] . '.php';

    if (!file_exists($fileName)) {
        echo '[ERROR] File was not found: ' . $fileName . '<br/>';
    } else {
        echo '[INFO] Including file ' . $fileName . '<br/>';
        require_once $fileName;
    }


    file_put_contents( __DIR__ . '/logs/' . $rowJobs['module'] . '-' .
                                                    $rowJobs['operation'] . '-' .
                                                    date('Y-m-d-h:i:s') . '.log' ,
                                                    $core->in($cronjobs->getLog()));

    // Updating info
    $query = 'UPDATE ' . $db->prefix . 'cronjobs 
              SET next_run = NOW() + INTERVAL ' . $rowJobs['interval'] . ' MINUTE,
                  latest_check = NOW(),
                  latest_status = ' . $cronjobs->status . ',
                  log = \'' . $core->in($cronjobs->getLog()) . '\'
              WHERE ID = ' . $rowJobs['ID'] . '
              LIMIT 1';

    $db->setQuery($query);

    echo 'OUTPUT LOG IS: <pre>' . $cronjobs->getLog() . '</pre>';

    if (!$db->executeQuery('update')) {
        echo '--> Query error. ';
    } else {
        echo '--> Updated. <br/>';
    }
}