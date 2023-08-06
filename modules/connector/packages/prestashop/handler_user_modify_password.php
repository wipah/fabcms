<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/03/2016
 * Time: 15:08
 */

include 'lib/class_prestashop.php';
$prestashop = new \CrisaSoft\FabCMS\prestashop($additionalData);

$prestashop->connectorData = $this->connectorData;

$prestashop->updatePassword();