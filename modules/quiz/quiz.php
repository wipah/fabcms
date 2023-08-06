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

if (!$core->loaded) {
    die('Direct call detected');
}

// Include la classe "quiz" ed istanzia il relativo oggetto
include 'lib/class_quiz.php';
$quiz = new quiz();

// Include, se esiste, il file di configurazione
if (file_exists('config.php')){
    $debug->write('info','File di configurazione caricato','quiz');
    include 'config.php';
}else{
    $debug->write('info','File di configurazione non esistente','quiz');
}

switch ($path[2]){
    case 'scheda':
        include 'op_scheda.php';
        break;
    case 'mylog':
        include 'op_mylog.php';
        break;
    case 'getpastquiz':
        include 'ajax_get_past_quiz.php';
        break;
    default:
        include 'op_default.php';
        break;
}