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
if (!$core->loaded) die('Direct call detected');

// $this->addCSSLink('style');

if ( (int) $core->getConfig( 'user', 'registrationEnabled') === false){
  echo $language->get('user', 'msgRegistrationDisabled');
}

$this->addJsFile('https://www.google.com/recaptcha/api.js', true);

$template->navBarAddItem($language->get('user', 'userManagement'), $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($language->get('user', 'registerNewUser'));

if ($user->logged) {
    $theLink        = $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/logout/';
    $thePanelLink   = $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/cp/';
    echo '<div class="bg-warning">' . sprintf($language->get('user', 'userIsAlreadyLoggedPleaseLogout'), $theLink, $thePanelLink) . '</div>';
    return;
}


$this->addTitleTag(  sprintf($language->get('user', 'registerTitle'), $conf['site']['name']) );


switch ($path[3]) {
    case 'save':
        include 'op_register_save.php';
}

$rulesText      =   file_get_contents(__DIR__ . '/static/rules.html');
$privacyText    =   file_get_contents(__DIR__ . '/static/privacy.html');

echo '<h1>' . $language->get('user', 'registerUserForm') . '</h1>
<!--FabCMS-hook:beforeRegistrationForm-->
<style type="text/css">
    .notRead{
        border-left: 6px solid #ff4d54;
     
    }
    .read{
    border-left: 6px solid #65ff7c;
        
    }
</style>
<form id="userRegisterForm" method="post" class="form-horizontal" role="form" action="' . $URI->getBaseUri() . 'user/register/save/">

  <div class="form-group">
    <label class="control-label col-sm-2" for="name">' . $language->get('user', 'name') . '</label>
    <div id="groupName" class="col-sm-10">
      <input value="' . htmlentities($_POST['name']) . '" type="text" class="form-control" id="name" name="name" placeholder="' . $language->get('user', 'name') . '">
    </div>
  </div>
  
  <div class="form-group">
    <label class="control-label col-sm-2" for="email">' . $language->get('user', 'surname') . '</label>
    <div id="groupSurname" class="col-sm-10">
      <input value="' . htmlentities($_POST['surname']) . '" type="text" class="form-control" id="surname" name="surname" placeholder="' . $language->get('user', 'surname') . '">
    </div>
  </div>
  
  <div class="form-group">
    <label class="control-label col-sm-2" for="email">' . $language->get('user', 'username') . '</label>
    <div id="groupUsername" class="col-sm-10">
      <input value="' . htmlentities($_POST['username']) . '" type="text" class="form-control" id="username" name="username" placeholder="' . $language->get('user', 'username') . '">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2" for="password">' . $language->get('user', 'password') . ':</label>
    <div id="groupPassword" class="col-sm-10">
      <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2" for="passwordVerify">' . $language->get('user', 'repeatPassword') . ':</label>
    <div id="groupPasswordVerify" class="col-sm-10">
      <input type="password" class="form-control" id="passwordVerify" name="passwordVerify" placeholder="Retype password">
    </div>
  </div>

  <div class="form-group">
    <label class="control-label col-sm-2" for="email">' . $language->get('user', 'email') . '</label>
    <div class="col-sm-10">
      <input value="' . htmlentities($_POST['email']) . '" type="email" class="form-control" id="email" name="email" placeholder="' . $language->get('user', 'email') . '">
    </div>
  </div>
  
    <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <div class="checkbox">
            <input type="checkbox" value="true"' . ($_POST['checkbox'] === 'true' ? 'checked="checked"' : '') . ' name="checkbox" id="checkbox">
            <label>
        ' . $language->get('user', 'userNewsletter') . '
        </label>
      </div>
    </div>
  </div>

<div class="form-group"><a name="rulesLink"></a>
  <label class="col-md-2 control-label" for="textarea">Regolamento</label>
  <div class="col-md-10">                     
    <div style="overflow-y: scroll; height: 100px;" class="form-control read" id="terms" name="terms">
' . $rulesText . '
    </div>
  </div>
</div>


<div class="form-group"><a name="privacyLink"></a>
  <label class="col-md-2 control-label" for="textarea">Trattamento dei dati</label>
  <div class="col-md-10">                     
    <div style="overflow-y: scroll; height: 350px;" class="form-control read" id="privacy" name="privacy">
' . $privacyText . '
    </div>
  </div>
</div>

';

if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1 ){
    echo '
    <div class="form-group">
        <label class="control-label col-sm-2" for="g-recaptcha">' . $language->get('user', 'captcha') . '</label>
        <div class="col-sm-10">
            <div class="g-recaptcha" data-sitekey="' . $core->getConfig( 'core', 'recaptchaPublic')  . '"></div>
        </div>
    </div>';
} else {
    die();
}

echo '


  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      ' . $language->get('user', 'registerScrollDownPrivacyAndRules') .' <br/>
      <button type="button" onclick="checkForm();" id="registerButtonSubmit" class="float-right btn btn-primary">Registrati</button>
    </div>
  </div>
</form>
<!--FabCMS-hook:afterRegistrationForm-->';

$theScript = '
var privacy = false;
var terms = false;

function checkPT(){
    if (privacy && terms) {
    
        $(\'#registerButtonSubmit\').removeAttr(\'disabled\');
   }
}

$(\'#terms\').scroll(function () {
    if ($(this).scrollTop() + $(this).innerHeight() +2 >= $(this)[0].scrollHeight) {
        terms = true;
        $(this).removeClass("notRead").addClass("read");
        checkPT();
    }
});

$(\'#privacy\').scroll(function () {
    if ($(this).scrollTop() + $(this).innerHeight() +2 >= $(this)[0].scrollHeight) {
        privacy = true;
        $(this).removeClass("notRead").addClass("read");
        checkPT();
    }
});


$("#name").blur(function()
{
    if( !$(this).val() ) {
          $("#groupName").addClass("has-error");
    } else {
        $("#groupName").removeClass("has-error");
    }
});

$("#surname").blur(function()
{
    if( !$(this).val() ) {
        $("#groupSurname").addClass("has-error");
    }else{
        $("#groupSurname").removeClass("has-error");
    }
});

function checkForm(){
    var error = false;
    if( !$("#name").val() || !$("#surname").val() )
    	error = "' . $language->get('user', 'userRegisterMissingNameSurname') . '";
    	
    if ($("#passwordVerify").val() === $("#password").val()) {
        if ( $("#passwordVerify").val().length <= ' . $conf['security']['minimumPasswordLenght'] . ' ){
            $("#groupUsername").addClass("has-error");
            error += ("' . sprintf($language->get('user', 'passwordIsTooShort'), $conf['security']['minimumPasswordLenght'] ) . '");    
        }
        
      
    } else {
        $("#groupPassword,#groupPasswordVerify").addClass("has-error");
        error += ("' . $language->get('user', 'passwordMismatch') . '");
        
    }
    
    if (error == false){
        $("#userRegisterForm").submit();
    } else {
        alert (error);
    }
}
';

$this->addScript($theScript);