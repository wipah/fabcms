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

if (!$core->loaded)
    die('Direct call detected. Parser.');
/*
* Search and process the panel widget. The syntax to build this widget is very simple
*

[$$
PANEL
|*type==warning||collapsible==true||startCollapsed==false*|
|*Header*|
|*Text here*|
$$]

*/
$theRegex = '#\[\$\$([\r\n|\<br \/>]+PANEL)(.*?)\$\$\]#mis';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;
        global $conf;
        global $user;

        $panelConfig = [];

        // Now it's time to find any [* ... *] subset
        $theRegex = '#\|\*(.*?)\*\|#mis';
        preg_match_all($theRegex, $theFirstMatch[2], $theSecondMatch);

        $i = 0;
        foreach ($theSecondMatch[1] as $item){
            switch ($i){
                case 0:
                    // The first line is the configuration
                    $theLine = explode('||', $item);
                    foreach ($theLine as $singleLine){
                        $theSegment = explode('==', $singleLine);
                        $panelConfig[$theSegment[0]] = $theSegment[1];
                    }
                    break;
                case 1:
                    // Header
                    $header = $item;
                    break;
                case 2:
                    // Body
                    $body =  $item;
                    break;
            }
            $i++;
        }

        $panelConfig['collapsible']     == "true" ? $collapsible    = true : $collapsible       = false;
        $panelConfig['startCollapsed']  == "true" ? $startCollapsed = true : $startCollapsed    = false;

        if (!isset($panelConfig['type']))
            $panelConfig['type'] = 'info';

        return $template->getPanel($header, $body, $panelConfig['type'], $collapsible, $startCollapsed);

    }, $content);

