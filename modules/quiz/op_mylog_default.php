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

if (!$user->logged){
    echo 'Per utilizzare questo modulo devi essere registrato.';
    return;
}

$template->navBarAddItem('Quiz',$URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem('MyLog');

$template->sidebar .= $template->simpleBlock('MyQuiz', $quiz->showLatestLogs());

$this->addJsFile($URI->getBaseUri(true) . 'lib/datatables/js/jquery.dataTables.min.js');


$template->moduleH1 = '<h1 class="FabCMSH1">Registro schede</h1>';

echo '
<style type="text/css" title="currentStyle">
  @import "' . $URI->getBaseUri(true) . 'lib/datatables/css/jquery.dataTables.css";
</style>

<div id="pastQuiz">Caricamento in corso.</div>';


$script = '
$.post( "' . $URI->getBaseUri() . 'quiz/getpastquiz/", { name: "John", time: "2pm" })
    .done(function( data ) {
        $("#pastQuiz").html(data);
        $("#tableItems").dataTable();
    });';

$this->addScript($script);

$stats->write(['IDX' => $user->ID, 'module' => 'quiz', 'submodule' => 'myLogDefault']);