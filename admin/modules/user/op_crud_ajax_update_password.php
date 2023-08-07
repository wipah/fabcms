<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = TRUE;

// Check if ID is passed
if (!isset($_GET['ID'])){
    echo 'ID was not passed!';
    return;
}
$ID = (int) $_GET['ID'];

// Check if password is passed and more than 4 chars
if (strlen($_POST['password']) < 4){
    echo 'Password is shorter than 4 chars!';
    return;
}

$password = $_POST['password'];

$query = 'UPDATE ' . $db->prefix . 'users
SET password = \'' . $user->getPasswordHash($password) . '\'
WHERE ID = ' . $ID . '
LIMIT 1;
';

if ($db->query($query)){
    echo 'Password changed';
    $log->write('user_update_password','user','ID:'. $ID);
}else{
    echo 'Query error: ' . $query;
}