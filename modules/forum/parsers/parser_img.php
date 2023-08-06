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

if (!$core->loaded)
    die('Direct call detected. IMG');

/*
         * Search and process the img code.
         * [$img src="theSource"|alt==Alt for the image||copyright==FabCMS||description==this is a simple description||class=test$]
         */
function forum_parser_img ($content){
    $theRegex = '/\[\$img src=([\a-z0-9\.\-\_\w\:\/]+)\|?(.*?)\$\]/miu'; // Removed [^\$\]] on the previous regex
    $content = preg_replace_callback($theRegex,
        function ($theFirstMatch) {
            global $URI;

            $imgSrc = $URI->getBaseUri(true) . $theFirstMatch[1];

            // Now, it's time to iterate
            if (isset($theFirstMatch[2])) {
                $theData = explode('||', $theFirstMatch[2]);

                foreach ($theData as $singleParam) {
                    $arrayFragment = explode('==', $singleParam);
                    switch ($arrayFragment[0]) {

                        case 'style':
                            $imgStyle = 'style= "' . $arrayFragment[1] . '"';
                            break;
                        case 'class':
                            $imgClass = 'class= "' . $arrayFragment[1] . '"';

                            break;
                        case 'quality':
                            $extension = pathinfo($imgSrc, PATHINFO_EXTENSION);
                            $pos = strrpos($imgSrc, '.' . $extension);

                            if ($pos !== false) {
                                if (strtolower($arrayFragment[1]) == 't')
                                    $imgSrc = substr_replace($imgSrc, '_thumb.' . $extension, $pos, strlen('.' . $extension));

                                if (strtolower($arrayFragment[1]) == 'm')
                                    $imgSrc = substr_replace($imgSrc, '_mq.' . $extension, $pos, strlen('.' . $extension));

                                if (strtolower($arrayFragment[1]) == 'l')
                                    $imgSrc = substr_replace($imgSrc, '_lq.' . $extension, $pos, strlen('.' . $extension));
                            }
                            break;
                        case 'alt':
                            $imgAlt = 'alt= "' . $arrayFragment[1] . '"';
                            break;
                        case 'license':
                            $imgLicense = $arrayFragment[1];
                            break;
                        case 'title':
                            $imgTitle = $arrayFragment[1];
                            break;
                        case 'description':
                            $imgDescription = '<div class="FabCMS-imageDescription">' . $arrayFragment[1] . '</div>';
                            break;
                        case 'simpleImage':
                            if ( (int) $arrayFragment[1] === 1 ){
                                $showSimpleImage = true;

                            } else{
                                $showSimpleImage = false;
                            }

                            break;
                        case 'align':
                            if ($arrayFragment[1] == 'left')
                                $imgAlign = 'float-left';
                            if ($arrayFragment[1] == 'right')
                                $imgAlign = 'float-right';
                            break;
                    }

                }
            }


            // $this->images[] = $imgSrc;
            // Here we go
            if ($showSimpleImage === true) {
                return "<img src='$imgSrc' $imgClass $imgStyle $imgAlt $imgTitle />";
            } else {
                return "<div class='FabCMS-imageContainer $imgAlign'>
                        <img src='$imgSrc' $imgClass $imgStyle $imgAlt $imgTitle />
                        $imgDescription
                    </div>";
            }

        }, $content);

    return $content;
}

