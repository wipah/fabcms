<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/12/2017
 * Time: 12:44
 */

switch ($_GET['op']) {
    default:
        require_once 'op_default.php';
        break;
}