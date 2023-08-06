<?php

if (!$core->adminLoaded)
    die('Direct call detected');

switch ($_GET['op']) {
    default:
        require_once 'op_default.php';
        break;
}