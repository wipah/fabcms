<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/09/2017
 * Time: 22:32
 */

if (!$core->loaded)
    die ('Direct call detected');

$theRegex = '#\[\$\$BIOBOX(.*)?\$\$\]#mius';

$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;
        global $conf;
        global $user;
        global $mobileDetect;

        $panelConfig = [];

        // Now it's time to find any [* ... *] subset
        $theRegex = '#\|\*(.*?)\*\|#mius';
        preg_match_all($theRegex, $theFirstMatch[1], $theSecondMatch);

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

        switch ( $panelConfig['type'] ) {
            case 'halfLeft':
                $class = 'bioBoxHalfLeft';
                break;
            case 'halfRight':
                $class = 'bioBoxHalfRight';
                break;
            default:
                $class = 'bioBoxFullBox';
                break;
        }

        if (isset($panelConfig['headType'])) {
            $headType = (int) $panelConfig['headType'];
        } else {
            $headType = 3;
        }

        if ($mobileDetect->isMobile()){
            $overFlow = 'overyflow-y: auto; max-heigth: 250px;';

        }
        return '<div class="' . $class . '" style="' . $overFlow . 'padding-top:12px; padding-bottom:12px; border-top: 1px solid #AAA; border-bottom: 1px solid #AAA;">
                    <h' . $headType . ' class="bioH' . $headType . '">' . $header . '</h' . $headType .'>
                        ' . $body . '
                </div>';

    }, $content);
