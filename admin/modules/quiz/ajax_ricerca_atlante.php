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

$this->noTemplateParse = TRUE;
include_once ($conf['path']['baseDir'] . 'modules/quiz/config.php');

if (!isset($_POST['termine']) || strlen($_POST['termine']) <= 3){
    echo '<div style="border:1px solid red; padding:4px; background-color:#FFC0C0" class="ui-corner-all">Nessun termine passato, o termine inferiore a tre lettere.</div>';
    return;
}

$termine = $core->in($_POST['termine'], true);

$query = '
SELECT * FROM '. $db->prefix .'appunti
WHERE articolo LIKE \'%'.$termine.'%\'
';


$query = "SELECT * " .
    "FROM {$db->prefix}wiki_pages " .
    "WHERE {$db->buildExtendedQuery($termine, 'title',' ', 'OR')}" .
    " OR {$db->buildExtendedQuery($termine, 'content',' ', 'OR')}" .
    " ORDER BY (NOT ({$db->buildExtendedQuery($termine, 'title',' ', 'OR')}))" .
    "AND visible = 1;";

if (!$result = $db->query($query)) {
    echo 'QUERY ERROR. ' . $query;
    return;
}

if (!$db->affected_rows) {
    echo '<br/>No result';
}
else {
    while ($row = mysqli_fetch_array($result)) {
        echo '&bull;<a onclick="aggiungiPagina(\''.$row['ID'].'\',\''.str_replace('\'','\\\'',$row['title']).'\')">' . $row['title'] . '</a><br/>';
    }
}

