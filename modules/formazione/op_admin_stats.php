<?php

if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

switch ($path[4]){
    default:
        require_once 'op_admin_stats_default.php';
        break;
}