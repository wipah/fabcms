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

use CrisaSoft\FabCMS\fabInstaller;

error_reporting(E_NONE);

echo '<pre>' . print_r($_POST, true ) . '</pre>';

if (!isset($_POST['dummy']))
    die ('Direct call detected');

require_once 'lib/class_installer.php';
$fabInstaller = new fabInstaller();

require_once '../modules/core/lib/class_core.php';
$core = new CrisaSoft\FabCMS\core;

require_once '../lib/debug/class_debug.php';
$debug = new debug;

require_once '../modules/user/lib/class_user.php';
$user = new CrisaSoft\FabCMS\user();

require_once '../lib/mysql/class_mysqli.php';

/*
 * Language
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Language</div>';

if (empty($_POST['language'])){
    $languageArrayConfig = '[\'it\']';
    $languagesArray      = ['it'];
} else {
    $language = $_POST['language'];

    $languagesArray = explode(', ', $language);

    count($languagesArray) > 1 ? $multilang = 'true' : $multilang = 'false';

    $languageArrayConfig = '[';

    foreach ($languagesArray as $singleLang){
        $languageArrayConfig .= '\'' . substr($singleLang,0,2) . '\', ';
    }

    $languageArrayConfig = substr($languageArrayConfig, 0, -2) . ']';

}

echo '&bull; Language config will be ' . $languageArrayConfig . '<br/>';

/*
 * MySQL
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Database</div>';

$mysqlUsername          =   $_POST['dbUsername'];
$mysqlPassword          =   $_POST['dbPassword'];
$mysqlPort              =   $_POST['dbPort'];
$mysqlHost              =   $_POST['mysql_host'];
$mysqlDbname            =   $_POST['dbDbName'];
$mysqlPrefix            =   $_POST['dbPrefix'];

$securityKey            =   $_POST['securityKey'];

$db = new CrisaSoft\FabCMS\dbi ($mysqlHost, $mysqlUsername, $mysqlPassword, $mysqlDbname, $mysqlPort);
$db->prefix = $mysqlPrefix;

if ($db->connect_errno) {
    die('Failed to connect to MySQL: (' . $db->connect_errno . ') ' . $db->connect_error);
} else {
    echo '&bull; MySQL connected. <br/>';
}

foreach (glob('../modules/*/install') AS $installer)
{
    echo '&bull; Installing <strong>' . $installer . '</strong> <br/>';

    if (file_exists( $installer . '/struct.sql' )){
        echo '--> struct.sql <br/>';
        doQuery($installer . '/struct.sql');
    }
    installFlush();
    if (file_exists( $installer . '/data.sql' )){
        echo '--> data.sql <br/>';
        doQuery($installer . '/data.sql');
    }
    installFlush();
    if (file_exists( $installer . '/install.php' )){
        echo '--> install.php <br/>';
        require_once($installer . '/install.php');
    }
    installFlush();

    $query = 'INSERT INTO ' .$db->prefix . 'core_modules 
              (
                module, 
                schema, 
                enabled
              ) 
              VALUES (
              \'' . $installer . '\',
              0,
              1
              );';
    
    $db->query($query);
    echo '--> Schema version updated. <br/>';
    installFlush();
}

/*
 * First user
 */
$usermame   = in($_POST['username']);
$password   = getPasswordHash($_POST['password']);
$email      = in($_POST['email']);
echo '<div style="border:1px solid gray; padding: 4px">First user</div>';
$query = "INSERT INTO {$mysqlPrefix}users 
          (
          group_ID,
          admin,              
          username,
          email,
          password,
          enabled
          ) VALUES (
          1,
          1,       
          '$usermame',
          '$email',
          '$password',
          1       
          );";


if (!$db->query($query)){
    echo '&ebull; Unable to insert the admin. ' . $query;
    return;
} else {
    echo '&ebull; Admin inserted. <br/>';
}
installFlush();

