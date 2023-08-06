<?php

if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_POST['op']){
    default:
        require_once 'op_forum_default.php';
        break;
}