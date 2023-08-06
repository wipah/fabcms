<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 20/07/2018
 * Time: 11:45
 */
if (!$core->adminBootCheck())
    die("Check not passed");

if (isset($_GET['uploadLogo'])) {
    $this->noTemplateParse = true;

    if (!isset($_FILES)) {
        echo 'No file was sent';

        return;
    }

    $fileInfo = pathinfo($_FILES['file']['name']);
    $extension = htmlentities(strtolower($fileInfo['extension']));
    $basename = htmlentities(strtolower($fileInfo['basename']));

    if (!in_array($extension, ['png', 'jpeg', 'jpg'])) {
        echo '<div class="alert alert-secondary" role="alert">
                <strong>Extension not allowed</strong>! Extension is not allowed. Please upload only png, jpg or jpeg images.
              </div>';

        return;
    }

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $conf['path']['baseDir'] . 'templates/logo.' . $extension)) {
        echo '<div class="alert alert-secondary" role="alert">
                Unable to move the file!
              </div>';

        return;
    }

    echo '<div class="alert alert-success" role="alert">
            <strong>Logo updated</strong>! Logo was updated.
          </div>
          <img class="img-fluid" alt="logo" src="' . $URI->getBaseUri(true) . 'templates/logo.' . $extension . '">';

    return;
}