/*
 * Htaccess
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Htaccess</div>';
$pathSubdirectory = $_POST['path_subdirectory'];

$htaccess = '
RewriteEngine on
Rewritebase /' . $pathSubdirectory .'
php_flag magic_quotes_gpc off
IndexIgnore *

php_flag file_uploads On
php_value post_max_size 105M
php_value upload_max_filesize 105M

DirectoryIndex mod_load.php index.php

RewriteCond %{REQUEST_URI} !admin/(.*) [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) mod_load.php [L]
';

if (!file_put_contents('../.htaccess', $htaccess)){
    echo '&ebull; Unable to write htaccess. <br/><textarea>' . $htaccess . '</textarea><br/>';
} else {
    echo '&ebull; Htaccess created. <br/>';
}
installFlush();
/*
 * Template
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Template configuration</div>';
$query = '
INSERT INTO fabcms_config (lang, module, param, value)
VALUES
( \'' . $languagesArray[0] . '\', \'template\', \'template\', \'fabtemplate\' ),
( \'' . $languagesArray[0] . '\', \'template\', \'templateVariant\', \'default.css\' );';


if ($db->query($query)){
    echo '&bull; Template set<br/>';
} else {
    echo '&ebull; [ERROR]. Query error while inserting template. ' . $query;
    return;
}
installFlush();

/*
 * Default module
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Template configuration</div>';
$query = '
INSERT INTO fabcms_config (lang, module, param, value)
VALUES
( \'' . $languagesArray[0] . '\', \'core\', \'defaultModule\', \'wiki\' );';


if ($db->query($query)){
    echo '&bull;  Default mode set.<br/>';
} else {
    echo '&ebull; [ERROR]. Query error while inserting default module. ' . $query;
    return;
}
installFlush();

/*
 * config.php
 */
echo '<div style="border:1px solid gray; padding: 4px; background-color: #EEE">Configuration file</div>';

$_POST['path_usePort'] === 'true' ? $pathUsePort = 'true' :  $pathUsePort = 'false' ;
$_POST['path_useHTTPS'] === 'true' ? $pathUseHttps = 'true' :  $pathUseHttps = 'false' ;
$pathPort = (int) $_POST['path_port'];
$path = $_POST['path'] . '/';
$path = str_replace('//', '/', $path);

$pathDomain = $_POST['path_domain'];
$pathSubdirectory= $_POST['path_subdirectory'];

$config = "<?php
/* FabCMS configuration file, autogenerated from installation. We are so smart! */

/* Path */
\$conf['path']['baseDir'] = '$path';

/* MySQL config */
\$conf['db']['user']        = '$mysqlUsername';
\$conf['db']['password']    = '$mysqlPassword';
\$conf['db']['host']        = '$mysqlHost';
\$conf['db']['port']        = $mysqlPort;
\$conf['db']['dbname']      = '$mysqlDbname';
\$conf['db']['prefix']      = '$mysqlPrefix';

\$conf['debug']['enabled']          = false;
\$conf['debug']['showDebugToGuest'] = false;

\$conf['langAllowed']   = $languageArrayConfig;
\$conf['multilang']     = $multilang;

/* Email */
\$conf['email']['noReply']      = '';
\$conf['email']['siteEmail']    = '';

/* Site Name */
\$conf['site']['name'] = 'FabCMS new site';

/* Error Handler */
\$conf['errorLevel'] = 1;

\$conf['uri']['useHTTPS']       = $pathUseHttps;
\$conf['uri']['usePort']        = $pathUsePort;
\$conf['uri']['port']           = '$pathPort';
\$conf['uri']['domain']         = '$pathDomain';
\$conf['uri']['subdirectory']   = '$pathSubdirectory';

\$conf['security']['siteKey']               = '$securityKey';
\$conf['security']['minimumPasswordLenght'] = 5;

/* Cache */
\$conf['cache']['enabled']  = true;
\$conf['cache']['time']     = 172800;

\$conf['organization'] = 'MyOrganization';
\$conf['organizationLogo'] = 'pathToLogo';";

echo '<pre>' . $config . '</pre>';
if (!file_put_contents('../config.php', $config)) {
    echo '&bull; Error writing configuration file. <br/><textarea>' . $config . '</textarea>';
} else {
    echo '&bull; Configuration file was saved.';
}
installFlush();

function installFlush(){
    flush();
    ob_flush();
    return;
}

function getPasswordHash($password){
    return md5($password . $_POST['securityKey']);
}
function in($text){
    return str_replace('\'', '\\\'', $text);
}

function doQuery($queryFile){
    global $db;

    $query = file($queryFile);

    $query = str_replace('fabcms_', $db->prefix, $query);

    // Temporary variable, used to store current query
    $templine = '';

    // Loop through each line
    foreach ($query as $line)
    {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $templine .= ' ' . $line;

        // If it has a semicolon at the end, it's the end of the query
        if (preg_match('#(.*)?(;\w?\s?$)#mix', $line)) {

            // Perform the query

            if (!$db->query($templine)) {
                echo '<pre style="border:1px solid red; padding: 8px; margin-left: 12px">' . $templine . '</pre>';
                die ("Query error. " . $db->lastError);
            }

            // Reset temp variable to empty
            $templine = '';
        }
    }

}


return;