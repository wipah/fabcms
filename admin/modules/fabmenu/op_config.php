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
if (!$core->adminLoaded) {
  die('Direct call detected');
}

$configurationPath = $conf['path']['baseDir'] . 'templates/menu_config.php';

echo '<h1>Built-in menù configuration</h1>';

// We should save?
if (isset($_GET['save'])) {
  if (!isset($_POST['dummy'])) {
    echo 'Direct call detected. You hit F5!';
    return;
  }

  (int) $_POST['enabled'] === 1
    ? $enabled = '$conf[\'template\'][\'overrideMenu\'] = true;'
    : $enabled = '$conf[\'template\'][\'overrideMenu\'] = false;';

  $customMenu = str_replace('\'', '\\', $_POST['customMenu']);

  $configFile =
    <<<EOT
    <?php
/*
 * Built-in menù configuration.
 * This file was auto-generated
 */
\$conf['template'] = array();

$enabled
\$conf['template']['customMenu'] = '$customMenu';
EOT;

  if (!file_put_contents($configurationPath, $configFile)) {
    echo '<div class="ui-state-highlight">Unable to write the file! ' . htmlentities($configurationPath) . '</div>';
  }
  else {
    echo '<div class="ui-state-highlight">Configuration saved!</div>';
  }
}

// Check if configuration file exists
if (!file_exists($configurationPath)) {
  echo '<div class="ui-state-highlight">Configuration file is not present and it will be created after saving</div>';
}
else {
  include_once ($configurationPath);
}

$conf['template']['overrideMenu'] === TRUE ? $enabledChecked = 'checked="checked"' : $enabledChecked = '';
;

echo
<<<EOT
<style type="text/css">
.formText{
  width: 200px;
  float:left;
  padding: 4px;
}

.fieldContainer{
  border-bottom: 1px dashed grey;
}

.formInput{
  padding: 4px;
  margin-left: 200px;
}

</style>
<div style="float:right; width: 300px; padding: 4px;" class="ui-state-highlight">
<b>HELP</b><br/>
Use this module to configure your own menù system. Remember that FabCMS uses suckerfish as default menu.
</div>

<form action="admin.php?module=fabmenu&op=config&save" method="post">
  <input type="hidden" name="dummy" value="dummy"/>
  <div class="fieldContainer">
    <div class="formText">
      </b>Enabled</b><br/>
      If checked default menù will not rendered, but FabCMS will use the HTML you write below.
    </div>
    <div class="formInput">
      <input type="checkbox" name="enabled" value="1" $enabledChecked>
    </div>
    <div style="clear:left"></div>
  </div>

  <div class="fieldContainer">
    <div class="formText">
      </b>Custom menù</b><br/>
    </div>
    <div class="formInput">
      <textarea  style="width:100%; height: 115px" name="customMenu">{$conf['template']['customMenu']}</textarea>
    </div>
    <div style="clear:left"></div>
  </div>

  <button type="submit">Save</button>
</form>
EOT;
