<?php
include 'lib/class_prestashop.php';
$prestashop = new \CrisaSoft\FabCMS\prestashop($additionalData);

$prestashop->connectorData = $this->connectorData;

if (!isset($connectorData['email'])){
    $log->write('error', 'HANDLER', 'Prestashop handler. Email is not passed. Aborting');
    return;
}

$prestashop->confirmUser();