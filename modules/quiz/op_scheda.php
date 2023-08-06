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

// Se non è stato passato l'ID della categoria apre la maschera di selezione
if (!isset($path[3])) {
    $template->navBarAddItem('Quiz', $URI->getBaseUri() . $this->routed .'/');
    $template->navBarAddItem('Scheda');

    $this->addTitleTag('Quiz universitari');

    // Crea la query
    $query = '
    SELECT * FROM
        ' . $db->prefix . 'quiz_categories
    WHERE lang = \'' . $core->shortCodeLang . '\'
    AND visibile = \'1\';';
    $db->setQuery($query);
    if (!$db->executeQuery()) {
        echo 'Errore nella query';
        return;
    }

    $this->addMetaData('description', 'Valutazione universitaria online: approfondisci le tue conoscenze con i quiz di preparazione per Biologia, Chimica, Scienze, Farmacia e altre facoltà.');

    echo '<h1>Selezione categoria di quiz</h1>';

    $result = $db->getResultAsObject();
    $i = 0;
    while ($linea = mysqli_fetch_array($result)) {
        echo '
        <div style="padding:6px; border:1px solid var(--fabCMS-secondary);" class="row mt-3">
            <div class="col-md-2">
                <img alt="Icona ' . $core->getTrackback($linea['nome']) . '" 
                     class="img-fluid d-none d-sm-block" 
                     src="' . $URI->getBaseUri(true) . 'modules/quiz/icons/' . $linea['icon'] . '.png">
            </div>
            
            <div class="col-md-10">
                <a href="' . $URI->getBaseUri() . $this->routed . '/scheda/' . $linea['ID'] . '-' . $core->getTrackback($linea['nome']) . '/">Quiz di ' . $linea['nome'] . '</a> (<i>' . $linea['schede_fatte'] . ' schede fatte in totale</i>). ' . $linea['short_description'] .
                '<p>'. $linea['long_description'] . '</p>
            </div>
        </div>';
        $i++;

        if ($i === 2) {
            echo '
            <div class="row">
                <div clas="col-md-12">
                    ' . $conf['quiz']['banner']['formSelectionBetweenSecondAndThirdCategory'] . '
                </div>
            </div>';
        }
    }

    $stats->write(['IDX' => 0, 'module' => 'quiz', 'submodule' => 'showForms']);
    return;
}

$this->addJsFile("https://connect.facebook.net/en_US/all.js");

// Ottiene l'ID
$catArray = explode('-', $path[3]);

$ID_categoria = (int )$catArray[0];
$nome_categoria = (str_replace('_', ' ', $core->in($catArray[1], true)));


// Crea una query ed ottiene il nome della categoria. Questo per prevenire attacchi XSS;
$query = '
SELECT * FROM
' . $db->prefix . 'quiz_categories
WHERE ID = \'' . $ID_categoria . '\'
AND visibile = \''. 1 . '\'
LIMIT 1;
';
$db->setQuery($query);
$db->executeQuery('SELECT');
if (!$db->affected_rows) {
    echo 'Errore interno in fase di selezione';
    return;
}

if (!$db->affected_rows){
    echo 'La categoria non esiste oppure non &egrave;, visibile.';
    return;
}

$linea = $db->getResultAsArray();
$nome_categoria = $core->getTrackback($linea['nome']);
$nome = $linea['nome'];
$this->addTitleTag($linea['title']);
$this->head .= '<meta name="description" content="' . $linea['meta_description'] . '" />';

