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

// header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
// header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// A lock file exists?
if (file_exists('lock.lock')) {
    echo file_get_contents('lock.lock');
    return;
}
header('Content-Type: text/html; charset=utf-8');

if (!file_exists('config.php'))
    die ('Config.php is missing');

require_once 'config.php';

if ( $conf['memcache']['enabled']  ) {
     $memcache = new Memcache;
     $memcache->connect('localhost', 11211) or die ("Memcached error");
}

/*
 * Error reporting
 */

if (isset($conf['errorLevel'])) {
    // $debug->write('info', 'Detected directive for error reporting: ' . $conf['errorLevel']);
    switch ($conf['errorLevel']) {
        case 1: // [LIVE]
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', 'off');
            break;
        case 2: // [DEBUG]
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            ini_set('display_errors', 'On');
            break;
        case 3: // [DEBUG]
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_DEPRECATED);
            ini_set('display_errors', 'On');
            break;
        case 4: // [DEBUG] Nuclear meltdown
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            break;
    }
}

session_start();

// URI logic
require $conf['path']['baseDir'] . 'lib/class_uri/class_uri.php';
$URI                =   new URI;
$URI->domain        =   $conf['uri']['domain'];
$URI->subDirectory  =   $conf['uri']['subdirectory'];
$URI->useHTTPS      =   $conf['uri']['useHTTPS'];
$URI->usePort       =   $conf['uri']['usePort'];
$URI->port          =   $conf['uri']['port'];

require $conf['path']['baseDir'] . 'lib/module/class_module.php';
$module = new \CrisaSoft\FabCMS\module();

require $conf['path']['baseDir'] . 'lib/language/class_language.php';
$language = new language();

require_once($conf['path']['baseDir'] . '/lib/debug/class_debug.php');
$debug = new debug();
$debug->module = 'mod_load';

// Loads $core
$pathCore = $conf['path']['baseDir'] . '/modules/core/lib/class_core.php';
if (!file_exists($pathCore)) {
    echo 'FabCMS error. Unable to reach core class';
    die();
} else {
    require($pathCore);
}
$core = new CrisaSoft\FabCMS\core();
$debug->write('info', 'Core object loaded');
$core->loaded = true;

require_once './lib/mysql/class_mysqli.php';

$db = new CrisaSoft\FabCMS\dbi ($conf['db']['host'], $conf['db']['user'], $conf['db']['password'], $conf['db']['dbname']);
$db->prefix = $conf['db']['prefix'];

if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
    die();
}
$debug->write('info', 'MySQL connected');

require_once($conf['path']['baseDir'] . '/lib/relog/class_relog.php');
$relog = new CrisaSoft\FabCMS\relog();

$relog->write(['module' => 'mod_load', 'operation' => 'boot', 'type' => 0]);

register_shutdown_function('shutDownFunction');

$db->username   = $conf['db']['user'];
$db->password   = $conf['db']['password'];
$db->hostname   = $conf['db']['host'];
$db->dbname     = $conf['db']['dbname'];
$db->prefix     = $conf['db']['prefix'];

$path = explode('/', $_SERVER['REQUEST_URI']);
$path[0] = 'root';
foreach ($path as $key => $value) {
    if ($value == '') {
        unset($path[$key]);
    }
}
$path = array_values($path);

// Load Mobile Detect
require_once (__DIR__ . '/lib/mobile-detect/Mobile_Detect.php');
$mobileDetect = new Mobile_Detect();

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

// Router
require_once $conf['path']['baseDir'] . 'lib/router/class_router.php';
$core->router = new router();

// Load $log class
require($conf['path']['baseDir'] . '/lib/log/class_log.php');
$log = new log();

// Load stats class
require($conf['path']['baseDir'] . '/lib/stats/class_stats.php');
$stats = new \CrisaSoft\FabCMS\stats();

// Carica ed istanzia l'helper $user
$pathUser = $conf['path']['baseDir'] . 'modules/user/lib/class_user.php';

if (!file_exists($pathUser)) {
    echo 'Internal error. No user class. Please reinstall.';
    die();
} else {
    require_once ($pathUser);
}
$user = new CrisaSoft\FabCMS\user();
$debug->write('info', 'User object created');

$user->checkLogin();

/*
 * If a site is placed under subdirectory, ie: http://www.domain.tld/sub/domain/module/
 * we have to remove the sub and the domain
 */
if (empty($conf['uri']['subdirectory']) === false) {
    $subArray = explode('/', $conf['uri']['subdirectory']);
    array_shift($path);
    for ($i = 0; $i < count($subArray); $i++) {
        if ($path[0] === $subArray[$i]) {
            array_shift($path);
        }
    }
    array_unshift($path, 'root');
}

