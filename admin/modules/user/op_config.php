<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBar[] = '<a href="admin.php?module=user">User</a>';
$template->navBar[] = '<a href="admin.php?module=user&op=config">Configuration</a>';

// Count number of users
$query = 'SELECT count(ID) as total from ' . $db->prefix . 'users';

$result = $db->query($query);
$row = mysqli_fetch_assoc($result);
$template->sidebar .= $template->simpleBlock('Statistic', 'Total user: ' . $row['total']);

$this->addJsFile("https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.8.0/tinymce.min.js", true);
$this->addJsFile("https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.8.0/plugins/code/plugin.min.js", true);

// We have to save?
if (isset($_GET['save'])) {
    if (!isset($_POST['dummy'])) {
        echo 'Reload detected';

        return;
    }

    if (!isset($_GET['lang'])){
        echo 'Language not passed.';
        return;
    }

    $lang = $core->in($_GET['lang'], true);

    $optinEmail = str_replace("'", "\'", $_POST['optinEmail']);

    if (false === strpos($optinEmail, '--THELINK--')){
        echo '--THELINK-- Is missing';
        return;
    }

    $core->deleteConfig('user', $lang);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'registrationEnabled',
                      'value' => (int) $_POST['enabled']
                     ]);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'loginEnabled',
                      'value' => (int) $_POST['loginEnabled']
                     ]);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'allowPublicProfiles',
                      'value' => (int) $_POST['allowPublicProfiles']
    ]);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'optinEmail',
                      'extended_value' => $optinEmail
    ]);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'minimumPasswordLenght',
                      'value' => (int) $_POST['minimumPasswordLenght']
    ]);

    $core->addConfig(['module' => 'user',
                      'lang' => $lang,
                      'param' => 'registeredGroup',
                      'value' => (int) $_POST['registeredGroup']
    ]);


    $core->loadConfig();

    echo '<div class="alert alert-success">
                    The configuration was updated.
          </div>';
}


if (!isset($_GET['lang'])){
    echo 'Please, select a language . <br/>';

    foreach ($conf['langAllowed'] as $lang){
        echo '&bull; <a href="admin.php?module=user&op=config&lang=' . $lang . '">' . $lang . '<br/>';
    }

    return;
}
$lang = $core->in ($_GET['lang'], true);

// Get groups
$query = 'SELECT * 
          FROM ' . $db->prefix . 'users_groups ';

if (!$result = $db->query($query)){
    echo 'Query error: ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'Please configure groups first.' . $query;
    return;
}

$registedGroup = (int) $core->getDbConfig('user', 'registeredGroup', $lang);
$registeredOptionGroup = '';



while ($row = mysqli_fetch_assoc($result)){
    switch ( (int) $row['group_type']){
        case 1:
            $type = 'admin';
            $bgColor = '#F00';
            $color = '#FFF';
            break;
        case 2:
            $type = 'registered';
            $bgColor = '#FFF';
            $color = '#000';
            break;
        case 3:
            $type = 'guest';
            $bgColor = '#FFF';
            $color = '#000';
            break;
        default:
            $type = 'N/A';
            $bgColor = '#0FF';
            $color = '#FFF';
            break;
    }

    $registeredOptionGroup .= '<option style="color: '. $color.'; background-color: ' . $bgColor .' " ' . ( (int) $row['ID'] === $registedGroup ? 'selected' : '') . ' value="' . $row['ID'] . '">' . $row['group_name'] . ' (' . $type . ')</option>';
}




echo '

<h2>Configure user module</h2>

<form method="post" action="admin.php?module=user&op=config&save&lang=' . $lang . '" class="form-horizontal">
<fieldset>
<input type="hidden" name="dummy" id="dummy" />
<!-- Form Name -->
<legend>General configuration</legend>

<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="selectbasic">Registered enabled</label>
      <div class="col-md-4">
        <select id="enabled" name="enabled" class="form-control form-control-sm">
          <option ' .  ( (int) $core->getDbConfig('user', 'registrationEnabled', $lang) === 0 ? 'selected' : '' ) . ' value="0">Disabled</option>
          <option ' .  ( (int) $core->getDbConfig('user', 'registrationEnabled', $lang) === 1 ? 'selected' : '' ) . ' value="1">Enabled</option>
        </select>
      </div>
    </div>
</div>


<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="selectbasic">Login enabled</label>
      <div class="col-md-4">
        <select id="loginEnabled" name="loginEnabled" class="form-control form-control-sm">
          <option ' .  ( (int) $core->getDbConfig('user', 'loginEnabled', $lang) === 0 ? 'selected' : '' ) . ' value="0">Login disabled</option>
          <option ' .  ( (int) $core->getDbConfig('user', 'loginEnabled', $lang) === 1 ? 'selected' : '' ) . ' value="1">Login enabled</option>
        </select>
      </div>
  </div>
</div>


<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="selectbasic">Minimum password lenght</label>
      <div class="col-md-4">
        <input type="text" class="form-control form-control-sm" id="minimumPasswordLenght" name="minimumPasswordLenght" value="' . $core->getDbConfig('user', 'minimumPasswordLenght', $lang) .'" />
      </div>
    </div>
</div>


<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="selectbasic">Allow public profiles</label>
      <div class="col-md-4">
        <select id="allowPublicProfiles" name="allowPublicProfiles" class="form-control form-control-sm">
          <option ' .  ( (int) $core->getDbConfig('user', 'allowPublicProfiles', $lang) === 0 ? 'selected' : '' ) . ' value="0">Public profiles disabled</option>
          <option ' .  ( (int) $core->getDbConfig('user', 'allowPublicProfiles', $lang) === 1 ? 'selected' : '' ) . ' value="1">Public profiles enabled</option>
        </select>
      </div>
    </div>
</div>



<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="selectbasic">Group of registered users</label>
      <div class="col-md-4">
        <select id="registeredGroup" name="registeredGroup" class="form-control form-control-sm">
            ' . $registeredOptionGroup . '
        </select>
      </div>
    </div>
</div>


<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="optinEmail">Optin email</label>
      <div class="col-md-8">
      --THELINK-- | --USERNAME-- | --SITENAME--
        <textarea class="form-control" name="optinEmail" id="optinEmail">' . $core->getDbConfig('user', 'optinEmail', $lang,'extended_value') . '</textarea>
      </div>
   </div>
</div>

<div class="form-group">
   <div class="row">
      <label class="col-md-4 control-label" for="singlebutton">Operations</label>
      <div class="col-md-4">
        <button type="submit" id="singlebutton" name="singlebutton" class="btn btn-primary">Save</button>
      </div>
    </div>
</div>

</fieldset>
</form>

<script type="text/javascript">
tinymce.init({
  selector: \'textarea\',
  theme: \'modern\',
  plugins: \'code print preview searchreplace autolink visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount  imagetools contextmenu colorpicker textpattern help\',
  toolbar1: \'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat\',
  image_advtab: true,
  templates: [
    { title: \'Test template 1\', content: \'Test 1\' },
    { title: \'Test template 2\', content: \'Test 2\' }
  ],
  content_css: [
    \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
    \'//www.tinymce.com/css/codepen.min.css\'
  ]
 });
</script>';