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

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);

// The basic stuf
if (!file_exists('../config.php')) {
    echo 'FabCMS is not installed. You have to <a href="../install/">install it first</a>';
    return;
}
require_once '../config.php';
if ( $conf['memcache']['enabled']  ) {
    $memcache = new Memcache;
    $memcache->connect('localhost', 11211) or die ("Memcached error");
}

session_start();

include($conf['path']['baseDir'] . '/lib/debug/class_debug.php');
$debug = new debug();
$debug->module = 'mod_load';

// Loads $core
$pathCore = $conf['path']['baseDir'] . '/modules/core/lib/class_core.php';
if (!file_exists($pathCore)) {
    echo 'FabCMS error. Unable to reach core class';
    die();
}
require_once $pathCore;

$core = new CrisaSoft\FabCMS\core();
$debug->write('info', 'Core object loaded');
$core->loaded           =   true;
$core->adminLoaded      =   true;

if ($conf['multilang'] === true) {
    $core->shortCodeLang = 'it';
    $core->multiLang = 'it';
} else {
    $core->shortCodeLang = $conf['langAllowed'][0];
}

// URI logic
require $conf['path']['baseDir'] . 'lib/class_uri/class_uri.php';
$URI = new URI;
$URI->domain = $conf['uri']['domain'];
$URI->subDirectory = $conf['uri']['subdirectory'];
$URI->useHTTPS = $conf['uri']['useHTTPS'];
$URI->usePort = $conf['uri']['usePort'];
$URI->port = $conf['uri']['port'];



require $conf['path']['baseDir'] . 'lib/module/class_module.php';
$module = new \CrisaSoft\FabCMS\module(true);

require $conf['path']['baseDir'] . 'lib/language/class_language.php';
$language = new language();

include_once '../lib/mysql/class_mysqli.php';

$db = new CrisaSoft\FabCMS\dbi ($conf['db']['host'], $conf['db']['user'], $conf['db']['password'], $conf['db']['dbname']);
$db->prefix = $conf['prefix'];

if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
    die();
}
$debug->write('info', 'MySQL connected');

$db->username   =   $conf['db']['user'];
$db->password   =   $conf['db']['password'];
$db->hostname   =   $conf['db']['host'];
$db->dbname     =   $conf['db']['dbname'];
$db->prefix     =   $conf['db']['prefix'];
$debug->write('info', 'DB created');

// Load $log class
require($conf['path']['baseDir'] . '/lib/log/class_log.php');
$log = new log();

if (isset($_GET['module'])) {
    $moduleName = $core->in($_GET['module']);
} else {
    $moduleName = 'redash';
}

require_once($conf['path']['baseDir'] . '/lib/relog/class_relog.php');
$relog = new CrisaSoft\FabCMS\relog();

$core->loadConfig();

// Router
require_once $conf['path']['baseDir'] . 'lib/router/class_router.php';
$core->router = new router();

// Loads plugin module
$pathPlugin = $conf['path']['baseDir'] . '/lib/plugin/class_plugin.php';
if (!file_exists($pathCore)) {
    echo 'FabCMS error. Unable to reach plugin class';
    die();
} else {
    require($pathPlugin);
}
$plugin = new \CrisaSoft\FabCMS\plugin();
$debug->write('info', 'Plugin object loaded');

// Carica ed istanzia l'helper $user
$pathUser = $conf['path']['baseDir'] . 'modules/user/lib/class_user.php';

if (!file_exists($pathUser)) {
    echo 'Internal error. No user class. Please reinstall.';
    die();
} else {
    require($pathUser);
}

$user = new CrisaSoft\FabCMS\user();
$debug->write('info', 'User object created');

$user->checkLogin();

// Time to load the mail
$mailClass = $conf['path']['baseDir'] . '/lib/email/class_email.php';
if (!file_exists($mailClass)){
    echo 'No mail class loaded. Error';
    return;
}
include $mailClass;
$fabmail = new Crisasoft\FabCMS\email();

// At this point, if user is not admin then redirect to login page
// Security question
$a = rand(0, 10);
$b = rand(0, 10);
$t = $a + $b;

$t_hash = md5($conf['security']['siteKey'] . $t);

// Hash based on date
$hashDate = md5($conf['security']['siteKey'] . date('Y-m-d'));

if (!$user->isAdmin && $module !== 'login') {
    echo '
        <div id="loginForm" title="Login" style="background-color:#CDF; padding: 4px;">
          <h1>Login</h1>
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
            </form>
          </p>
          <em>Powered by FabCMS</em>
        </div>
';
    return;
}

/*
 * Template loading
 */

// @todo: admin template
$conf['admin']['template'] = 'fabadmin';

require_once $conf['path']['baseDir'] . 'lib/template/interface_template.php';    // The interface
require_once $conf['path']['baseDir'] . 'lib/template/class_template.php';         // The "core" class
require_once 'templates/' . $conf['admin']['template'] . '/lib/class_template.php';

$template = new template();

/*
 * Module loading
 */
if (isset($moduleName)) {
    $module->name = $moduleName;
    $debug->write('info', 'Module name was taken by rewrite rule.');
}
$language->loadLang($module->name); // Language and translation support for the module
$module->loadModule($module->name);

$template->parse();

echo $template->page;