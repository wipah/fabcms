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
    die('Direct access not allowed');
}

if (!isset($_POST['name'])) {
    $registerErrors .= $language->get('user', 'nameNotPassed') . '<br/>';
}
$name = $core->in($_POST['name'], true);

if (!isset($_POST['surname'])) {
    $registerErrors .= $language->get('user', 'surnameNotPassed') . '<br/>';
}
$surname = $core->in($_POST['surname'], true);

if (!isset($_POST['username'])) {
    $registerErrors .= $language->get('user', 'usernamelNotPassed') . '<br/>';
}
$username = $core->in($_POST['username'], true);

if (strlen($username) < 3) {
    $registerErrors .= $language->get('user', 'usernameIsTooShort') . '<br/>';
}

if (!isset($_POST['email'])) {
    $registerErrors .= $language->get('user', 'emailNotPassed') . '<br/>';
}
$email = filter_var($core->in($_POST['email'], true), FILTER_SANITIZE_EMAIL);

$_POST['checkbox'] === 'true' ? $newsletter = 1 : $newsletter = 0;

if (!isset($_POST['password'])) {
    $registerErrors .= $language->get('user', 'passwordNotPassed') . '<br/>';
}
$password = $core->in($_POST['password'], true);

if (strlen($password) < 4) {
    $registerErrors .= $language->get('user', 'passwordIsTooShort') . '<br/>';
}

if (!isset($_POST['passwordVerify'])) {
    $registerErrors .= $language->get('user', 'passwordRepeatNotPassed') . '<br/>';
}
$passwordRepeat = $core->in($_POST['passwordVerify'], true);

// Check if username exists
if ($user->checkIfUserExists($username)) {
    $registerErrors .= $language->get('user', 'userAlreadyExist') . '<br/>';
}

// Check if email exists
if ($user->checkIfEmailExists($email)) {
    $registerErrors .= $language->get('user', 'emailAlreadyExist') . '<br/>';
}

// Check for password's length
if (strlen($password) < 4) {
    $registerErrors .= $language->get('user', 'passwordIsTooShort') . '(<i>' . $password . '</i>)' . '<br/>';
}

// Check if passwords match
if ($password !== $passwordRepeat) {
    $registerErrors .= $language->get('user', 'passwordMismatch') . '<br/>';
}

if ( (int) $core->getConfig('core', 'recaptchaEnabled') === 1  ) {

    if (!$core->reCaptchaValidateCode('curl', 'g-recaptcha-response'))
        $registerErrors .= $language->get('user', 'securityCodeNotValid');

}

// If no error is present continue
if (!$registerErrors) {

    $registeredUserGroup = $core->getConfig('user', 'registeredGroup');

    if (!$userResult = $user->addUserToDb($username, $password, $email, $newsletter, $name, $surname, $registeredUserGroup)) {
        echo '<div class="bg-warning">' . $language->get('user', 'errorInsertingUser') . '</div>';

        $relog->write(['type'      => '4',
                       'module'    => 'user',
                       'operation' => 'register_user_db_error',
                       'details'   => 'Unable to store the new user, database failure. LOGIN', 'username: ' . $core->in($username, true) . '; email:' . $email,
        ]);

        return;
    }

    $relog->write(['type'      => '2',
                   'module'    => 'user',
                   'operation' => 'user_registration_to_db_ok',
                   'details'   => 'User registered to the DB: ' . $userResult['ID'],
    ]);

    // Email send
    if (!$user->sendConfirmationEmail( (int) $userResult['ID'], $userResult['hash'])) {

        $relog->write(['type'      => '4',
                       'module'    => 'user',
                       'operation' => 'user_register_error_while_sending_email',
                       'details'   => 'Unable to send the confirmation email. User ID is: ' . $userResult['ID'],
        ]);


        echo '<div class="bg-danger">' . $language->get('user', 'registrationUnableToSendConfirmEmail') . '</div>';
    } else {

        echo '<!--FabCMS-hook:beforeRegistrationConfirmOk-->
              <div class="bg-success">' . $language->get('user', 'registrationConfirmationEmailSent') . '</div>
              <!--FabCMS-hook:afterRegistrationConfirmOk-->';

        $relog->write(['type'      => '2',
                       'module'    => 'user',
                       'operation' => 'user_optin_email_send',
                       'details'   => 'Full Name: ' . $surname . ' ' . $name . ', username:' . $core->in($username, true) . '; email:' . $emailAddress,
        ]);

        /*************
         * Connector *
         ************/
        $connector->callHandler('user_created', array('name' => $name, 'surname' => $surname, 'email' => $email, 'password' => $password));
    }


    $relog->write(['type'      => '2',
                   'module'    => 'USER',
                   'operation' => 'user_register_ok',
                   'details'   => 'New user is registered. : ' . $email,
    ]);

    return;
} else {
    $theLink = $URI->getBaseUri() . $core->router->getRewriteAlias('contacts') . '/';

    $log->write('register_user_data_error', 'Unable to store the new user, errors detected. ' . $core->in($registerErrors));

    $relog->write(['type'      => '3',
                   'module'    => 'USER',
                   'operation' => 'user_register_error',
                   'details'   => 'Unable to register a new user. : ' . $registerErrors,
    ]);

    echo '
    <!--FabCMS-hook:beforeRegistrationConfirmError-->
    <div class="bg-warning" style="padding:4px;">' . $registerErrors . '<br/>
    ' . sprintf($language->get('user', 'registerSaveNewContactLink'), $theLink) . '
    </div>
    <!--FabCMS-hook:afterRegistrationConfirmError-->';
}