$template->navBarAddItem('Quiz',  $URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem('Scheda');

echo '<h1>Quiz di ' . $nome . '. Esito scheda</h1>
      <div class="row">
        <div class="col-sm-12"> 
            <div style="background-color:#DCDCDC; padding: 12px"> ' . $linea['long_description'] .  '</div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">' . $conf['quiz']['banner']['formUnderTheDescription'] . '</div>
      </div>';

//======================= RISPOSTE
if ($path[4] == 'risposte') {
    $corrette = array();
    $nonCorrette = array();
    $nonDate = array();

    // First check
    if (!isset($_POST['darray'])) {
        echo 'Errore nel primo check di sicurezza';
        return;
    }

    // Second check
    $secure = md5($conf['security']['siteKey'] . $ID_categoria . date('Y-m-d'));
    if ($secure !== $_POST['s']){
        echo '<div class="ui-state-error">Security check failed. MSM-1</div>';
        $log->write('quiz_hack','quiz', 'First check mismatch. S1: ' . $core->in($_POST['s1']) . ', S2: ' . $core->in($_POST['s2'] ));
        return;
    }

    if ($_POST['s2'] !== md5($_POST['s'])){
        echo '<div class="ui-state-error">Security check failed. MSM-2</div>';
        $log->write('quiz_hack','quiz', 'Second check mismatch. S1: ' . $core->in($_POST['s1']) . ', S2: ' . $core->in($_POST['s2'] ));
        return;
    }
    // Aggiunge una scheda fatta
    $query = '
    UPDATE ' . $db->prefix . 'quiz_categories
    SET schede_fatte = schede_fatte + 1
    WHERE ID = \'' . $ID_categoria . '\'
    LIMIT 1;
    ';
    $db->setQuery($query);
    $db->executeQuery('update');
    $debug->write('info', 'Aggiunta una nuova scheda nel campo delle statistiche');

    $ids = '';
    foreach ($_POST['darray'] as $item) {
        $ids .= $item;

        if (!isset($_POST['q' . $item])) {
            $nonDate[] = (int) $item;
            continue;
        }
        if ($quiz->controllaDomanda($item, $_POST['q' . $item]) === true) {
            $corrette[] = (int) $item;
        } else {
            $nonCorrette[] = (int) $item;
        }
    }

    // Security check
    if (md5($ids . 'roberta') !== $_POST['secHash']) {
        echo 'Errore di sicurezza interno.';
        return;
    }

    $punti = (count($corrette) * 3) - (count($nonCorrette));

    // Log
    $log->write('quiz_completed','quiz','Points: ' . $punti . ', OK: ' . count($corrette) . ', KO: ' . count($nonCorrette) . ', BLANK: ' . count($nonDate) );

    echo '
    <h2>Risultati del quiz</h2>
    <div class="row">
        <div class="col-md-4">
            ' . $conf['quiz']['banner']['schedaRisposteBannerlaterale'] . '
        </div>
        
        <div class="col-md-4" style="">
            <span style="text-align: center!important;">
                <a href="javascript:fbPost();"><img src="' . $URI->getBaseUri(true) . '/modules/quiz/layout/fb_50.png" alt="Facebook post"></a><br/>
                Condividi il risultato su FaceBook
            </span>
        </div>
        
        <div class="col-md-4" style="background-color: #baffbd; padding: 12px;">
           <strong>Punteggio: ' . $punti . '</strong> <br/>
           <em>Corrette</em>:     ' . count($corrette) . ' <br/>
           <em>Non corrette</em>: ' . count($nonCorrette) . ' <br/>
           <em>Non risposte</em>: ' . count($nonDate) . ' <br/>
        </div>
        
        

        
    </div>';

    if (!$user->logged){
        echo '<div style="border:1px solid gray; padding: 8px; background-color: #edfaff; margin-top: 24px; margin-bottom: 24px;">
                Migliora la tua preparazione. <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('user') . '/register/'. '">Registrati</a> subito per tenere traccia di tutti i tuoi quiz.
              </div>  ';
    }
    echo        '

	<div id="fb-root"></div>';
$theScript = '
         FB.init({
            appId:\'124984864761131\', cookie:true,
            status:true, xfbml:true
         });
		 function fbPost(){
            FB.ui(
                {
                    name: \'BiologiaWiki QUIZ\',
                    link: \'' . $URI->getBaseUri() . $this->routed . '/\',
                    method: \'feed\',
                    caption: \'Valutazione del quiz\',
                    picture: \'https://biologiawiki.it/modules/quiz/layout/thumb_fb.png\',
                    description: \'Ho appena fatto un quiz di ' . $nome_categoria . '. La mia valutazione è stata di ' . $punti . '/30! E tu, quanto ne sai di ' . $nome . '?\',
                    message: \'\'
                },
                    function response(){
                    }
                );
		 }
    ';
$this->addScript($theScript);

    /*******************/
    /* Risposte errate */
    /*******************/

    echo '
        <div class="row">
            <div class="col-md-12">
                <h3>Risposte errate</h3>';

    if (count($nonCorrette) > 0) {
        // Crea la query
        $query = '
        SELECT *
        FROM ' . $db->prefix . 'quiz_questions
        WHERE 
        ';
        foreach ($nonCorrette as $item) {
            $item = (int)$item;
            $query .= 'ID = ' . $item . ' OR ';
        }
        $query = substr($query, 0, -3);

        $db->setQuery($query);
        $db->executeQuery('select');
        $risultato = $db->getResultAsObject();
        while ($linea = mysqli_fetch_array($risultato)) {

            // Pagine
            $pagine = '';
            if (strlen($linea['pagine']) > 1) {
                $pagine .= '<div style="border:1px solid black; background-color:#F0F0F0; padding:4px" class="ui-corner-all">Per maggiori informazioni sull\'argomenti puoi consultare le seguenti pagine: ';
                $pagineID = explode('|', $linea['pagine']);
                $query = '
                    SELECT 
                        W.ID, 
                        W.title,
                        W.trackback
                    FROM ' . $db->prefix . 'wiki_pages AS W
                    WHERE ';
                foreach ($pagineID as $ID) {
                    $query .= 'W.ID = \'' . $ID . '\' OR ';
                }

                $query = substr($query, 0, -3);

                $db->setQuery($query);
                $db->executeQuery('select');
                $resultpagine = $db->getResultAsObject();
                while ($linea2 = mysqli_fetch_array($resultpagine)) {
                    $pagine .= '<a target="_blank" href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $linea2['trackback'] . '/">' . $linea2['title'] . '</a> - ';
                }
                $pagine .= '</div>';
            } else {
                $pagine .= '<!-- Al momento non sono disponibili risorse per approfondire la domanda. -->
                    <div style="clear:left;"></div>';
            }

            echo '
            <div style="border:1px solid black; padding: 12px; margin-top:12px" class="ui-corner-all">
                <div style="border-bottom: 1px solid #DDD">' . $linea['domanda'] . '</div>'
                . '<div style="margin-top: 8px;">' .  str_replace('<p>','<p><span color="green">&#10004;</span> ', $linea['risposta_1']) . '</div>
                ' . $appunti . ' <br />
                ' . $pagine . '

            </div>
            ';
        }
    } else {
        echo 'Non hai commesso alcun errore nelle risposte date.';
    }

    echo '</div> <!-- /col -->
    </div> <!-- /row -->';


    /*
     * RISPOSTE NON DATE
     */

    echo '
<div class="row">
    <div class="col-md-12">
        <h3>Risposte non date</h3>
    ';

    if (count($nonDate) > 0) {
        // Crea la query
        $query = '
        SELECT *
        FROM ' . $db->prefix . 'quiz_questions
        WHERE 
        ';
        foreach ($nonDate as $item) {
            $item = (int)$item;
            $query .= 'ID = ' . $item . ' OR ';
        }
        $query = substr($query, 0, -3);

        $db->setQuery($query);
        $db->executeQuery('select');
        $risultato = $db->getResultAsObject();

        while ($linea = mysqli_fetch_array($risultato)) {
            // Pagine
            $pagine = '';
            if (strlen($linea['pagine']) > 1) {
                $pagine .= '<div style="border:1px solid black; background-color:#F0F0F0;padding:4px">Per maggiori informazioni sull\'argomenti puoi consultare le seguenti pagine:';
                $pagineID = explode('|', $linea['pagine']);
                $query = '
                    SELECT
                        W.ID,
                        W.title,
                        W.trackback
                    FROM ' . $db->prefix . 'wiki_pages AS W
                    WHERE ';
                foreach ($pagineID as $ID) {
                    $query .= 'W.ID = \'' . $ID . '\' OR ';
                }

                $query = substr($query, 0, -3);

                $db->setQuery($query);
                $db->executeQuery('select');
                $resultpagine = $db->getResultAsObject();
                while ($linea2 = mysqli_fetch_array($resultpagine)) {
                    $pagine .= ' <a target="_blank" href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $linea2['trackback'] .'/">' . $linea2['title'] . '</a> - ';
                }
                $pagine .= '</div>';
            } else {
                $pagine .= '<!-- Al momento non sono disponibili risorse per approfondire la domanda. -->
                    Non sono al momento disponibili risorse.';
            }

            echo '
            <div style="border:1px solid black; padding:12px; margin-top:12px">
                <div style="border-bottom: 1px solid #EEE;">' . $linea['domanda'] . '</div>
                
                <div> ' . str_replace('<p>','<p><span color="green">&#10004;</span> ', $linea['risposta_1']) . '</div>
                ' . $pagine . '

            </div>
            ';
        }
    } else {
        echo 'Hai risposto a tutte le domande.';
    }
    echo '</div>
    </div>';

    $ID_categoria = (int)($path[3]);

    // Ottiene il nome della categoria
    $query = '
    SELECT * FROM
    ' . $db->prefix . 'quiz_categories
    WHERE ID = \'' . $ID_categoria . '\'
    LIMIT 1;
    ';

    $db->setQuery($query);
    $db->executeQuery('select');

    if (!$db->affected_rows) {
        echo 'Errore in selezione categoria';
        return;
    }

    $linea = $db->getResultAsArray();

    echo '
        <span class="float-right">
            <a href="' . $URI->getBaseUri() . $this->routed . '/scheda/' . $ID_categoria . '-' . $core->getTrackback($linea['nome']) . '/">Fai un\'altra scheda di valutazione di ' . $linea['nome'] . '.</a>
        </span>
            <br/>';

    if (!$user->isAdmin) {
        // Update delle domande, per fini statistici.
        $stats = '';
        foreach ($corrette AS $IDTemp){
            $stats = 'UPDATE ' . $db->prefix . 'quiz_questions SET ok = ok + 1, views = views + 1 WHERE ID = \'' . $IDTemp . '\'; ';
            $db->setQuery($stats);
            if (!$db->executeQuery('update')){
                echo 'Admin Message: query error. ' . $stats;
            }
        }

        foreach ($nonCorrette AS $IDTemp){
            $stats = 'UPDATE ' . $db->prefix . 'quiz_questions SET ko = ko + 1, views = views + 1 WHERE ID = \'' . $IDTemp . '\'; ';
            $db->setQuery($stats);
            //$db->executeQuery('update');

            if (!$db->executeQuery('update')){
                echo 'Admin Message: query error. ' . $stats;
            }
        }

        foreach ($nonDate AS $IDTemp){
            $stats = 'UPDATE ' . $db->prefix . 'quiz_questions SET blank = blank + 1, views = views + 1 WHERE ID = \'' . $IDTemp . '\'; ';
            $db->setQuery($stats);
            //$db->executeQuery('update');
            if (!$db->executeQuery('update')){
                echo 'Admin Message: query error. ' . $stats;
            }
        }


        $quiz->storeSession('scheda', $ID_categoria, $corrette, $nonCorrette, $nonDate);
    }

    return;
}
// ============ FINE RISPOSTE

// Seleziona 10 domande casuali
/*
 * 1|2|3|4|5|
 * 
 */

// Easter egg, used for debug and quality assurance test. If a $_GET "showAll" is passed
// the script takes ALL the questions from the database
if ($user->isAdmin && (int) $_GET['showAll'] == 1){ // Well, not a proper easter eggs due the admin limitation, but...
    $limit = '';
}else{
    $limit = 'LIMIT 10';
}

$query = '
SELECT * 
FROM ' . $db->prefix . 'quiz_questions
WHERE 
    categorie LIKE \'%|' . $ID_categoria . '|%\'
    OR categorie LIKE \'%|' . $ID_categoria . '\'
    OR categorie LIKE \'' . $ID_categoria . '|%\'
    OR categorie  = \'' . $ID_categoria . '\'
ORDER BY RAND()
' . $limit .'
';

$db->setQuery($query);
if (!$db->executeQuery('select')) {
    echo 'Errore nella query';
    return;
}

// Security code
$secure = md5($conf['security']['siteKey'] . $ID_categoria . date('Y-m-d'));

echo '
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/core.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/components/md5-min.js"></script>

<form method="post" action="' . $URI->getBaseUri() . $this->routed . '/scheda/' . $ID_categoria . '/risposte/">
<input type="hidden" name="s" id="s" value="' . $secure . '" />
<input type="hidden" name="s2" id="s2" value="" />

<script type="text/javascript">
    var hash = CryptoJS.MD5("' . $secure . '");
    var el = document.getElementById("s2");
    el.value = hash;
</script>';
$ids = '';
while ($linea = mysqli_fetch_array($db->getResultAsObject())) {
    $risposte = array();

    $ids .= $linea['ID'];

    $ok = round( ( (int) $linea['ok'] /  (int) $linea['views']) * 100 );
    $ko = round( ( (int) $linea['ko'] / (int) $linea['views']) * 100 ) ;
    $blank = round( ( (int) $linea['blank'] / (int) $linea['views']) * 100 );

    if ( (int) $linea['views'] == 0){
        $background = '#CCC';
    }elseif ($ok > $ko){
        $background = '#AFA';
    }elseif ($ko > $ok){
        $background = '#FAA';
    }else{
        $background = '#CCC';
    }

    echo '
        <div class="row mt-2" style="border: 1px solid gray;">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-sm-12 col-md-8" style="background-color: var(--fabCMS-tertiary); border-left: 4px solid var(--fabCMS-primary)">' . $linea['domanda'] . '</div>
                    <div class="col-md-4">
                        <small class="d-none d-sm-block">
                            <strong>Statistiche difficoltà</strong>: <span style="color:green">Corretta : ' . $ok . '%</span>, 
                                                                     <span style="color:red">errata : ' . $ko . '%</span>, 
                                                                                       bianca : ' . $blank . '%
                        </small>
                    </div>
                </div>
            </div>
         </div>';

    $risposte[] =  str_replace('<p>','<p>' . '<input onclick="switchState(\'' . $linea['ID'] . '-1\')" id="input-' . $linea['ID'] . '-1" style="" type="radio" value="' . $quiz->cifraCorretta($linea['ID']) . '" name="q' . $linea['ID'] . '" /> '    , '<span onclick="switchState(\'' . $linea['ID'] . '-1\')">' . $linea['risposta_1'] . '</span>');
    $risposte[] =  str_replace('<p>','<p>' . '<input onclick="switchState(\'' . $linea['ID'] . '-2\')" id="input-' . $linea['ID'] . '-2" style="" type="radio" value="' . $quiz->cifraNonCorretta($linea['ID']) . '" name="q' . $linea['ID'] . '" /> ' , '<span onclick="switchState(\'' . $linea['ID'] . '-2\')">' . $linea['risposta_2'] . '</span>');
    $risposte[] =  str_replace('<p>','<p>' . '<input onclick="switchState(\'' . $linea['ID'] . '-3\')" id="input-' . $linea['ID'] . '-3" style="" type="radio" value="' . $quiz->cifraNonCorretta($linea['ID']) . '" name="q' . $linea['ID'] . '" /> ' , '<span onclick="switchState(\'' . $linea['ID'] . '-3\')">' . $linea['risposta_3'] . '</span>');
    $risposte[] =  str_replace('<p>','<p>' . '<input onclick="switchState(\'' . $linea['ID'] . '-4\')" id="input-' . $linea['ID'] . '-4" style="" type="radio" value="' . $quiz->cifraNonCorretta($linea['ID']) . '" name="q' . $linea['ID'] . '" /> ' , '<span onclick="switchState(\'' . $linea['ID'] . '-4\')">' . $linea['risposta_4'] . '</span>');

    echo '<input type="hidden" value="' . $linea['ID'] . '" name="darray[]" />';

    shuffle($risposte);


    echo '<div class="row mt-2" style="border: 1px solid green; ">';
    $i = 0;
    foreach ($risposte as $risposta) {
        $i === 0 ? ($bgColor = "#EEFFEE") : ($bgColor = "#FFFFEC"); // The italian way
        $i === 0 ? ($i = 1) : ($i = 0); //
        echo '<div class="col-sm" style="background-color:' . $bgColor . '; padding: 8px">';
        echo $risposta;
        echo '</div> <!-- End row-->';

    }
    echo '</div>';



    echo '
            <div class="row mt-2" style="border:1px solid #AEF; background-color:#CEF">
                <div class="col-md-12">
                    <strong>Argomenti inerenti</strong>: ' . str_replace('|',', ', $linea['argomenti']) . '
                </div>
            </div>
     <!-- End row-->';

    if ($user->isAdmin)
        echo '<a href="'. $URI->getBaseUri(true) . 'admin/admin.php?module=quiz&op=editaDomanda&ID=' . $linea['ID'] . '">Edit question</a>';
}

echo '
<input type="hidden" name="secHash" value="' . md5($ids . 'roberta') . '">
<div style="margin-top: 12px;" class="float-right">
    <button type="submit" id="btnInvia">Valuta le risposte</button>
</div>
</form>';

$theScript = '
    $("#btnInvia").button();
    
    function switchState(inputID) {
        if ($("#input-" + inputID).is(":checked")){
            $("#input-" + inputID).prop("checked", false);
        } else {
            $("#input-" + inputID).prop("checked", true);
        }
    }
';

$this->addScript($theScript);

$stats->write(['IDX' => $ID_categoria, 'module' => 'quiz', 'submodule' => 'form']);