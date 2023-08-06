<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/09/2017
 * Time: 22:32
 */

if (!$core->loaded)
    die ('Direct call detected');

$theRegex = '#\[\$\$(BOX)?(.*)?\$\$\]#miu';

$content = preg_replace_callback($theRegex,
    function ($matches){
        $theRegex = '#\|\*(.*?)\*\|#ism';

        preg_match_all($theRegex, $matches[2], $theSecondMatch);
        $box = '<div style="border: 1px solid darkslategray; padding: 8px; background-color: #dff3ff" class="fabCMSBox">';

        $i = 0;
        foreach ($theSecondMatch[1] as $singleLine) {
            $i++;


            if ($i === 1) {
                $box .=  '<div style="background-color: #5840f3; border: 1px solid blue; color: white; font-size: 24px; padding: 6px;">' . $singleLine . '</div>';
                continue;
            }

            if ($i === 2) {
                $box .=  $singleLine;
                continue;
            }
        }
        $box .= '</div>';

        return $box;
    }, $content);