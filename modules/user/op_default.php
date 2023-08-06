<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 17/04/15
 * Time: 9.11
 */

if (!$core->loaded)
    die ('Cannot load the file outside FabCMS');

$theLink = $URI->getBaseUri() . $core->router->getRewriteAlias('contacts') . '/';

$template->navBarAddItem($language->get('user', 'userManagement'), $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($language->get('user', 'userShowInfoUser'));

$template->fullPage = true;

if (!$user->logged) {
    // At this point, if user is not admin then redirect to login page
    // Security question
    $a = rand(0, 10);
    $b = rand(0, 10);
    $t = $a + $b;

    $t_hash = md5($conf['security']['siteKey'] . $t);

    // Hash based on date
    $hashDate = md5($conf['security']['siteKey'] . date('Y-m-d'));

    echo '
    <div class="container">
            <div class="row">
                <div class="col-md-6">
                    
                    <h3>Login</h3>
                    
                        <p>
                            <form method="post" action="' . $URI->getBaseUri() . 'user/login/">
                              <div style="float:left; width: 100px">
                                Email:
                              </div>
                              <div style="margin-left: 110px">
                                <input id="inputEmail" title="Email" name="email">
                              </div>
    
                              <div style="clear:left"></div>
    
                              <div style="float:left; width: 100px">
                                Password:
                              </div>
                              <div style="margin-left: 110px">
                                <input id="inputPassword" title="Password" name="password" type="password">
                              </div>
    
                              <div style="clear:left"></div>
    
                              <div style="float:left; width: 100px">
                                Security: ' . $a . '+' . $b . ' =
                              </div>
    
                              <div style="margin-left: 110px">
                                <input type="hidden" name="securityQuestionHash" value="' . $t_hash . '">
                                <input type="hidden" name="securityHashDate" value="' . $hashDate . '">
                                <input id="inputSecurityQuestion" title="Security question" name="securityQuestion" type="">
                              </div>
    
                              <div style="clear:left"></div>
    
                              <button type="submit">Login</button>
                              <hr />
                              &bull; <a href="' . $URI->getBaseUri() . 'user/register/">' . sprintf($language->get('user', 'registerTo'), $conf['site']['name']) . '</a>
                            </form>
                        </p>
                
                </div>
                
                <div class="col-md-6">
                    <h3>' . $language->get('user', 'userDefaultHelpLinks') . '</h3>
                    <p>
                    &bull; <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/reset_password/">' . $language->get('user', 'userDefaultPasswordForgot') . '</a>.<br/>
                    &bull; <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/resend_email/">' . $language->get('user', 'userResendEmail') . '</a>.<br/>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <h3>' . $language->get('user', 'userDefaultRegistrationHelp') . '</h3>
                <p>
                ' . sprintf($language->get('user', 'userDefaultContactUsLink'), $theLink) . '
                </p>
                </div>
            </div>
        </div>
    ';
} else {

    $this->addTitleTag(  $language->get('user', 'defaultTitleNotLogged') );

    $theLink = $URI->getBaseUri() . $this->routed . '/' . 'cp/';
    echo sprintf($language->get('user', 'userDefaultWelcome'), $user->username, $theLink);
}