if (isset($_GET['getTemplateVariants'])) {
    $this->noTemplateParse = true;

    $templateUsed = $_POST['templateUsed'];
    echo ' <select id="templateVariant" name="templateVariant" class="form-control">';

    foreach (glob($conf['path']['baseDir'] . 'templates/' . $templateUsed . '/css/*') AS $singleTemplateVariant) {
        $singleTemplateVariant = basename($singleTemplateVariant);
        echo '<option value="' . $singleTemplateVariant . '">' . $singleTemplateVariant . '</option>';
    }

    echo '</select>';

    return;
}
if (isset($_GET['save'])) {

    $this->noTemplateParse = true;

    /*
     * Admin
     */
    $core->deleteConfig('admin');
    $adminHomepageModules = $_POST['adminHomepageModulePosition'];
    $core->addConfig(['module' => 'admin', 'param' => 'adminHomepageModulePosition', 'extended_value' => $adminHomepageModules]);

    /*
    * Template
    */
    $core->deleteConfig('template');
    $templateUsed       = $_POST['templateUsed'];
    $templateVariant    = $_POST['templateVariant'];
    $templateHead       = $_POST['templateHead'];
    $templateBeforeBody = $_POST['templateBeforeBody'];
    $templateScripts    = $_POST['templateScripts'];
    $template404        = $_POST['template404'];

    $core->addConfig(['module' => 'template', 'param' => 'template', 'value' => $templateUsed]);
    $core->addConfig(['module' => 'template', 'param' => 'templateVariant', 'value' => $templateVariant]);
    $core->addConfig(['module' => 'template', 'param' => 'templateHead', 'extended_value' => $templateHead]);
    $core->addConfig(['module' => 'template', 'param' => 'templateBeforeBody', 'extended_value' => $templateBeforeBody]);
    $core->addConfig(['module' => 'template', 'param' => 'templateScripts', 'extended_value' => $templateScripts]);
    $core->addConfig(['module' => 'template', 'param' => 'template404', 'extended_value' => $template404]);

    /*
     * Email
     */

    $method = (int) $_POST['method'];
    $smtp   = str_replace('\'', '', $_POST['smtp']);
    $port   = (int) ($_POST['port']);

    $username = str_replace('\'', '', $_POST['username']);
    $password = str_replace('\'', '', $_POST['password']);
    $auth = str_replace('\'', '', $_POST['auth']);
    $defaultEmail = str_replace('\'', '', $_POST['defaultEmail']);
    $defaultEmailName = str_replace('\'', '', $_POST['defaultEmailName']);
    $replyEmail = str_replace('\'', '', $_POST['replyEmail']);
    $replyEmailName = str_replace('\'', '', $_POST['replyEmailName']);

    (int) $_POST['enableEmail'] === 1 ? $enableEmail = 1 : $enableEmail = 0;
    (int) $_POST['bypassPeerVerify'] === 1 ? $bypassPeerVerify = 1 : $bypassPeerVerify = 0;

    $core->deleteConfig('email');
    $core->addConfig(['module' => 'email', 'param' => 'method', 'value' => $method]);
    $core->addConfig(['module' => 'email', 'param' => 'smtp', 'value' => $smtp]);
    $core->addConfig(['module' => 'email', 'param' => 'port', 'value' => $port]);
    $core->addConfig(['module' => 'email', 'param' => 'username', 'value' => $username]);
    $core->addConfig(['module' => 'email', 'param' => 'password', 'value' => $password]);
    $core->addConfig(['module' => 'email', 'param' => 'auth', 'value' => $auth]);
    $core->addConfig(['module' => 'email', 'param' => 'defaultEmail', 'value' => $defaultEmail]);
    $core->addConfig(['module' => 'email', 'param' => 'defaultEmailName', 'value' => $defaultEmailName]);
    $core->addConfig(['module' => 'email', 'param' => 'replyEmail', 'value' => $replyEmail]);
    $core->addConfig(['module' => 'email', 'param' => 'replyEmailName', 'value' => $replyEmailName]);
    $core->addConfig(['module' => 'email', 'param' => 'enableEmail', 'value' => $enableEmail]);
    $core->addConfig(['module' => 'email', 'param' => 'bypassPeerVerify', 'value' => $bypassPeerVerify]);

    /*
     * Contacts
     */
    $core->deleteConfig('contacts');
    $contactsWhatsapp = $_POST['contactsWhatsapp'];
    $contactsFacebook = $_POST['contactsFacebook'];
    $contactsTelegram = $_POST['contactsTelegram'];
    $contactsTelegramBroadcast = $_POST['contactsTelegramBroadcast'];

    $core->addConfig(['module' => 'contacts', 'param' => 'whatsapp', 'value' => $contactsWhatsapp]);
    $core->addConfig(['module' => 'contacts', 'param' => 'facebook', 'value' => $contactsFacebook]);
    $core->addConfig(['module' => 'contacts', 'param' => 'telegram', 'value' => $contactsTelegram]);
    $core->addConfig(['module' => 'contacts', 'param' => 'telegramBroadcast', 'value' => $contactsTelegramBroadcast]);

    /*
     * Core
     */

    $recaptchaEnabled   = (int) $_POST['recaptchaEnabled'];
    $recaptchaPublic    = $_POST['recaptchaPublic'];
    $recaptchaPrivate   = $_POST['recaptchaPrivate'];
    $dateFormat         = $_POST['dateFormat'];
    $dateTimeFormat     = $_POST['dateTimeFormat'];
    $defaultModule      = $_POST['defaultModule'];

    $core->deleteConfig('core');
    $core->addConfig(['module' => 'core', 'param' => 'recaptchaEnabled', 'value' => $recaptchaEnabled]);
    $core->addConfig(['module' => 'core', 'param' => 'recaptchaPublic', 'value' => $recaptchaPublic]);
    $core->addConfig(['module' => 'core', 'param' => 'recaptchaPrivate', 'value' => $recaptchaPrivate]);
    $core->addConfig(['module' => 'core', 'param' => 'dateFormat', 'value' => $dateFormat]);
    $core->addConfig(['module' => 'core', 'param' => 'dateTimeFormat', 'value' => $dateTimeFormat]);
    $core->addConfig(['module' => 'core', 'param' => 'defaultModule', 'value' => $defaultModule]);

    /* Media */
    $mediaDefaultPageTitle = $core->in($_POST['mediaDefaultPageTitle']);
    $mediaSeoDescription   = $core->in($_POST['mediaSeoDescription']);

    $core->deleteConfig('media');
    $core->addConfig(['module' => 'media', 'param' => 'mediaDefaultPageTitle', 'value' => $mediaDefaultPageTitle]);
    $core->addConfig(['module' => 'media', 'param' => 'mediaSeoDescription', 'extended_value' => $mediaSeoDescription]);

    echo '<div class="alert alert-success" role="alert">
                <strong>Configuration saved</strong>. Configuration was saved successefully!
          </div>';

    return;
}

