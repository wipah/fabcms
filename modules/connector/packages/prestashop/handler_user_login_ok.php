<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/03/2017
 * Time: 10:36
 */
include 'lib/class_prestashop.php';
$prestashop = new \CrisaSoft\FabCMS\prestashop($additionalData);

$prestashop->connectorData = $this->connectorData;

$prestashop->refreshUser();