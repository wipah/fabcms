<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/04/2018
 * Time: 11:09
 */

if (!$core->loaded || !$user->isAdmin)
    die("Security");


switch ($_GET['op']) {
    case 'ajaxSearch':
        require_once 'op_ajax_search.php';
        return;
    case 'ajaxShow':
        require_once 'op_ajax_show.php';
        break;
    default:
        require_once 'op_default.php';

        return;
}