// Admin
$adminHomepageModulePosition = $core->getConfig('admin', 'adminHomepageModulePosition', 'extended_value');
$dirs = '';
foreach (glob( $conf['path']['baseDir'] . 'admin/modules/*' ) AS $singleDir ){
    $dirs .= basename($singleDir) . ' ';
}
$tabAdmin = '<div class="form-group row">
                <label class="col-md-4 control-label" for="adminHomepageModulePosition">Admin quick icons (separeted with pipe |)</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="adminHomepageModulePosition" id="adminHomepageModulePosition" placeholder="wiki|reconfig|" value="' . $adminHomepageModulePosition . '">
                    <small>' . $dirs . '</small>
                    </div>
                    
            </div>';

// Core
$dateFormat = $core->getConfig('core', 'dateFormat');
$tabCore = '
            <div class="form-group row">
                <label class="col-md-4 control-label" for="dateFormat">Date format</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="dateFormat" id="dateFormat" placeholder="dateFormat" value="' . $dateFormat . '">
                    </div>  
            </div>
';

$dateTimeFormat = $core->getConfig('core', 'dateTimeFormat');
$tabCore .= '
            <div class="form-group row">
                <label class="col-md-4 control-label" for="dateTimeFormat">DateTime format</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="dateTimeFormat" id="dateTimeFormat" placeholder="dateFormat" value="' . $dateTimeFormat . '">
                    </div>  
            </div>
            
            <hr/>
            <div class="form-group row">
                <label class="col-md-4 control-label" for="dateTimeFormat">Default module</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="defaultModule" id="defaultModule" placeholder="Default module" value="' . $core->getConfig('core','defaultModule') . '">
                    </div>  
            </div>
            
';

// Template
$templateUsed       = $core->getConfig('template', 'template');
$templateVariant    = $core->getConfig('template', 'templateVariant') ?? 'default.css';
$templateHead       = $core->getConfig('template', 'templateHead' ,'extended_value');
$templateBeforeBody = $core->getConfig('template', 'templateBeforeBody' ,'extended_value');
$templateScripts    = $core->getConfig('template', 'templateScripts' ,'extended_value');
$template404        = $core->getConfig('template', 'template404' ,'extended_value');

$tabTemplate = '
            <div class="form-group row">
                <label class="col-md-4 control-label" for="method">Template</label>
                    <div class="col-md-4">
                        <select onchange="selectSubTemplate();" id="templateUsed" name="templateUsed" class="form-control">';


foreach (glob($conf['path']['baseDir'] . 'templates/*', GLOB_ONLYDIR) AS $singleTemplate) {
    $singleTemplate = basename($singleTemplate);
    $tabTemplate .= '<option ' . ($templateUsed === $singleTemplate ? 'selected' : '') . ' value="' . $singleTemplate . '">' . $singleTemplate . '</option>';
}

$tabTemplate .= '
                        </select>
                    </div>
            </div>';


$tabTemplate .= '
            <div class="form-group row">
                <label class="col-md-4 control-label" for="method">Template variant</label>
                    <div class="col-md-4" id="templateVariantContainer">
                        <select id="templateVariant" name="templateVariant" class="form-control">';


foreach (glob($conf['path']['baseDir'] . 'templates/' . $templateUsed . '/css/*') AS $singleTemplateVariant) {
    $singleTemplateVariant = basename($singleTemplateVariant);
    $tabTemplate .= '<option ' . ($templateVariant === $singleTemplateVariant ? 'selected' : '') . ' value="' . $singleTemplateVariant . '">' . $singleTemplateVariant . '</option>';
}

