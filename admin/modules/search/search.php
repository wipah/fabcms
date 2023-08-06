<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 24/05/2015
 * Time: 09:43
 */

switch ($_GET['op']) {
    case 'ajaxSearch':
        include 'op_ajax_search.php';
        break;
    default:
        include 'op_default.php';
}