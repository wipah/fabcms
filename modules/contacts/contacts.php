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

if (!$core->loaded) {
    die('Direct call detected');
}

echo '<h1>' . $language->get('contacts', 'contacts', null) . '</h1>';

$this->addTitleTag(  sprintf($language->get('contacts', 'contactsModuleFor'), $conf['site']['name']) );



if (( !$core->getConfig('email', 'defaultEmail')) ) {

    echo '<div class="ui-state-error">This module is currently offline. Please try again later.</div>';
    if ($user->isAdmin) {
        echo 'ADMIN: $conf[\'email\'][\'siteEmail\'] is not set with config.php file';
    }
    return;
}

// Check if recaptcha should be enabled
if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1 ) {
    $this->addJsFile('https://www.google.com/recaptcha/api.js', true);
    $useRecaptcha = true;
}

if (isset($_GET['send'])) {
    $template->moduleH1 = '<h1 class="FabCMSH1">' . $language->get('contacts', 'contacts') . '</h1>';

    if ($_SESSION['email']['sent'] >= 5) {
        echo 'Dear user, you already sent five emails. Please try again later.';
        return;
    }
    // Simply hash check
    if ($_GET['hash'] !== $_POST['hash']) {
        echo 'No way';
        return;
    }

    if(!$core->reCaptchaValidateCode('curl')) {
        echo $language->get('contacts', 'contactsCrudRecaptchaIsNotValid', null);
        return;
    }

    if (false === filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo '<div class="ui-state-error">Invalid email</div>';
        $errors = true;
    } else {
        $email = htmlentities($_POST['email']);
    }

    if (!isset($_POST['subject']) || strlen($_POST['subject']) < 1) {
        $subject = 'No subject';
    } else {
        $subject = htmlentities($_POST['subject']);
    }

    if (!isset($_POST['message']) || strlen($_POST['message']) < 1) {
        echo '<div class="ui-state-error">Invalid message</div>';
        $errors = true;
    } else {
        $message = strip_tags($_POST['message']);
    }

    if (!$errors) {

        if (!isset($_SESSION['email']['sent'])) {
            $_SESSION['email']['sent'] = 1;
        } else {
            $_SESSION['email']['sent']++;
        }

        $fabmail->addFrom($core->getConfig('email','replyEmail'));
        $fabmail->addReply($email, $email);
        $fabmail->addSubject($subject);
        $fabmail->addTo($core->getConfig('email', 'defaultEmail'), $core->getConfig('email', 'defaultEmailName'));
        $fabmail->addMessage($message);

        if (!$fabmail->sendEmail()) {
            $relog->write(['type'      => '4',
                           'module'    => 'CONTACTS',
                           'operation' => 'contacts_send_email_error',
                           'details'   => 'Unable to send the email. Email: ' . $email . ', subject: ' . $subject . ', message: ' . $message,
            ]);
            echo "{$language->get('contacts', 'sendMailKoEmailError')}";
        } else {
            $relog->write(['type'      => '1',
                           'module'    => 'CONTACTS',
                           'operation' => 'contacts_send_email_ok',
                           'details'   => 'Unable to send the email. Error: ' . $fabmail->lastError  . ' , email: ' . $email . ', subject: ' . $subject . ', message: ' . $message,
            ]);

            echo "<h2>{$language->get('contacts','sendMailOkEmailSent')}</h2>
                 {$language->get('contacts','sendMailOkEmailSentDescription')}
                 ";
        }
        return;
    }
}

$randHash = md5(microtime(true));

$template->moduleH1 = '<h1 class="FabCMSH1">' . $language->get('contacts', 'contacts') . '</h1>';

$template->navBarAddItem($language->get('contacts', 'contacts'), $URI->getBaseUri() . $this->routed);
echo '
<div class="row">
    <div class="col-md-7">
    
    <div id="' . $randHash . '">
    <script type="text/javascript">
        hash = \'$randHash&send\';
        dummy = \'\';
        dummy1 = \'form method=post action=sendmailfk.php\';
        dummy2 = \'action="spbt_sens";\';
        dummy2 = \'\';
    </script>

    <!--FabCMS-hook:contactsBeforeForm-->

    <form method="post" action="' .$URI->getBaseUri() . 'contacts/?hash=' . $randHash . '&send" class="form-horizontal" role="form">
        <input type="hidden" name="hash" id="hash" value="' . $randHash . '" />

        <div class="form-group">
          <label class="control-label col-md-4" for="email">' . $language->get('contacts', 'subject') . ':</label>
          <div class="col-md-8">
            <input type="text" name="subject" value="' . $subject . '"class="form-control" id="email" placeholder="' . $language->get('contacts', 'subject') . '">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-md-4" for="email">' . $language->get('contacts', 'email') . ':</label>
          <div class="col-md-8">
            <input type="text" name="email" value="' . $email . '"class="form-control" id="email" placeholder="' . $email . '">
          </div>
        </div>

        <div class="form-group">
          <label class="control-label col-md-4" for="email">' . $language->get('contacts', 'message') . ':</label>
          <div class="col-md-8">
            <textarea style="width:100%; height: 220px;" name="message"></textarea>
          </div>
        </div>';

if ( $useRecaptcha) {

    echo '
    <div class="form-group">
        <label class="control-label col-sm-4" for="g-recaptcha">' . $language->get('contacts', 'contactsRecaptchaCode') . '</label>
        <div class="col-sm-8">
            <div class="g-recaptcha" data-sitekey="' . (  $core->getConfig( 'core', 'recaptchaPublic'))  . '"></div>
        </div>
    </div>';

}

echo '
        <div class="form-group">
          <div class="col-md-offset-2 col-md-8">
            <button type="submit" class="btn btn-default">' . $language->get('contacts', 'send') . '</button>
          </div>
        </div>
    </form>
    <!--FabCMS-hook:contactsAfterForm-->
</div>
 
    </div>
    <div class="col-md-5">
    ' . ( !empty($core->getConfig('contacts','whatsapp')) ? 'Whatsapp: <a href="https://wa.me/' . $core->getConfig('contacts','whatsapp')  .'">' . $core->getConfig('contacts','whatsapp')   .'</a><br/>' : '' ) . '
    ' . ( !empty($core->getConfig('contacts','telegram')) ? 'Telegram: <a href="https://tm.me/' . $core->getConfig('contacts','telegram')  .'">' . $core->getConfig('contacts','telegram')   .'</a><br/>' : '' ) . '
    ' . ( !empty($core->getConfig('contacts','telegramBroadcast')) ? 'Telegram broadcast: <a href="https://tm.me/' . $core->getConfig('contacts','telegramBroadcast')  .'">' . $core->getConfig('contacts','telegram')   .'</a><br/>' : '' ) . '
    ' . ( !empty($core->getConfig('contacts','facebook')) ? 'Facebook: <a href="' . $core->getConfig('contacts','facebook') . '">' . $core->getConfig('contacts','facebook') : '</a><br/> ' ) . '
    </div>

';