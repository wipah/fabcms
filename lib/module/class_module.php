<?php

namespace CrisaSoft\FabCMS;

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
class module
{
    private $path;

    public $majorVersion;
    public $minorVersion;
    public $buildVersion;
    public $dbSchema;
    public $moduleAuthor;

    public $routed;
    public $content;
    public $head;
    public $name;
    public $title;
    public $adminSide;

    public $scripts;

    /**
     * Meta keywords
     * @var
     */
    public $metaKeywords;

    /** Set true if module outputs "raw" data and there's no need to parse the template.
     * Useful, as an example, when headers are set.
     *
     * @var bool
     */
    public $noTemplateParse = false;


    function __construct($adminSide = false)
    {
        global $core;
        $adminSide == true ? $this->adminSide = true : $this->adminSide = false;
    }

    function addScript($script, $type = 'text/javascript', $forceInHead = false)
    {
        if ($forceInHead === true) {
            $this->head .= '<script type="' . $type . '">' . $script . '</script>' . "\r\n";
        } else {
            $this->scripts .= '<script type="' . $type . '">' . $script . '</script>' . "\r\n";
        }

    }

    function addJsFile($path, $forceInHead = false, $async = false)
    {

        if ($forceInHead === true) {
            $this->head .= '<script src="' . htmlentities($path) . '"' . ($async === true ? ' defer="defer"' : '') . '></script>' . "\r\n";
        } else {
            $this->scripts .= '<script src="' . htmlentities($path) . '"' . ($async === true ? ' defer="defer"' : '') . '></script>' . "\r\n";
        }

    }

    function addStyle($style, $type = 'text/css')
    {
        $this->head .= '<style type="' . $type . '">' . $style . '</style>' . "\r\n";
    }

    function addCSSLink($fileName, $isInternal = true, $media = 'all', $isExternal = false)
    {
        global $URI;
        global $core;

        $fileName = htmlentities($fileName);
        $media = htmlentities($media);

        if ($isExternal === true) {
            $this->head .= '<link rel="stylesheet" href="' . $fileName . '" type="text/css" media="' . htmlentities($media) . '">' . PHP_EOL;
            return;
        }

        if ($isInternal === true) {
            $link = $URI->getBaseUri(true) . 'modules/' . $this->name . '/css/' . htmlentities($fileName) . '.css';
        } else {
            $link = $URI->getBaseUri(true) . $fileName;
        }

        $this->head .= '<link rel="stylesheet" href="' . $link . '" type="text/css" media="' . htmlentities($media) . '">' . PHP_EOL;
    }

    function addMeta($property, $content)
    {
        $property = htmlentities($property);
        $content = htmlentities($content);
        $this->head .= '<meta property="' . $property . '" content="' . $content . '" />' . PHP_EOL;

    }

    function addMetaData($meta, $data)
    {
        $this->head .= '<meta name="' . htmlentities($meta) . '" content="' . $data . '" />' . PHP_EOL;
    }

    function addTitleTag($title)
    {
        $this->head .= '<title>' . htmlentities($title) . '</title>' . PHP_EOL;
    }

    function loadModule($module)
    {
        global $conf;
        global $language;
        global $path;
        global $core;
        global $URI;
        global $debug;
        global $user;
        global $error;
        global $db;
        global $template;
        global $log;
        global $router;
        global $fabmail;
        global $connector;
        global $stats;
        global $relog;
        global $memcache;

        $module = str_replace('.', '', $module); //@todo: security check

        // Check if module has custom configuration file (config.php)
        $moduleConfig = $conf['path']['baseDir'] . ($this->adminSide == true ? 'admin/' : '') . 'modules/' . $module . '/config.php';
        if (file_exists($moduleConfig)) {
            $debug->write('info', 'Module ' . $module . ' has configuration file: loading it');
            include $moduleConfig;
        }

        // Loads interface if it exists
        $interface = $conf['path']['baseDir'] . 'modules/' . $module . '/mvc/interface_' . $module . '_view.php';
        if (file_exists($interface)) {
            $debug->write('info', 'Module ' . $module . ' has interface. Loading it');
            require_once $interface;
        } else {
            $debug->write('info', 'Module ' . $module . ' hasn\'t any interface');
        }

        // Loads view if it exists

        // Check if module has custom version track (version.php)
        $moduleVersion = $conf['path']['baseDir'] . 'modules/' . $module . '/version.php';
        if (file_exists($moduleVersion)) {
            $debug->write('info', 'Module ' . $module . ' has version file: loading it');

            require_once $moduleVersion;

            $this->majorVersion = $version[$module]['major'];
            $this->minorVersion = $version[$module]['minor'];
            $this->buildVersion = $version[$module]['build'];
            $this->dbSchema = $version[$module]['dbSchema'];
            $this->moduleAuthor = $version[$module]['author'];

        } else {
            $debug->write('info', 'Module ' . $module . ' has any version file to include. (' . $moduleVersion . ')');
        }

        $this->path = $conf['path']['baseDir'] . ($this->adminSide == true ? 'admin/' : '') . 'modules/' . $module . '/' . $module . '.php';
        $debug->write('info', 'Trying to load module (' . $this->path . ')', 'MODULE');

        // Check if module exists
        if (!file_exists($this->path)) {
            header("HTTP/1.0 404 Not Found");
            $debug->write('warn', 'Module not found', 'MODULE');

            $this->content .= 'Module ' . strip_tags($module) . ' not found.<br />';

            $this->content .= $core->getConfig('template', 'template404', 'extended_value');
            return;
        }

        $debug->write('info', 'Module found. Including...', 'MODULE');

        // Loads the module
        ob_start();
        include($this->path);
        $this->content = ob_get_contents() . (
            $this->noTemplateParse === true // Not showing any version info when template is not parsed
                ? ''
                : '<div style="clear:both"></div>
                                    <div style="text-align: right; margin-top: 12px; font-size:x-small; color: #666;">' .
                $module . ' ver.' . $this->majorVersion . '.' . $this->minorVersion . '.' .
                $this->buildVersion . '. &copy; ' . $this->moduleAuthor . '</div>'
            );
        ob_end_clean();

        if (isset($this->title)) {
            $this->head .= '<title>' . htmlentities($this->title) . '</title>';
        }
    }

}