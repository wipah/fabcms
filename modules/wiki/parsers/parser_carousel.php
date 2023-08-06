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
    die('Direct call detected. carousel');

/*
* Search and process the carousel widget. The syntax to build this widget is very simple
*
*  [$$
*  CAROUSEL
*  |*header==the header||description==the description*|
*  |*src==http://www.example.com/img1.png||alt==this is an image||href==http://www.google.com*|
*  |*src==http://www.example.com/img2.png||alt==this is an image||href==http://www.microsoft.com*|
*  |*src==http://www.example.com/img3.png||alt==this is an image||href==http://www.yahoo.com*|
*  $$]
s*/
$theRegex = '#\[\$\$([\r\n|\<br \/>]+CAROUSEL)(.*?)\$\$\]#miu';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){

        global $template;
        $carouselConfig = array();
        // Now it's time to find any [* ... *] subset
        $theRegex = '#\|\*(.*?)\*\|#ims';
        preg_match_all($theRegex, $theFirstMatch[2], $theSecondMatch);

        $i = 0;
        $items = array();
        foreach ($theSecondMatch[1] as $item){
            if ($i === 0){
                // The first line is the configuration
                $theLine = explode('||', $item);
                foreach ($theLine as $singleLine){
                    $theSegment = explode('==', $singleLine);
                    $carouselConfig[$theSegment[0]] = $theSegment[1];
                }
                $i++;
                continue;
            }else{
                $theLine = explode('||', $item);
                foreach ($theLine as $singleLine){

                    $theSegment = explode('==', $singleLine);
                    $carouselData[$theSegment[0]] = $theSegment[1];
                }
            }
            $items[] = array('src' => $carouselData['src'],
                'alt' => $carouselData['alt'],
                'href' => $carouselData['href']);

        }
        return $template->getCarousel(-1, $carouselConfig, $items);
    }, $content);