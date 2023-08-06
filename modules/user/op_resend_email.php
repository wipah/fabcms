<?php
if (!$core->loaded) {
    die('Direct call detected');
}


$template->navBarAddItem($language->get('user', 'userManagement'), $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($language->get('user', 'resendActivationEmail'));

$this->addTitleTag(  $language->get('user', 'resendActivationEmail'));


if ($user->logged) {
    echo $language->get('user', 'userResendEmailAlreadyLogged');
    return;
}

if ($path[3] === 'send') {
    // Get the email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        echo '<div class="ui-state-error">No email passed. Aborting</div>'; //todo: language
        return;
    }

    // Check the status of the user
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'users 
              WHERE email = \'' . $email . '\' LIMIT 1';

    $db->setQuery($query);
    if (!$db->executeQuery('select')) {

        $relog->write(['type'      => '4',
                       'module'    => 'user',
                       'operation' => 'resend_email_query_status_error',
                       'details'   => 'Unable to query the user: ' . $query,
        ]);


        echo '<div class="ui-state-error">Query error</div>';
        return;
    }

    if (!$db->affected_rows) {
        echo '<div class="ui-state-error">Email not found</div>';
        return;
    }

    $row = $db->getResultAsArray();
    if ((int)$row['enabled'] === 1) {
        echo '<div class="ui-state-error">User is already enabled.</div>';
        return;
    }

    // Email send
    if (!$user->sendConfirmationEmail($row['ID'], $row['optin_hash'], $username)) {

        $relog->write(['type'      => '4',
                       'module'    => 'USER',
                       'operation' => 'user_resend_email_error',
                       'details'   => 'Unable to send the email. Username: ' . $username,
        ]);

        echo '<div class="bg-danger">' . $language->get('user', 'registrationUnableToSendConfirmEmail') . '</div>';
    } else {
        echo '<div class="bg-success">' . $language->get('user', 'registrationConfirmationEmailSent') . '</div>';

        $relog->write(['type'      => '2',
                       'module'    => 'USER',
                       'operation' => 'user_resend_email_ok',
                       'details'   => 'Send email ok. Username: ' . $username,
        ]);

    }
}

echo '
<!--FabCMS-hook:beforeResendEmailForm-->
<div class="row">
    <div class="col-md-12">
        <p>
            <h3>' . $language->get('user', 'userResendEmail') . '</h3>' .
    sprintf($language->get('user', 'userResendEmailHelp'), $URI->getBaseUri() . $core->router->getRewriteAlias('contacts') . '/') . '
            <form id="userRegisterForm" method="post" class="form-horizontal" role="form" action="' . $URI->getBaseUri() . $this->routed . '/resend_email/send/">
              <div class="form-group">
                <label class="control-label col-sm-2" for="email">' . $language->get('user', 'email') . '</label>
                <div class="col-sm-10">
                  <input value="' . htmlentities($_POST['email']) . '" type="email" class="form-control" id="email" name="email" placeholder="' . $language->get('user', 'email') . '">
                </div>
              </div>

              <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" id="registerButtonSubmit" class="btn btn-default">' . $language->get('user', 'userResetPasswordButtonReset') . '</button>
                </div>
              </div>

            </form>
        </p>
    </div>
</div>
<!--FabCMS-hook:afterResendEmailForm-->';