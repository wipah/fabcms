<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/06/2019
 * Time: 11:13
 */

switch ($path[2]){
    case 'show':
        require_once 'op_licenes_show.php';
        break;
    default:
        require_once 'op_licenses_default.php';
        break;
}