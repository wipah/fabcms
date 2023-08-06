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
PROCONS
|*type==warning||collapsible==true||startCollapsed==false*|
|*Header*|
|* Pro 1 || Pro 2 || Pro 3 || Pro 4*|
|* Cons 1 || Cons 2 || Cons 3 || Cons 4*|
$$]

*/
$theRegex = '#\[\$\$([\r\n|\<br \/>]+PROCONS)(.*?)\$\$\]#mis';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;
        global $conf;
        global $user;
        global $language;

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
                    $pro =  $item;
                    break;
                case 3:
                    $cons = $item;
                    break;
            }
            $i++;
        }

        $output = '<table class="table table-borderd table-striped">
                        <caption>' . $header . '</caption>
                        <thead>
                            <tr>
                                <th>' . $language->get('wiki','wikiParserProConsPro') . '</th>
                                <th>' . $language->get('wiki','wikiParserProConsCons')  .'</th>
                            </tr>
                        </thead>
                    <tbody>
        ';

        $proItems = explode('||', $pro);
        $consItems = explode('||', $cons);

        $pro = '';
        foreach ($proItems as $singlePro) {
            $pro .= '<i style="color:green" class="fas fa-check-square"></i>' . $singlePro . '<br/>';
        }

        $cons = '';
        foreach ($consItems as $singleCons) {
            $cons .= '<i style="color:red" class="fas fa-times"></i>' . $singleCons . '<br/>';
        }

        $output .= '<tr>
                        <td>' . $pro . '</td>
                        <td>' . $cons . '</td>
                    </tr>
                    </tbody>
                    </table>';

        return $output;

    }, $content);