$tabTemplate .= '
                        </select>
                    </div>
            </div>
            
            
            <div class="form-group row">
                <label class="col-md-4 control-label" for="logoUpload">Header code</label>
                    <div class="col-md-8">
                        <textarea 
                            class="form-control" name="templateHead" id="templateHead" placeholder="Header code">' . $templateHead . '</textarea>
                        
                    </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 control-label" for="logoUpload">Before closing body tag</label>
                    <div class="col-md-8">
                        <textarea 
                            class="form-control" name="templateBeforeBody" id="templateBeforeBody" placeholder="Header code">' . $templateBeforeBody . '</textarea>
                        
                    </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 control-label" for="logoUpload">Scripts (loaded lastly)</label>
                    <div class="col-md-8">
                        <textarea 
                            class="form-control" name="templateScripts" id="templateScripts" placeholder="Header code">' . $templateScripts . '</textarea>
                            <small>Don\'t use &lt;script&gt;. Those tags will be automatically added.</small>
                    </div>
            </div>
            
            
            <div class="form-group row">
                <label class="col-md-4 control-label" for="logoUpload">Template head</label>
                    <div class="col-md-4">
                        <input type="file" class="form-control" name="logoUpload" id="logoUpload" placeholder="Logo upload">
                        <button id="btnUpload" onclick="uploadLogo();" class="btn btn-primary form-control">Upload</button>
                    </div>  
                    <div class="col-md-4" id="currentLogo">Logo</div>
            </div>
            
            <div class="form-group row">
                <label class="col-md-4 control-label" for="logoUpload">404 custom code</label>
                    <div class="col-md-4">
                        <textarea 
                            class="form-control" name="template404" id="template404" placeholder="Header code">' . $template404 . '</textarea>
                        
                    </div>
            </div>';

switch ($core->getConfig('email', 'auth')) {
    case 'ssl':
        $selectssl = 'selected="selected"';
        break;
    case 'tls':
        $selecttls = 'selected="selected"';
        break;
    case 'starttls':
        $selectstarttls = 'selected="selected"';
        break;
    case 'noauth':
    default:
        $selectnoauth = 'selected="selected"';
        break;
}

if ((int) $core->getConfig('email', 'enableEmail') === 1)
    $confenabled = 'checked="checked"';

if ((int) $core->getConfig('email', 'bypassPeerVerify') === 1)
    $bypassPeerVerify = 'checked="checked"';

