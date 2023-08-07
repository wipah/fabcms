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

if (!isset($core->adminLoaded)) {
    die("Only admins here");
}

if (!isset($_POST['dummy'])) {
  echo 'Direct call detected';
  return;
}

if (!isset($_GET['ID'])) {
  echo 'No ID passed';
  return;
}

if (!isset($_POST['name']) || empty($_POST['name']) == 'true') {
  echo 'Name not passed.';
  return;
}


echo '<h1>Save updated hook</h1>';

$template->sidebar .= $template->simpleBlock('Quick links', '<a href="admin.php?module=hooks&op=new">New hook</a><br />');


$ID = (int) $_GET['ID'];
$name = $core->in($_POST['name'], TRUE);
$html = $core->in($_POST['html']);
$_POST['enabled'] === 'true' ? $enabled = '1' : $enabled = '0';

$template->navBar[] = '<a href="admin.php?module=hooks">Hooks editor</a>';
$template->navBar[] = '<a href="admin.php?module=hooks&op=edit&ID=' . $ID . '">Editing hook (ID:' . $ID . ')</a>';

$query = "UPDATE {$db->prefix}hooks
SET
	name 	= '$name',
	html 	= '$html',
	enabled = '$enabled'
WHERE ID = '$ID'
LIMIT 1;";

if (!$db->query($query)) {
  echo 'Query error: ' . $query;
  return;
}

if (!$db->affected_rows) {
  echo 'No data changed';
}
else {
    echo $template->getCustomBox( ['class' => 'info', 'title' => 'Hook saved', 'message' => 'Hook updated. <a href="admin.php?module=hook&op=edit&ID=' . $ID. '">Click here</a> to edit it.'] );

}