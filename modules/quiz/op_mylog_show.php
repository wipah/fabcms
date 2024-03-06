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

$template->navBarAddItem('Quiz', $URI->getBaseUri() . $this->routed  . '/');
$template->navBarAddItem('MyLog', $URI->getBaseUri() . $this->routed  . '/mylog/');
$template->navBarAddItem('Visualizzazione scheda');

$template->sidebar .= $template->simpleBlock('MyQuiz', $quiz->showLatestLogs());

if (!isset($path[4])){
    echo 'Non è stato passato un valido identificativo';
}

$ID = (int) $path[4];
$log_ID = $ID;

$query = 'SELECT * FROM ' . $db->prefix . 'quiz_logs WHERE ID = \'' . $ID . '\' AND user_ID = \'' .  $user->ID .'\' LIMIT 1';


$result = $db->query($query);

if (!$db->affected_rows){
    echo 'Non risulta nessuna scheda.';
    return;
}

$row =  mysqli_fetch_array($result);

echo '<h1>Riepilogo schede di valutazione</h1>';

echo '
<p><em>Scheda fatta il ' . $core->getDateTime($row['date']) . '</em></p>';

if ($row['ok'] === ''){
    $percentuale = 0;
}else if (!substr_count($row['ok'],'|')){
    $percentuale = 10;
}else{
    $percentuale = (substr_count($row['ok'],'|') + 1) * 10;
}

if ($percentuale == 100){
    echo 'Complimenti! Hai risposto correttamente a tutte le domande!';
    return;
}
echo 'La percentuale di risposte corrette è stata del <strong>' . $percentuale . '</strong>%. Di seguito le risposte errate oppure non date.';

if (($row['ko'] != '') && ($row['blank'] != '') ){
    $domande = $row['ko'] . '|' . $row['blank'];
}elseif ( ($row['ko'] != '') && ($row['blank'] == '') ){
    $domande = $row['ko'];
}else{
    $domande = $row['blank'];
}

$domande = explode('|', $domande);

$query = 'SELECT * FROM ' . $db->prefix . 'quiz_questions WHERE ID = ';

foreach ($domande as $domanda){
    $query .= '\'' . $domanda . '\' OR ID = ';
}

$query = substr($query,0, -9);

$result = $db->query($query);

while ($row = mysqli_fetch_array($result)){

    // Pagine
    $pagine = '';
    if (strlen($row['pagine']) > 1) {
        $pagine .= '<div style="border:1px solid black; background-color:#F0F0F0;margin-left:72px;padding:4px" class="ui-corner-all">Per maggiori informazioni sull\'argomenti puoi consultare le seguenti pagine:';
        $pagineID = explode('|', $row['pagine']);
        $query = '
                    SELECT 
                        a.ID, 
                        a.title,
                        a.trackback
                    FROM ' . $db->prefix . 'wiki_pages AS a
                    WHERE ';
        foreach ($pagineID as $ID) {
            $query .= 'a.ID = \'' . $ID . '\' OR ';
        }

        $query = substr($query, 0, -3);

        
        $resultpagine = $db->query($query);

        while ($row2 = mysqli_fetch_array($resultpagine)) {
            $pagine .= '<a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $row2['trackback'] . '/">' . $row2['title'] . '</a> - ';
        }
        $pagine .= '</div>';
    } else {
        $pagine .= '<!-- Al momento non sono disponibili risorse per approfondire la domanda. -->
                    <div style="clear:left;"></div>';
    }

    echo '
            <div style="border:1px solid black; padding:4px; margin-top:12px" class="ui-corner-all">
                <div style="background-color: var(--fabCMS-tertiary); border-left: 4px solid var(--fabCMS-primary)">' . $row['domanda'] . '</div>
                    <img style = "float:left;margin-right:12px;" 
                         src = "' . $URI->getBaseUri(false) . 'modules/quiz/layout/arrow.png" 
                         border = "0" 
                         width = "68" 
                         height = "68" 
                         alt = "arrow.png" /> <span class="float-left" color="green">&#10004;</span> ' . $row['risposta_1'] . '
                
                ' . $pagine . '
                
            </div>
            ';
}


$stats->write(['IDX' => $log_ID, 'module' => 'quiz', 'submodule' => 'myLogShow']);