switch ((int) $core->getConfig('email', 'method')) {
    case 0:
        $methodPhpMail = 'selected';
        break;
    case 1:
        $methodPHPMAiler = 'selected';
        break;
}
$tabEmail = <<<EOT

            <div class="form-group row">
                <label class="col-md-4 control-label" for="method">Method</label>
                    <div class="col-md-4">
                        <select id="method" name="method" class="form-control">
                        <option {$methodPhpMail} value="0">PHP mail()</option>
                        <option {$methodPHPMAiler} value="1">PHPMailer</option>
                        </select>
                    </div>
            </div>
            
            <div class="form-group row">
                <label class="col-md-4 control-label" for="smtp">SMTP</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="smtp" id="smtp" placeholder="SMTP server" value="{$core->getConfig('email', 'smtp')}">
                </div>
            </div>
                
            <div class="form-group row">
                <label class="col-md-4 control-label" for="port">Port (usually 25, 465 or 587)</label>
                    <div class="col-md-4">

                    <input type="text" class="form-control" name="port" id="port" placeholder="Port" value="{$core->getConfig('email', 'port')}">
                </div>
            </div>
        
            <div class="form-group row">
                <label class="col-md-4 control-label" for="username">Username</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="username" id="username" placeholder="SMTP username" value="{$core->getConfig('email', 'username')}">
                </div>
            </div>  
            
            <div class="form-group row">
                <label class="col-md-4 control-label" for="password">Password</label>
                    <div class="col-md-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="SMTP password" value="{$core->getConfig('email', 'password')}">
                    </div>
            </div>
    
            <div class="form-group row">
                <label class="col-md-4 control-label" for="auth">Authentication type (SSL, TLS, and STARTTLS maybe required)</label>
                    <div class="col-md-4">
                        <select name="auth" class="form-control" id="auth">
                            <option $selectssl value="ssl">SSL</option>
                            <option $selecttls value="tls">TLS</option>
                            <option $selectstarttls value="starttls">STARTTLS</option>
                            <option $selectnoauth value="noauth">No Auth (unsafe)</option>
                        </select>
                 
            
                    </div>
            </div>
   
            <hr />

            <div class="form-group row">
                <label class="col-md-4 control-label" for="defaultEmail">Default email</label>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="defaultEmail" name="defaultEmail" placeholder="Default email" value="{$core->getConfig('email', 'defaultEmail')}">
                </div>
            </div>
     
            <div class="form-group row">
                <label class="col-md-4 control-label" for="defaultEmailName">Default name email</label>
                    <div class="col-md-4">
                        <input type="text" 
                               class="form-control"
                               name="defaultEmailName"
                               id="defaultEmailName" 
                               placeholder="Default sender name, such Crisasoft.com sales"
                               value="{$core->getConfig('email', 'defaultEmailName')}">
                    </div>
            </div>
   
            <hr />
   
            <div class="form-group row">
                <label class="col-md-4 control-label" for="replyEmail">Global reply email</label>
                    <div class="col-md-4">
                        <input type="text" 
                               class="form-control" 
                               id="replyEmail"
                               name="replyEmail" 
                               placeholder="Default reply email (noreply@namesite.com)" 
                               value="{$core->getConfig('email', 'replyEmail')}">
                </div>
            </div>
   
            <div class="form-group row">
                <label class="col-md-4 control-label" for="replyEmailName">Reply email name</label>
                <div class="col-md-4">
                    <input type="text" 
                           class="form-control" 
                           id="replyEmailName"
                           name="replyEmailName"
                           placeholder="Default reply email name"
                           value="{$core->getConfig('email', 'replyEmailName')}">
                </div>
            </div>

            <hr />

            <div class="form-group row">
                <label class="col-md-4 control-label" for="enableEmail">Enable email</label>
                <div class="col-md-4">
            
                    <div class="checkbox">
                        <label>
                            <input $confenabled name="enableEmail" id="enableEmail" value="1" type="checkbox">Enable email
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 control-label" for="bypassPeerVerify">Bypass peer verify</label>
                <div class="col-md-4">
            
                    <div class="checkbox">
                        <label>
                            <input $bypassPeerVerify name="bypassPeerVerify" id="bypassPeerVerify" value="1" type="checkbox">Enable email
                        </label>
                    </div>
                </div>
            </div>
EOT;

$tabRecaptcha = '
<!-- Form Name -->
<legend>Security</legend>

<h3>Use recaptcha</h3>

<!-- Select Basic -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="recaptchaEnabled">Use recaptcha</label>
  <div class="col-md-4">
    <select id="recaptchaEnabled" name="recaptchaEnabled" class="form-control">
      <option ' . ((int) $core->getConfig('core', 'recaptchaEnabled') === 0 ? 'selected' : '') . ' value="0">Disabled</option>
      <option ' . ((int) $core->getConfig('core', 'recaptchaEnabled') === 1 ? 'selected' : '') . ' value="1">Enabled</option>
    </select>
  </div>
</div>

<!-- Text input-->
<div class="form-group row">
  <label class="col-md-4 control-label" for="textinput">Public key</label>  
  <div class="col-md-4">
  <input id="recaptchaPublic" name="recaptchaPublic" type="text" value="' . $core->getConfig('core', 'recaptchaPublic') . '" placeholder="Recapcha public" class="form-control input-md">
  <span class="help-block">Public key</span>  
  </div>
</div>

<!-- Text input-->
<div class="form-group row">
  <label class="col-md-4 control-label" for="textinput">Secret key</label>  
  <div class="col-md-4">
  <input id="recaptchaPrivate" name="recaptchaPrivate" type="text" value="' . $core->getConfig('core', 'recaptchaPrivate') . '" placeholder="Recaptcha secret" class="form-control input-md">
  <span class="help-block">Secret key</span>  
  </div>
</div>
';

