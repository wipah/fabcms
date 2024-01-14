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
    die('Direct call detected. tabs.');


$pattern = "//";
preg_match($pattern, $testo, $matches);

if (!empty($matches)) {
    // Estrai l'ID del video dalla corrispondenza
    $videoID = $matches[1];

    // Genera l'HTML5 per la riproduzione del video
    $html5Video = "<video width='640' height='360' controls>";
    $html5Video .= "<source src='url_del_tuo_video/$videoID.mp4' type='video/mp4'>";
    $html5Video .= "Il tuo browser non supporta la riproduzione video.";
    $html5Video .= "</video>";

    // Sostituisci il tag nel testo con l'HTML5 video
    $testo = preg_replace($pattern, $html5Video, $testo);
}


$theRegex = '#\[video id==(\d+)\]#mis';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch) {
        global $template;
        $videoID = $matches[1];

        $html5Video = "<video width='640' height='360' controls>";
        $html5Video .= "<source src='url_del_tuo_video/$videoID.mp4' type='video/mp4'>";
        $html5Video .= "Il tuo browser non supporta la riproduzione video.";
        $html5Video .= "</video>";

        return $html5Video;
    }, $content);