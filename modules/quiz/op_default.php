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

$template->navBarAddItem('Quiz', $URI->getBaseUri() . $this->routed .'/');
$template->navBarAddItem('Homepage');

if ($user->logged){
    $template->sidebar .= $template->simpleBlock('MyQuiz', $quiz->showLatestLogs());
}else{
    $template->sidebar .= $template->simpleBlock('MyQuiz', '<a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/register/'. '">Registrati subito</a> per visualizzare le statistiche sulla tua preparazione.');
}


echo '<h1 class="FabCMSH1">Quiz</h1>';

echo '
Benvenuto nel modulo <b>quiz</b> del portale BiologiaWiki.it. In questa sezione puoi mettere alla prova le tue conoscenze su molte materie affrontate negli attuali corsi di laurea.

<div class="row">
    
    <div class="col-md-3" style="display:inline-block; vertical-align:middle;">
        <img class="center-block float-left" src="' . $URI->getBaseUri(true) . 'modules/quiz/layout/arrow.png" alt="Quiz">
    </div>
    
    <div class="col-md-9">
        <h4>Scheda</h4> Quiz a scheda, per simulare i quiz di un esame universitario. <br />
        <span class="float-right">
        
            <a href="' . $URI->getBaseUri()  . 'quiz/scheda/">
                Fai il quiz adesso
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            </a>
        </span>
    </div>
</div>';

if ($user->logged) {
    echo '<h2>Quiz precedentemente svolti</h2><div id="pastQuiz">Caricamento in corso.</div>';

    $script = '
$.post( "' . $URI->getBaseUri() . 'quiz/getpastquiz/", { name: "John", time: "2pm" })
    .done(function( data ) {
        $("#pastQuiz").html(data);
        $("#tableItems").dataTable();
    });';

    $this->addScript($script);


}
