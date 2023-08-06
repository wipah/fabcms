<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/12/2016
 * Time: 15:08
 */

// Hooks (https://bitbucket.org/thewiper/fabcms/issue/8/sidebar-interactions && https://bitbucket.org/thewiper/fabcms/wiki/Hooks)
$theRegex = '#\[!hook=([a-z0-9\-\_]+)!\](.*?)\[!endhook!\]#mis';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;

        if (!is_array($theFirstMatch[0]))
            return;

        for ($i = 0; $i < count($theFirstMatch[0]); $i++){
            $template->hooks[] = $theFirstMatch[1];
            $template->hooksData[] = $theFirstMatch[2];
        }
    }, $content);
