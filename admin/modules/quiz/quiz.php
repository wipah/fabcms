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

if (!$core->adminLoaded) {
    die('Direct call detected');
}

switch ($_GET['op']){
    case 'categoria':
        include 'op_categoria.php';
        break;
    case 'nuova':
    case 'editaDomanda':
        include 'op_nuova.php';
        break;
    case 'config':
        include 'op_config.php';
        break;
    case 'ajaxSearch':
        include 'ajax_ricerca_domanda.php';
        break;
    case 'ricercaTermine':
        include 'ajax_ricerca_termine.php';
        break;
    case 'ricercaAtlante':
        include 'ajax_ricerca_atlante.php';
        break;
    case 'job':
        include 'op_jobs.php';
        break;
    default:
        include 'op_default.php';
        break;
}