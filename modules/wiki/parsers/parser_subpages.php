<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/04/2017
 * Time: 16:01
 */

if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
    return $return;
}

var_dump($dataArray);