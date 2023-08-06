<?php

if (!$core->adminBootCheck())
    die("Check not passed");

if (!isset($_POST['ID'])) {
    echo 'ID not passed';
    return;
}

$ID = (int) $_POST['ID'];

$query = 'SELECT ' . $db->prefix . 'fabmedia_gallery';