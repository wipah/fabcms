<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/11/2017
 * Time: 11:35
 */

if (!$core->loaded)
    die ("Direct access");

switch ($path[2]) {
    case 'init':
        include 'op_init.php';
        break;
}