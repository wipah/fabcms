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

$theRegex = '/\[\$img src=([\a-z0-9\.\-\_\w\:\/]+)\|?(.*?)\$\]/miu'; // Removed [^\$\]] on the previous regex
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch) {
        global $template;
        global $URI;
        global $fabwiki;
        global $language;
        global $core;
        global $db;
        global $conf;
        global $user;
        global $relog;
        $imgSrc = $URI->getBaseUri(true) . $theFirstMatch[1];
        $imgClass = 'class= "lazy';
        // Now, it's time to iterate
        if (isset($theFirstMatch[2])) {
            $theData = explode('||', $theFirstMatch[2]);

            foreach ($theData as $singleParam) {
                $arrayFragment = explode('==', $singleParam);
                switch ($arrayFragment[0]) {

                    case 'ID':
                        $ID = (int)$arrayFragment[1];
                        break;
                    case 'style':
                        $imgStyle = 'style= "' . $arrayFragment[1] . '"';
                        break;
                    case 'class':
                        $imgClass .= ' ' . $arrayFragment[1] . '';

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
                        if ((int)$arrayFragment[1] === 1) {
                            $showSimpleImage = true;

                        } else {
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

        $imgClass .= '" ';
        $this->images[] = $imgSrc;

        /*
         * Trackback patch
         */
        $extension = pathinfo($theFirstMatch[1], PATHINFO_EXTENSION);

        $query = 'SELECT title,
                         trackback,
                         filename,
                         extension,
                         user_ID
                  FROM ' . $db->prefix . 'fabmedia
                  WHERE ID = ' . $ID ;


        $db->setQuery($query);

        try {
            $resultPatch = $db->executeQuery('select');
        } catch (Exception $e) {

            $relog->write(['type'      => '4',
                'module'    => 'wiki',
                'operation' => 'wiki_parser_img',
                'details'   => 'Patch did not worked. ' . "\r\n" . $query . "\r\n" .$query,
            ]);


            return;
        }

        if (!$db->numRows){
            echo 'No rows. ' . $query;
            return;
        }

        $row = mysqli_fetch_assoc($resultPatch);

        if ( empty($row['title']) || empty($row['trackback']) || empty($row['extension']) || empty($row['filename'])) {

            if (empty($row['title'])) {
                $title = basename(($theFirstMatch[1]));
            } else {
                $title = $row['title'];
            }

            $trackback  =   $core->getTrackback( basename($title));

            $fileSize   =   filesize($conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . basename($theFirstMatch[1]));

            $query = 'UPDATE ' . $db->prefix . 'fabmedia 
                      SET extension     = \'' . $extension . '\',
                          title         = \'' . $title . '\',
                          filename      = \'' . basename($theFirstMatch[1]) . '\',
                          trackback     = \'' . $trackback . '\',
                          size          = \'' . $fileSize . '\'
                      WHERE ID = \'' . $ID .'\'
                      LIMIT 1';

            $db->setQuery($query);

            if (!$db->executeQuery('update')){
                echo 'Query error.';
            }
        }
        // Here we go
        if ($showSimpleImage === true) {
            return "<img data-src='$imgSrc' $imgClass $imgStyle $imgAlt $imgTitle>";
        } else {
            $fragment = "<div class='FabCMS-imageContainer $imgAlign'>
                        <img data-src='$imgSrc' $imgClass $imgStyle $imgAlt $imgTitle>
                        $imgDescription
                    </div>";


            if ( (int) $this->config['showPageLicense'] === 1) {

                $query = 'SELECT trackback 
                          FROM ' . $db->prefix . 'fabmedia 
                          WHERE ID = ' . $ID . ' 
                          LIMIT 1';

                $db->setQuery($query);

                if (!$result = $db->executeQuery('select')){
                    echo 'Query error';
                    return;
                }

                if ($db->numRows) {
                    $row = mysqli_fetch_assoc($result);

                    $fragment .= '
                    <div class="fabCms-Wiki-ImageCopyrightNotice">' .
                        sprintf($language->get('wiki', 'showPageCopyrightImageNotice'),
                            $URI->getBaseUri() . 'media/showimage/' . $ID . '-' . $row['trackback'] . '/'
                        ) . '</div>';
                }

            }

            return $fragment;
        }

    }, $content);