// A language has been passed?
if (strlen($path[1]) === 2) { // Yes, it has

    $path[1] = str_replace('\'', '', $path[1]);
    if (strlen($path[1]) !== 2) {
        echo 'No way, sorry.';
        return;
    }

    $core->multiLang = $path[1];
    $core->shortCodeLang = $path[1];

    $debug->write('info', 'URI Language code detected, so using ' . $core->shortCodeLang);
    // 0        1       2       3
    // root     it      module  test
    $tbd = array_shift($path); // it       module  test
    $tbd = array_shift($path); // module   test
    array_unshift($path, 'root'); // root     module  test
} else {
    $debug->write('info', 'URI language not detected');

    if ( $conf['multilang'] === true) {

        if (!isset($path[1])) {

            // Try to automatically redirect the browser
            $langBrowser = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

            if (in_array($langBrowser, $conf['langAllowed'])) {
                header('Location: ' . $URI->getBaseUri() . $langBrowser . '/' . ($conf['defaultModuleForceRedirect'] === true ? $core->router->getRewriteAlias($conf['defaultModule']) . '/' : ''));
            } else {
                header('Location: ' . $URI->getBaseUri() . $conf['langAllowed'][0] . '/' . ($conf['defaultModuleForceRedirect'] === true ? $core->router->getRewriteAlias($conf['defaultModule']) . '/' : ''));
            }

        }

        // Try to load the landing page if exists
        $landingFile = $conf['path']['baseDir'] . 'templates/' . $conf['template'] . '/landing.html';
        if (file_exists($landingFile)) {
            $landing = file_get_contents($landingFile);
            echo $landing;
        } else {

            echo '<div style=\'padding:4px; border: 1px solid black; background-color: #FAFAFA; font-size: x-large; font-family: Calibri, "Lucida Grande"\'>' .
                ' <div style=\'border: 1px solid #808080; background-color: #EAFFEA; font-size: xx-large;\'>Please, choose a language.</div>';
            foreach ($conf['langAllowed'] as $singleLang) {
                echo '  &bull; <a href="' . $URI->getBaseUri(false, $singleLang) . '/' . ($conf['defaultModuleForceRedirect'] === true ? $core->router->getRewriteAlias($conf['defaultModule']) . '/' : '') . '">' . $singleLang . '</a><br/>';
            }
            echo ' </div>' .
                '</div>
                    <hr>
                    <div style="float: right;color: #919B9C;">Powered by <a href="http://fabcms.crisasoft.com">FabCMS</a>.</div>
              ';
        }

        return;
    }

    if ( isset( $conf['langAllowed'][0] ) ) {
        $debug->write('info', 'Language not passed, using the value from config.php: ' . $conf['langAllowed'][0] );
        $core->shortCodeLang = $conf['langAllowed'][0];
    } else {
        $debug->write('info', 'Language not passed, and language directive was not found on config.php. Using italian (it) as default: ');
        $core->shortCodeLang = 'it';
    }
}

$core->loadConfig();
if( false === $core->getConfig('user', 'minimumPasswordLenght')){
    die("Please, configure the minimumPasswordLenght.");
}

/*
 * Loads email
 */
$mailClass = $conf['path']['baseDir'] . '/lib/email/class_email.php';
if (!file_exists($mailClass)){
    die('FabCMS was not able to find email class. Please check your installation.');
}
require_once $mailClass;
$fabmail = new Crisasoft\FabCMS\email();
$debug->write('info','Email class loaded','mod_load');

// At this point we have to check if a redirect without language must be done
if (!isset($path[1]) && $conf['defaultModuleForceRedirect'] === true) {
    header('Location: ' . $URI->getBaseUri() . $core->router->getRewriteAlias($conf['defaultModule']) . '/');
    return;
}

// A routing roule exists?
$theKey = $router[ (string) $core->shortCodeLang];
if (!is_null($theKey) && $arraypos = array_keys( $theKey, $path[1])) {
    $debug->write('info', 'Routing route found in router.php: ' . $path[1] . '->' . $arraypos[0]);
    // Just one?
    if (count($arraypos) > 1) {
        echo 'Fatal error: more routing path found.';
        return;
    }

    $moduleName = $arraypos[0];
} else {
    $debug->write('info', 'Routing not found. Searched for ' . $path[1]);
}

require_once 'lib/template/interface_template.php';    // The interface
require_once 'lib/template/class_template.php';         // The "core" class

// Check if the template can extend the template class
$templateName = $core->getConfig('template','template');
$templateVariant = $core->getConfig('template', 'templateVariant');

$templateClass = __DIR__ . '/templates/' . $templateName . '/lib/class_template.php';

if (file_exists($templateClass)){
    require_once $templateClass;
    $template = new template();
} else {
    $template = new templateBase();
}
$template->template = $templateName;
$templateVariant = $templateVariant;

$template->loadTranslation();

/* Loads connector */
$connectorPath = $conf['path']['baseDir'] . 'modules/connector/lib/class_connector.php';
if (!file_exists($connectorPath)) {
    die ('Cannot load connector class. Aborting');
}
require_once $connectorPath;
$connector = new CrisaSoft\FabCMS\connector();

/*
 * Module loading
 */
if (isset($moduleName)) {
    $module->name = $moduleName;
    $debug->write('info', 'Module name was taken by rewrite rule.');
} elseif (isset ($path[1]) && !isset($moduleName)) {
    $module->name = $path[1];
    $debug->write('info', 'URI, a module has been passed:' . htmlentities($path[1]));
} else {
    $debug->write('info', 'URI, module not passed, using default: ' . $core->getConfig('core', 'defaultModule'));
    $module->name = $core->getConfig('core', 'defaultModule');
}

if ($module->name !== 'user')
    $language->loadLang('user');

$language->loadLang($module->name); // Language and translation support for the module


$module->routed = $core->router->getRewriteAlias($module->name);
$module->loadModule($module->name);
$template->loadPage();
$template->parse();

echo $template->page;

$db->close();

function shutDownFunction()
{
    global $relog;
    $error = error_get_last();

    $message = $error['message'];
    $file = $error['file'];
    $line = $error['line'];

    if ($error['type'] === E_ERROR || $error['type'] === E_COMPILE_ERROR)
    {

        $relog->write(['module'    => 'generic',
                       'operation' => 'generic',
                       'type'      => 4,
                       'details'   => $message . '. File: ' . $file . ' / ' . $line]
        );

        echo 'We have got an internal error. We apoligize. ' . $message . ' (file: ' . $file . ', line: ' . $line . ')';

    }
}