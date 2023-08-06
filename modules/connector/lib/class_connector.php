<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 17/05/2015
 * Time: 00:32
 */

namespace CrisaSoft\FabCMS;
class connector
{
    public $foo;
    public $bar = 1;
    private $handlers;

    protected  $connectorData;

    public function callHandler($handler, $connectorData)
    {

        global $conf;
        global $debug;
        global $db;
        global $core;
        global $user;
        global $URI;
        global $template;
        global $log;

        $this->bar = 2;

        $this->connectorData = $connectorData;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'connectors
                  WHERE handler = \'' . $handler . '\' 
                    AND enabled = 1
                  ORDER BY \'order\' ASC';

        $db->setQuery($query);

        if (!$result = $db->executeQuery()){
            echo 'Query error. '. $query;
            return;
        }

        if (!$db->numRows)
            return;


        $scanDir = $conf['path']['baseDir'] . 'modules/connector/packages/';

        while ($row = mysqli_fetch_array($result)) {
            $additionalData = $row['additional_data'];

            if (strlen($additionalData) > 0 && !$additionalData =  json_decode($additionalData)){

                $log->write('error','CONNECTOR','Cannot fetch JSON data. Connector is ' . $row['connector'] . ' and handler is ' . $handler);
                continue;
            }

            $handlerFile = $scanDir . $row['connector'] . '/handler_' . $handler . '.php';

            if (file_exists($handlerFile)) {
                $log->write('info','CONNECTOR', 'Adding ' . $handler);

                include ($handlerFile);
            } else {
                $log->write('error','CONNECTOR','Cannot find the file. Connector is ' . $row['connector'] . ' and handler is ' . $handler);
            }
        }
    }
}