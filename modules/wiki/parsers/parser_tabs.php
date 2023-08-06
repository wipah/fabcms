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
    die('Direct call detected. tabs.');

/*
  * Search and process the tabs widget.
  *
  * The first line contains the configuration
  * The second line contains the name of the tabs
  * The third line contains the contents_remove of the tabs
  *
  *  [$$
  *  TABS
  *  |*tabCustomStyle==custom style||tabType==the description*|
  *  |*Tab 1||Tab 2||Tab 3*|
  *  |*This is the contents_remove of TAB 1*|
  *  |*This is the contents_remove of TAB 2*|
  *  |*This is the contents_remove of TAB 3*|
  *  $$]
  */
$theRegex = '#\[\$\$([\r\n|\<br \/>]+TABS)(.*?)\$\$\]#mis';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){

        global $template;
        $tabsConfig = array();
        // Now it's time to find any [* ... *] subset
        $theRegex = '#\|\*(.*?)\*\|#mis';
        preg_match_all($theRegex, $theFirstMatch[2], $theSecondMatch);

        if (!isset($tabsConfig['ID']))
            $tabsConfig['ID'] = 'TEST';

        $tabsName = [];

        $i = 0;
        $items = array();
        foreach ($theSecondMatch[1] as $item){
            if ($i === 0){ // The first line contains the configuration
                $theLine = explode('||', $item);
                foreach ($theLine as $singleLine){
                    $theSegment = explode('==', $singleLine);
                    $tabsConfig[$theSegment[0]] = $theSegment[1];
                }
                $i++;
                continue;
            }else if ($i === 1){ // The second line contains the tabs
                $theLine = explode('||', $item);
                foreach($theLine as $singleLine){
                    $tabsName[] = $singleLine;
                }
            }else{ // The third data is processed "as" is
                $tabsData[] = $item;
            }
            $i++;
        }

        return $template->getTabs($tabsConfig['ID'], $tabsName, $tabsData, $tabsConfig);
    }, $content);