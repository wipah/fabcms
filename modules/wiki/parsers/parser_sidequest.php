<?php
/*
 * Usage: ==_SideQuest_==
 *
 */
$theRegex = '#\=\=\_(.*)?\_\=\=#mis';
$content = preg_replace_callback($theRegex,
    function ($matches){
        return '<p class="sideQuest">' . $matches[1] . '</p>';
    }, $content);