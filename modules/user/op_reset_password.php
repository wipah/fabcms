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

if (!$core->loaded) {
    die('Direct call detected');
}

if ($user->logged) {
    echo 'You are logged!!!';
    return;
}

$template->navBarAddItem($language->get('user', 'user'), $URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($language->get('user', 'userResetPassword'));

$theLink = $URI->getBaseUri() . $core->router->getRewriteAlias('contacts') . '/';

if (  (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1 ){
    $this->addJsFile('https://www.google.com/recaptcha/api.js', true);
    $useRecaptacha = true;
}

echo '<div class="">
        <p>
            <h3>' . $language->get('user', 'userResetPassword') . '</h3>
                ' . sprintf($language->get('user', 'userResetPasswordWelcome'), $theLink) . '
        </p>
    </div>';

switch ($path[3]) {
    case 'sendbymail':
        $log->write('user_reset_password_start', 'user', '');
        if ( (int) $core->getConfig('core', 'recaptchaEnabled') === 1 && $core->reCaptchaValidateCode() === false) {
            echo '<div class="ui-state-error">' . $language->get('user', 'securityCodeNotValid') . '</div>';
            return;
        }

        // Get the email
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (empty($email)) {
            echo '<div class="ui-state-error">No email passed. Aborting</div>'; //todo: language
            return;
        }

        // Check if email exists
        $query = "
        SELECT username
        FROM {$db->prefix}users
        WHERE email = '$email'
        LIMIT 1";

        $db->setQuery($query);

        if (!$db->executeQuery('select')) {
            $log->write('user_reset_password_error', 'user', 'Query error: ' . $query);
            echo '<div class="ui-state-error">We have got an internal error</div>.'; //todo: language
            return;
        }

        if (!$db->numRows) {
            $log->write('user_reset_password_error', 'user', 'Mail not found:' . $email);
            echo '<div class="ui-state-error">The email provided was not found.</div>'; //todo: language
            return;
        }
        $row = $db->getResultAsArray();

        $username = $row['username'];
        $hash = md5(microtime(true) . $conf['security']['siteKey']);

        $query = "
        UPDATE {$db->prefix}users
        SET reset_email_hash = '$hash'
        WHERE email = '$email'
        LIMIT 1;";

        $db->setQuery($query);

        if ($db->executeQuery('update')) {
            if (!$db->numRows) {
                echo '<div class="ui-state-error">' . $language->get('user', 'userResetPassowordEmailNotFound') . '</div>';
                return;
            }
            if (!$user->sendResetPasswordEmail($email, $username, $hash)) {
                $log->write('user_reset_password_send_email_sent_error', 'user', 'Email: ' . $email);
                echo '<div class="ui-state-error">' . $language->get('user', 'userResetPasswordEmailSendingError') . '</div>';
            } else {
                $log->write('user_reset_password_send_email_sent_ok', 'user', 'Email: ' . $email);
                echo '<div class="alert alert-success">' . $language->get('user', 'userResetPasswordEmailSent') . '</div>';
            }
            return;
        } else {
            $log->write('user_reset_password_send_email_sent_error', 'user', 'Email: ' . $email . ', query: ' . $query);
            echo 'Query error. What a shame!';
        }
        break;
    case 'verify':
        if ($path[5] === 'change') {

            if ( (int) $core->getConfig('core', 'recaptchaEnabled') === 1  && $core->reCaptchaValidateCode() === false) {
                echo '<div class="ui-state-error">' . $language->get('user', 'securityCodeNotValid') . '</div>';
                return;
            }

            $password = $_POST['password'];
            $passwordConfirm = $_POST['confirm_password'];

            if ($password !== $passwordConfirm) {
                echo '<div class="ui-state-error">' . $language->get('user', 'resetPasswordPasswordMismatch') . '</div>';
                return;
            }

            if (strlen($password) < 4) {
                echo '<div class="ui-state-error">' . $language->get('user', 'passwordIsTooShort') . '</div>';
                return;
            }

            $password = $user->getPasswordHash($password);
            $hash = $core->in($path[4]);

            $query = "UPDATE {$db->prefix}users 
                      SET password = '$password', 
                          reset_email_hash = '' 
                      WHERE reset_email_hash = '$hash' 
                      LIMIT 1";

            $db->setQuery($query);
            if (!$db->executeQuery('update')) {
                echo 'Query error';

                $relog->write(['type'      => '4',
                               'module'    => 'user',
                               'operation' => 'reset_password_password_change_error',
                               'details'   => 'Email: ' . $email . ', query: ' . $query,
                ]);

                return;
            } else {

                /*************
                 * Connector *
                 ************/
                $connector->callHandler('user_modify_password', array('email' => $email, 'password' => $_POST['password']));

                echo '<div class="ui-state-default">' . $language->get('user', 'resetPasswordPasswordChanged') . '</div>';


                $relog->write(['type'      => '2',
                               'module'    => 'user',
                               'operation' => 'reset_password_password_change_ok',
                               'details'   => 'Email: ' . $email . ', query: ' . $query,
                ]);
                return;
            }
        }

        $hash = $core->in($path[4], true);
        $query = "SELECT * FROM {$db->prefix}users WHERE reset_email_hash = '$hash' LIMIT 1";
        $db->setQuery($query);
        $result = $db->executeQuery('select');
        if ($db->numRows) {
            echo $language->get('user', 'userChooseNewPassword');

            echo '
            <!--FabCMS-hook:beforeResetPasswordSelectForm-->
            <form action="' . $URI->getBaseUri() . $this->routed . '/reset_password/verify/' . $hash . '/change/" method="post" class="form-horizontal" role="form" action="' . $URI->getBaseUri() . $this->routed . '/reset_password/sendbymail/">
              <div class="form-group">
                <label class="control-label col-sm-2" for="password">' . $language->get('user', 'userResetPasswordNewPassword') . '</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" id="password" name="password" placeholder="' . $language->get('user', 'userResetPasswordNewPassword') . '">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-sm-2" for="confirm_password">' . $language->get('user', 'userResetPasswordNewPasswordConfirm') . '</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="' . $language->get('user', 'userResetPasswordNewPasswordConfirm') . '">
                </div>
              </div>';

            if ( (int) $core->getConfig('core', 'recaptchaEnabled') === 1 ) {
                echo '
                    <div class="form-group">
                    <label class="control-label col-sm-2" for="g-recaptcha">' . $language->get('user', 'captcha') . '</label>
                    <div class="col-sm-10">
                        ' . $core->reCaptchaGetCode() . '
                    </div>
                </div>';
            }
            echo '
              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                  <button type="submit" id="registerButtonSubmit" class="btn btn-default">' . $language->get('user', 'resetPasswordButtonResetPassword') . '</button>
                </div>
              </div>
            </form>
            <!--FabCMS-hook:afterResetPasswordSelectForm-->';

            return;
        } else {
            echo 'Query error.';
            return;
        }
        break;
}

echo '
<!--FabCMS-hook:beforeResetPasswordForm-->
<form id="userRegisterForm" method="post" class="form-horizontal" role="form" action="' . $URI->getBaseUri() . $this->routed . '/reset_password/sendbymail/">
  <div class="form-group">
    <label class="control-label col-sm-2" for="email">' . $language->get('user', 'email') . '</label>
    <div class="col-sm-10">
      <input value="' . htmlentities($_POST['email']) . '" type="email" class="form-control" id="email" name="email" placeholder="' . $language->get('user', 'email') . '">
    </div>
  </div>';

if ( (int) $core->getConfig('core', 'recaptchaEnabled') === 1 ) {
    echo '
    <div class="form-group">
        <label class="control-label col-sm-2" for="g-recaptcha">' . $language->get('user', 'captcha') . '</label>
        <div class="col-sm-10">
            ' . $core->reCaptchaGetCode() . '
        </div>
    </div>';
}
echo '
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" id="registerButtonSubmit" class="btn btn-default">' . $language->get('user', 'userResetPasswordButtonReset') . '</button>
    </div>
  </div>
</form>
<!--FabCMS-hook:afterResetPasswordForm-->';