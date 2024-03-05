<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 20/07/2018
 * Time: 11:10
 */

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']){
    case 'emailTester':
        require_once 'op_email_tester.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}
