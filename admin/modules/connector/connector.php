<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/02/2016
 * Time: 09:00
 */

if (!$core->adminBootCheck())
    die("Check not passed");

echo '<h1>Connector manager</h1>';
$scanDir = $conf['path']['baseDir'] . 'modules/connector/packages/';
foreach (glob($scanDir . '*', GLOB_ONLYDIR) as $dir) {

    $handlerFile = $dir . '/' . 'handler_' . $handler . '.php';
    echo $dir;

    if (file_exists($handlerFile)) {
        echo $handlerFile . '<br/>';
    }
}