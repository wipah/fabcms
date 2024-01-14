<?php

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
                        $ID = (int) $arrayFragment[1];
                        break;
                    case 'style':
                        $imgStyle = 'style= "' . $arrayFragment[1] . '"';
                        break;
                    case 'class':
                        if ($this->renderType === 0) {
                            $imgClass .= ' ' . $arrayFragment[1] . '';
                        } else {
                            $imgClass = '';
                        }

                        break;
                    case 'quality':
                        if ($this->renderType === 0 ){
                            $extension = pathinfo($imgSrc, PATHINFO_EXTENSION);
                            $pos = strrpos($imgSrc, '.' . $extension);

                            if ($pos !== false) {
                                if (strtolower($arrayFragment[1]) == 't')
                                    $imgSrc = substr_replace($imgSrc, '_thumb.' . $extension, $pos, strlen('.' . $extension));

                                if (strtolower($arrayFragment[1]) == 'm')
                                    $imgSrc = substr_replace($imgSrc, '_mq.' . $extension, $pos, strlen('.' . $extension));

                                if (strtolower($arrayFragment[1]) == 'l')
                                    $imgSrc = substr_replace($imgSrc, '_lq.' . $extension, $pos, strlen('.' . $extension));
                            } else {
                                $imgSrc = substr_replace($imgSrc, '_original.' . $extension, $pos, strlen('.' . $extension));
                            }
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

        switch ($this->renderType) {
            case 0:
            default:
                $src = 'src="' . $imgSrc . '"';
                break;
            case 1:
                $src = 'src="' . $imgSrc . '"';
                break;
        }


        // Here we go
        if ($showSimpleImage === true || $this->renderType === 1) {
            if ($this->renderType === 1)
                $description = '<br/>' . $imgDescription;

            return "<img loading='lazy' $src $imgClass $imgStyle $imgAlt $imgTitle>$description";
        } else {
            $fragment = "<div class='FabCMS-imageContainer $imgAlign'>
                        <img loading='lazy' $src $imgClass $imgStyle $imgAlt $imgTitle>
                        $imgDescription
                    </div>";

            if ( (int) $this->config['showPageLicense'] === 1) {

                $query = 'SELECT trackback 
                          FROM ' . $db->prefix . 'fabmedia 
                          WHERE ID = ' . $ID . ' 
                          LIMIT 1';

                if (!$result = $db->query($query)){
                    echo 'Query error';
                    return;
                }

                if ($db->affected_rows) {
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