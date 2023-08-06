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
class router
{
    function __construct()
    {
        global $conf;
        global $debug;
        global $router;

        // Loads router.php if this file exists
        $routerPath = $conf['path']['baseDir'] . 'router.php';

        if (!file_exists($routerPath)) {
            $debug->write('warning', 'Router.php not found!', 'ROUTER');
        } else {
            require_once $routerPath;
            $debug->write('info', 'Router.php loaded', 'ROUTER');
        }

    }

    function getRewriteAlias($module, $lang = '')
    {
        global $core;
        global $debug;
        global $router;

        if (empty($lang)) {
            $lang = $core->shortCodeLang;
        }

        if (!isset($router[$lang][$module])) {
            $debug->write('info', 'Requesting a rule for module ' . $module . ' with lang ' . $lang . '. Rule was not found, so return value is module\'s name: ' . $module, 'ROUTER');
            return $module;
        } else {
            $debug->write('info', 'Requesting a rule for module ' . $module . ' with lang ' . $lang . '. Rule was found, so return value is: ' . $router[$lang][$module], 'ROUTER');
            return $router[$lang][$module];
        }
    }
}