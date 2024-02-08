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

error_reporting(E_ALL);
$isInstalling = true;

require_once 'lib/class_installer.php';

$fabInstaller = new fabInstaller();

if (file_exists('../config.php') || file_exists('../db_version')) {
    echo 'FabCMS is already installed.';
    die();
}

switch ($_GET['stage']) {
    case 'install':
        require_once 'op_write.php';
        return;
        break;
}


// Check for permissions
$directories = ['cache',
                'fabmedia',
                'templates',
                'modules/forum/assets/',
                'modules/forum/assets/custom_avatars/'];
$permission = true;
$permission_output = '';
foreach ($directories as $directory) {
    if (is_writable ('../' . $directory)) {
        $permission_output .= '&bull; ' . $directory . ' is writable; <br/>';
    } else {
        $permission = false;
        $permission_output .= '&bull; ' . $directory . ' is NOT writable; <br/>';
    }
}

if (false === $permission){
    echo $permission_output;
    return;
}

$pathSubdirectory = $_SERVER['REQUEST_URI'];
$pathSubdirectory = explode('/', $pathSubdirectory);
$pathSubdirectory = $pathSubdirectory[1];

$domain = $_SERVER['SERVER_NAME'];
echo '<!DOCTYPE HTML>
<head>
   <title>FabCMS installer</title>
</head>
<!-- Jquery -->		
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>	
<body>
<style type="text/css">
</style>
<div class="container">
   <div class="row">
      <div class="col-lg-12">
         <h1>FabCMS installer</h1>
         ' .  $permission_output . '
         <form action="install.php?stage=install" method="post">
         
         <input type="hidden" name="dummy" id="dummy">
         
            <ul class="nav nav-pills">
               <li class="active"><a data-bs-toggle="pill" href="#mysql">MySQL</a></li>
               <li><a data-bs-toggle="pill" href="#path">Path</a></li>
               <li><a data-bs-toggle="pill" href="#security">Security</a></li>
               <li><a data-bs-toggle="pill" href="#cms">CMS</a></li>
               <li><a data-bs-toggle="pill" href="#first_admin">First admin</a></li>
            </ul>
            <div class="tab-content">
               <div id="mysql" class="tab-pane fade in active">
        
                <div class="form-horizontal">
                <fieldset>
                
                <!-- Form Name -->
                <legend>MySQL settings</legend>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbDbName">Database</label>  
                  <div class="col-md-4">
                  <input id="dbDbName" name="dbDbName" type="text" placeholder="Database name" class="form-control input-md" required>
                  <span class="help-block">Database name (will not be created)</span>  
                  </div>
                </div>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbUsername">Username</label>  
                  <div class="col-md-4">
                  <input id="dbUsername" name="dbUsername" type="text" placeholder="Username" class="form-control input-md" required>
                  <span class="help-block">Username</span>  
                  </div>
                </div>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbPassword">Password</label>  
                  <div class="col-md-4">
                  <input id="dbPassword" name="dbPassword" type="text" placeholder="Password" class="form-control input-md" >
                  <span class="help-block">Password</span>  
                  </div>
                </div>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbPort">Port</label>  
                  <div class="col-md-4">
                  <input id="dbPort" name="dbPort" type="text" placeholder="3306" class="form-control input-md" value="3306">
                  <span class="help-block">Port (Usually 3306)</span>  
                  </div>
                </div>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbPrefix">Prefix</label>  
                  <div class="col-md-4">
                  <input id="dbPrefix" name="dbPrefix" type="text" placeholder="fabcms_" class="form-control input-md" value="fabcms_">
                  <span class="help-block">Db prefix, usually fabcms_</span>  
                  </div>
                </div>
                
                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="dbServer">Server</label>  
                  <div class="col-md-4">
                  <input id="dbServer" name="dbServer" type="text" placeholder="localhost" class="form-control input-md" value="localhost" required>
                  <span class="help-block">Server name (usually is localhost or 127.0.0.1)</span>  
                  </div>
                </div>
                
                </fieldset>
                </div>
                
                        
                 </div>
                        
                <div id="path" class="tab-pane fade">
                  <h3>Path</h3>
                
                  <div class="form-group">
                    <label for="path">Path</label>
                    <input type = "text" 
                           id   ="path" 
                           name ="path"
                           class="form-control" 
                           value="' . $_SERVER["DOCUMENT_ROOT"] . (empty($pathSubdirectory) === true ? '' : '/' . $pathSubdirectory) . '">
                  </div>
                
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="float-left" for="path_usePort">Use Port</label>
                            <input type="checkbox" id="path_usePort" name="path_usePort" class="form-control" value="true">
                        </div>  			  
                    </div>
                    <div class="col-md-6">    			  	
                      <div class="form-group">
                        <label class="float-left" for="path_port">Port</label>
                        <input type="text" id="path_port" name="path_port" class="form-control">
                      </div>		
                    </div>
                  </div>
                            
                  <div class="form-group">
                    <label for="path_useHTTPS">Use HTTPS</label>
                    <input type="checkbox" id="path_useHTTPS" name="path_useHTTPS" class="form-control" value="true">    			  	
                  </div> 
                
                 <div class="form-group">
                    <label for="path">Domain (http://<u>www.example.com</u>/subpath/)</label>
                    <input type="text" id="path_domain" name="path_domain" class="form-control" value="' . $domain . '">
                  </div>
                            
                 <div class="form-group">
                    <label for="path">Subpath (http://www.example.com/<u>subpath</u>/)</label> without ending slash
                    <input type="text" id="path_subdirectory" name="path_subdirectory" class="form-control" value="' . ($pathSubdirectory == 'install' ? '' : substr_replace('install/', '', $pathSubdirectory)) . '">
                  </div>
                                    
                </div>
                                            
                <div id="security" class="tab-pane fade">
                    <h3>Security</h3>
                                            
                    <div class="form-group">
                        <label for="securityKey">Security key (be sure to keep this in a safe place)</label>
                        <input type="text" id="securityKey" name="securityKey" class="form-control" value="' . md5(microtime()) . '">
                   </div>
                                        
                </div>
                                            
                <div id="cms" class="tab-pane fade">
                    <h3>CMS</h3>
                    <div class="form-group">
                        <label for="language">Language(s) (<em>it, en, de</em>  uses <strong>it</strong> as primary language)</label>
                        <input type="text" id="language" name="language" class="form-control" value="it">    			  	
                    </div> 
                    
                </div>
                                            
                <div id="first_admin" class="tab-pane fade">
                    <h3>First Admin</h3>
                        
                                            
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="">
                        </div>
                                            
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" value="">
                        </div>
                                            
                        <div class="form-group">
                            <label for="password_confirm">Password</label>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control" value="">
                        </div>
                                            
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" id="email" name="email" class="form-control" value="">
                        </div>		
                </div>
            </div>
                           		
            <button type="submit" class="btn btn-success float-right"><span class="glyphicon glyphicon-floppy-save"></span> Install</button>
         </form>
      </form>
   </div>
</body>
</html>';