$tabMedia = '
            <div class="form-group row">
                <label class="col-md-4 control-label" for="mediaDefaultPageTitle">Default title</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="mediaDefaultPageTitle" id="mediaDefaultPageTitle" placeholder="Default title" value="' . $core->getConfig('media', 'mediaDefaultPageTitle') . '">
                    </div>  
            </div>

            <div class="form-group row">
                <label class="col-md-4 control-label" for="mediaSeoDescription">Homepage seo description</label>
                    <div class="col-md-4">
                        <textarea  class="form-control" name="mediaSeoDescription" id="mediaSeoDescription" placeholder="Default SEO description">' . $core->getConfig('media', 'mediaSeoDescription', 'extended_value') . '</textarea>
                    </div>  
            </div>
            
            <hr/>';


$tabContacts = '
<!-- Form Name -->
<legend>Contact us</legend>

<h3>Whatsapp</h3>
<!-- Text input-->
<div class="form-group row">
  <label class="col-md-4 control-label" for="contactsWhatsapp">Whatsapp</label>  
  <div class="col-md-4">
  <input id="contactsWhatsapp" name="contactsWhatsapp" type="text" value="' . $core->getConfig('contacts', 'whatsapp') . '" placeholder="Whatsapp" class="form-control input-md">
  <span class="help-block">Whatsapp</span>  
  </div>
</div>

<div class="form-group row">
  <label class="col-md-4 control-label" for="contactsFacebook">Whatsapp</label>  
  <div class="col-md-4">
  <input id="contactsFacebook" name="contactsFacebook" type="text" value="' . $core->getConfig('contacts', 'facebook') . '" placeholder="facebook" class="form-control input-md">
  <span class="help-block">Facebook</span>  
  </div>
</div>

<div class="form-group row">
  <label class="col-md-4 control-label" for="contactsFacebook">Telegram</label>  
  <div class="col-md-4">
  <input id="contactsTelegram" name="contactsTelegram" type="text" value="' . $core->getConfig('contacts', 'telegram') . '" placeholder="telegram" class="form-control input-md">
  <span class="help-block">Telegram</span>  
  </div>
</div>

<div class="form-group row">
  <label class="col-md-4 control-label" for="contactsTelegramBroadcast">Telegram Broadcast</label>  
  <div class="col-md-4">
  <input id="contactsTelegramBroadcast" name="contactsTelegramBroadcast" type="text" value="' . $core->getConfig('contacts', 'telegramBroadcast') . '" placeholder="telegramBroadcast" class="form-control input-md">
  <span class="help-block">Facebook</span>  
  </div>
</div>
';

echo '
<div class="form-horizontal">
<fieldset>

' . $template->getTabs('reconfig', ['Core', 'Email', 'Recaptcha', 'template', 'contacts', 'media', 'admin'], [$tabCore, $tabEmail, $tabRecaptcha, $tabTemplate, $tabContacts, $tabMedia, $tabAdmin], ['tabType' => 'normal'], []) . '

<!-- Button -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="singlebutton">Operation</label>
  <div class="col-md-4">
    <button id="singlebutton" name="singlebutton" class="btn btn-primary" onclick="update();">Update</button>
  </div>
</div>
</fieldset>
</div>

<div id="updateResult"></div>
<script type="text/javascript">

editorTemplateHead = CodeMirror.fromTextArea( document.getElementById("templateHead"), {
        mode:  "xml",
        autoRefresh:true,
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: "default elegant"
    });


editorTemplateBeforeBody = CodeMirror.fromTextArea( document.getElementById("templateBeforeBody"), {
        mode:  "xml",
        autoRefresh:true,
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: "default elegant"
    });

editorTemplateScripts = CodeMirror.fromTextArea( document.getElementById("templateScripts"), {
        mode:  "javascript",
        autoRefresh:true,
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: "default elegant"
    });

editorTemplate404 = CodeMirror.fromTextArea( document.getElementById("template404"), {
        mode:  "xml",
        autoRefresh: true,
        lineNumbers: true,
        matchBrackets: true,
        matchTags: true,
        theme: "default elegant"
});



