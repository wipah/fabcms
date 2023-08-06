<?php

if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

$ID = $_POST['ID'];
$newName = $_POST['newName'];

$fabMedia->renameMedia($ID, $newName);