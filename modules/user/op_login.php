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

$email = $core->in($_POST['email']);
$password = $core->in($_POST['password']);

$this->addTitleTag(  sprintf($language->get('user', 'loginDefaultTitle'), $conf['site']['name']) );

$template->navBarAddItem($language->get('user', 'userManagement'), $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($language->get('user', 'login'));


if ( (int) $core->getConfig('user', 'loginEnabled') !== 1  ) {
    echo 'Login is disabled.';
    return;
}

// Check if the security question was the right one
$securityQuestion = $_POST['securityQuestion'];
if (md5($conf['security']['siteKey'] . $securityQuestion) !== $_POST['securityQuestionHash']){
    $relog->write(['type'      => '2',
                   'module'    => 'USER',
                   'operation' => 'user_login_',
                   'details'   => 'Error while checking for security question. User email is: ' . $email,
    ]);

    echo 'Mismatch between security question. If you are not a spammer FabCMS sincerely apologize with you.';
    return;
}

// Check the security hash date
if ($_POST['securityHashDate'] !== md5($conf['security']['siteKey'] . date('Y-m-d'))){
    $relog->write(['type'      => '2',
                   'module'    => 'USER',
                   'operation' => 'user_login_hash_date_mismatch',
                   'details'   => 'Error while validate the security hash date. User email is: ' . $email,
    ]);

    echo 'Cannot validate the security hash date';
    return;
}

if (!$user->login($email, $password)) {
    $forgotURL = $URI->getBaseUri() . '/user/reset_password/';
    $resendURL = $URI->getBaseUri() . '/user/resend_email/';

    $relog->write(['type'      => '2',
                   'module'    => 'USER',
                   'operation' => 'user_login_error',
                   'details'   => 'User cannot login. User email is: ' . $email,
    ]);
    echo '<!--FabCMS-hook:beforeLoginFailed-->

    <div class="alert alert-danger">
      <strong>
        
        <p>
            ' . $language->get('user', 'loginLoginErrorShort') . '!</strong> ' . $language->get('user', 'loginLoginError') . '.
        </p>
        
        <p>Help: <br/> 
            &bull;' . sprintf($language->get('user', 'loginLoginErrorForgotPassword', null), $forgotURL) . '
            &bull;' . sprintf($language->get('user', 'loginLoginErrorResendEmail', null), $resendURL) . '
        </p>
        
    </div>

    <!--FabCMS-hook:beforeLoginFailed-->';
} else {
    $relog->write(['type'      => '2',
                   'module'    => 'USER',
                   'operation' => 'user_login_error',
                   'details'   => 'User logged in. User email is: ' . $email,
    ]);

    $connector->callHandler('user_login_ok', array('plainPassword' => $_POST['password']));

    echo '
  <!--FabCMS-hook:beforeLogin-->
    <div style="padding:4px" class="ui-state-highlight">' . $language->get('user', 'loginLoginOk') . '. <a href="' . $_SERVER['HTTP_REFERER'] . '">' . $language->get('user', 'loginClickHereToReturnToYourPage') . '</a></div>
  <!--FabCMS-hook:afterLogin-->';
}