$(\'#btnUpload\').on(\'click\', function() {
    var file_data = $(\'#logoUpload\').prop(\'files\')[0];   
    var form_data = new FormData();                  
    form_data.append(\'file\', file_data);
                
    $.ajax({
        url: \'admin.php?module=reconfig&uploadLogo\', 
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        type: \'post\',
        success: function(data){
           $("#currentLogo").html(data); 
        }
     });
});
    
function selectSubTemplate() {
    templateUsed        =   $("#templateUsed").val();
    
    $.post( "admin.php?module=reconfig&getTemplateVariants", {   
                                               templateUsed     : templateUsed,
                                               })
    .done(function( data ) {
        $("#templateVariantContainer").html("Loading");
        $("#templateVariantContainer").html(data);
    });
}

function update() {
    
    adminHomepageModulePosition = $("#adminHomepageModulePosition").val();
    
    dateFormat          =   $("#dateFormat").val();
    dateTimeFormat      =   $("#dateTimeFormat").val();
    defaultModule       =   $("#defaultModule").val();
    
    method              =   $("#method").val();
    smtp                =   $("#smtp").val();
    port                =   $("#port").val();
    username            =   $("#username").val();
    password            =   $("#password").val();
    auth                =   $("#auth").val();
    defaultEmail        =   $("#defaultEmail").val();
    defaultEmailName    =   $("#defaultEmailName").val();
    replyEmail          =   $("#replyEmail").val();
    replyEmailName      =   $("#replyEmailName").val();
    
    $("#enableEmail").is(":checked") ? enableEmail = 1 : enableEmail = 0;
    
    bypassPeerVerify    =   $("#bypassPeerVerify").val();
    
    contactsWhatsapp    =  $("#contactsWhatsapp").val();
    contactsFacebook    =  $("#contactsFacebook").val();
    contactsTelegram    =  $("#contactsTelegram").val();
    contactsTelegramBroadcast    =  $("#contactsTelegramBroadcast").val();
    
    recaptchaEnabled    =  $("#recaptchaEnabled").val();
    recaptchaPublic     =   $("#recaptchaPublic").val();
    recaptchaPrivate    =   $("#recaptchaPrivate").val();
    
    mediaDefaultPageTitle = $("#mediaDefaultPageTitle").val();
    mediaSeoDescription   = $("#mediaSeoDescription").val();
    
    templateUsed        =   $("#templateUsed").val();
    templateVariant     =   $("#templateVariant").val();
    templateHead        =   editorTemplateHead.getValue();
    templateBeforeBody  =   editorTemplateBeforeBody.getValue();
    templateScripts     =   editorTemplateScripts.getValue();
    template404         =   editorTemplate404.getValue();
    
$.post( "admin.php?module=reconfig&save", {    adminHomepageModulePosition  : adminHomepageModulePosition,
                                               dateFormat                   : dateFormat,
                                               dateTimeFormat               : dateTimeFormat, 
                                               defaultModule                : defaultModule, 
                                               method                       : method, 
                                               smtp                         : smtp,
                                               port                         : port,
                                               username                     : username,
                                               password                     : password,
                                               auth                         : auth,
                                               defaultEmail                 : defaultEmail,
                                               defaultEmailName             : defaultEmailName,
                                               replyEmail                   : replyEmail,
                                               replyEmailName               : replyEmailName,
                                               enableEmail                  : enableEmail,
                                               bypassPeerVerify             : bypassPeerVerify,
                                               contactsWhatsapp             : contactsWhatsapp,
                                               contactsFacebook             : contactsFacebook,
                                               contactsTelegram             : contactsTelegram,
                                               contactsTelegramBroadcast    : contactsTelegramBroadcast,
                                               recaptchaEnabled             : recaptchaEnabled,
                                               recaptchaPublic              : recaptchaPublic,
                                               recaptchaPrivate             : recaptchaPrivate,
                                               mediaDefaultPageTitle        : mediaDefaultPageTitle,
                                               mediaSeoDescription          : mediaSeoDescription,
                                               templateUsed                 : templateUsed,
                                               templateVariant              : templateVariant,
                                               templateHead                 : templateHead,
                                               templateBeforeBody           : templateBeforeBody,
                                               template404                  : template404,
                                               templateScripts              : templateScripts
                                               })
    .done(function( data ) {
        $("#updateResult").html(data);
    });
}
</script>';