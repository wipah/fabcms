<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/08/2018
 * Time: 08:51
 */

switch ( $_GET['op']) {
    case 'config';
        require_once 'op_config.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}