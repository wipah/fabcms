<?php

if (!$core->adminLoaded)
    die("No way.");

if (!$user->isAdmin)
    die("Only admin here");

switch ($_GET['op']){
    default:
        include 'op_default.php';
        break;
}