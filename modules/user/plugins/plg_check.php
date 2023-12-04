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

/**
 * Check if user is logged, otherwise show the login form.
 *
 * @return string
 */
function plugin_check($data)
{
    global $user;
    global $core;
    global $debug;
    global $conf;
    global $URI;
    global $user;
    global $language;
    global $template;

    if ($user->isAdmin) {
        $return = $data['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    if (!$user->logged) {
        // Check if login is allowed
        if ( $core->getConfig( 'user', 'loginEnabled')=== 0) {
            return 'Login disabled';
        }

        // Security question
        $a = rand(0, 10);
        $b = rand(0, 10);
        $t = $a + $b;

        $t_hash = md5($conf['security']['siteKey'] . $t);

        // Hash based on date
        $hashDate = md5($conf['security']['siteKey'] . date('Y-m-d'));

        $template->scripts[] = '
        <script type="text/javascript">

        $("#FabCMSLoginLink").append(" \
        <div id=\"loginForm\" title=\"Login\" style=\"background-color:#CDF; padding: 4px;\"> \
          <p> \
            <form method=\"post\" action=\"' . $URI->getBaseUri() . 'user/login/\"> \
              <div style=\"float:left; width: 100px\"> \
                Email: \
              </div> \
              <div style=\"margin-left: 110px\"> \
                <input id=\"inputEmail\" title=\"Email\" name=\"email\"> \
              </div> \
\
              <div style=\"clear:left\"></div> \
\
              <div style=\"float:left; width: 100px\"> \
                Password: \
              </div> \
              <div style=\"margin-left: 110px\"> \
                <input id=\"inputPassword\" title=\"Password\" name=\"password\" type=\"password\"> \
              </div> \
\
              <div style=\"clear:left\"></div> \
\
              <div style=\"float:left; width: 100px\"> \
                Security: ' . $a . '+' . $b . ' = \
              </div> \
              <div style=\"margin-left: 110px\"> \
                <input type=\"hidden\" name=\"securityQuestionHash\" value=\"' . $t_hash . '\"> \
                <input type=\"hidden\" name=\"securityHashDate\" value=\"' . $hashDate . '\"> \
                <input id=\"inputSecurityQuestion\" title=\"Security question\" name=\"securityQuestion\" type=\"\"> \
              </div> \
 \
              <div style=\"clear:left\"></div> \
 \
              <a href=\"' . $URI->getBaseUri() . 'user/register/\">' . $language->get('user', 'plgCheckSignup') . '</a> \
\
              <button type=\"submit\">' . $language->get('user', 'plgLogin') . '</button> \
              <hr /> \
              <a href=\"' . $URI->getBaseUri() . 'user/reset_password/\">' . $language->get('user', 'plgForgotYourPassword') . '</a> \
            </form> \
          </p> \
        </div>") ;
        </script>

        <script type="text/javascript">
        $("#loginForm").dialog({
          modal: true,
          autoOpen : false,
          minWidth: 500
        });

        function showLoginForm(){
          $("#loginForm").dialog("open");
        }
        </script>
        ';

        return '
        <li class="dropdown">
            <a href="' . $URI->getBaseUri() . 'user/">Login / Register</a><div id="FabCMSLoginLink"></div>
        </li>';

    } else {
        $user->isAdmin === TRUE ? $adminLink = '. <span style=\"background-color:#FAA;\">Go to the <a href=\"' . $URI->getBaseUri(TRUE) . 'admin/admin.php\">admin area</a></span>' : $adminLink = '';
        $theReturn = '
                    <li class="dropdown">
                    <a href="/user/" class="dropdown-toggle" data-bs-toggle="dropdown">
                        User control (' . $user->username . ')
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/cp/">User control panel</a></li>
                        <li><a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/logout/">Logout</a></li>
                        ';
        if ($user->isAdmin){
            $theReturn .= '<li role="presentation" class="divider"></li>
                           <li><a href="' . $URI->getBaseUri() . '/admin/">Admin Control panel</a></li>
            ';
        }

        $theReturn .= '
                    </ul>
                </li>';

        return $theReturn;
    }
}