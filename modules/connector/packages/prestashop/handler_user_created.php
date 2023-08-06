<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 05/02/2016
 * Time: 20:41
 */
include 'lib/class_prestashop.php';
$prestashop = new \CrisaSoft\FabCMS\prestashop($additionalData);

$prestashop->connectorData = $this->connectorData;

$prestashop->addUser();