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
  echo 'Chiamata diretta.';
  return;
}

$template->navBarAddItem('Hook editor');

$template->sidebar .= $template->simpleBlock('Quick links', '<a href="admin.php?module=hooks&op=new">New hook</a><br />');

echo '<h1>Hooks editor</h1>';

switch ($_GET['op']) {
  case 'edit':
    if (!isset($_GET['ID'])) {
      echo 'ID not passed';
    }

    $ID = (int) $_GET['ID'];
      $action = 'admin.php?module=hooks&op=saveUpdate&ID=' . $ID;

      $query = "SELECT * FROM {$db->prefix}hooks 
                WHERE ID = '$ID' LIMIT 1;";

    $db->setQuery($query);

    if (!$db->executeQuery('select')) {
      echo 'Query error. ' . $query;
      return;
    }

    if (!$db->affected_rows) {
        echo 'No hook found with ID ' . $ID;
      return;
    }

    $row = $db->getResultAsArray();

      (int) $row['enabled'] === 1 ? $checkboxenabled = 'checked="checked"' : $checkboxenabled = '';
    break;
  case 'new':
      $action = 'admin.php?module=hooks&op=saveNew';
    break;
}

echo "

<form action='$action' method='post'>

	<input type='hidden' value='dummy' name='dummy'>

    <div class='row'>
        <div class='col-md-2'>ID</div>
        <div class='col-md-10'>
            <input disabled='disabled'  class='form-control' value='{$row['ID']}'>
        </div>
    </div>
	
	<div class='row'>
        <div class='col-md-2'>Name</div>
        <div class='col-md-10'>
            <input name='name'  class='form-control' value='{$row['name']}'>
        </div>
    </div>
	
	<div class='row'>
        <div class='col-md-2'>Data</div>
        <div class='col-md-10'>
            <textarea id='hookData' name='html' class='form-control' >{$row['html']}</textarea>
        </div>
    </div>

	
	<div class='row'>
        <div class='col-md-3'>Status</div>
        <div class='col-md-9'>
            <input type='checkbox' $checkboxenabled name='enabled' value='true'>
        </div>
    </div>

	<button class='btn btn-info float-right' type='submit'>Save</button>
</form>

<script>

editorTemplateHead = CodeMirror.fromTextArea( document.getElementById(\"hookData\"), {
        mode:  \"json\",
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: \"default elegant\"
    });

</script>

<pre class='mt-5' style='border-top:1px solid #4A4A4A'>Example:
{ \"it\":
        { 
            \"data\" : \"This is data sample\"
        }
}
</pre>";