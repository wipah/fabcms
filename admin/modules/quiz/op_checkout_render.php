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

//include $conf['path']['baseDir'] . 'lib/dompdf/dompdf_config.inc.php';
ob_start();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<title>Generation</title>
<style type="text/css">
html{
    font-size: 11.5px;
    font-family: garamond;
}

</style>
</head>
<body>

<p style="text-align: justify;">La <strong>Fisiologia Vegetale</strong> &egrave; una Disciplina presente in numerosi corsi di Laurea, triennali e magistrali, con differenti organizzazioni. Scienze Biologiche, Scienze Naturali e Scienze agrarie sono soltanto alcuni corsi di Laurea che adottano dei programmi, pi&ugrave; o meno corposi, inerenti alla Fisiologia Vegetale. Non stupisce che, vista la complessit&agrave; degli argomenti trattati, e il numero degli stessi, il campo della Fisiologia Vegetale pu&ograve; essere ostico da percorrere e da affrontare.</p>
<p style="text-align: justify;">Questa raccolta di domande &egrave; stata concepita e realizzata per aiutare lo studente di Fisiologia Vegetale dei Corsi di Laurea Magistrali nella auto-valutazione delle proprie conoscenze, in base all\'analisi dei Programmi di alcune Universit&agrave; Italiane. &Egrave; un progetto che accompagna il gi&agrave; nutrito "Percorso di Fisiologia Vegetale", proposto nel portale LaCellula.net (<a href="http://www.lacellula.net/pagine/portale:fisiologia_vegetale">http://www.lacellula.net/pagine/portale:fisiologia_vegetale</a>).</p>
<p>La struttura di questa raccolta di domande &egrave; molto semplice e linare. Sono presenti undici schede differenti, con dieci domande per ciascuna scheda, per un totale di centodieci domande. Lo studente pu&ograve;, semplicemente rispondendo alle domande, valutare la preparazione e approfondire gli argomenti delle domande mediante un gran numero di link di pagine di approfondimento.</p>
<h2>Risorse collegate</h2>
<p>Sono molti gli strumenti che possono essere accompagnati a questa raccolta di domande. LaCellula.net &egrave; il portale di riferimento per gli studenti di Biologia, Medicina, Chimica e&nbsp;<span class="testo">&ndash;</span> genericamente&nbsp;<span class="testo">&ndash;</span> di qualsiasi Facolt&agrave; con attinenze alla Scienza. All\'interno del portale &egrave; presente una ricca raccolta di pagine che trattano argomenti di Fisiologia Vegetale (<a href="http://www.lacellula.net/pagine/portale:fisiologia_vegetale">http://www.lacellula.net/pagine/portale:fisiologia_vegetale</a>) e un forum di discussione (<a href="http://forum.lacellula.net">http://forum.lacellula.net</a>). Altri quiz e schede di valutazione sono reperibili all\'indirizzo <a href="http://www.lacellula.net/quiz/">http://www.lacellula.net/quiz/</a>.</p>
<h2>Errori</h2>
<p>Questa raccolta di domande è stata accuratamente creata e controllata; tuttavia è possibile che piccole imperfezioni possano essere sfuggite e, di conseguenza, presenti all\'iterno delle domande o delle risposte. Qualora voleste segnalare un errore potete utilizzare il modulo di contatto del portale LaCellula.net presente all\'indirizzo <a href="http://www.lacellula.net/contacts/">http://www.lacellula.net/contacts/</a></p>
<h2>Nota di copyright</h2>
<p>La presente Opera &egrave; protetta dalle vigenti Leggi in materia di Diritto D\'autore, non &egrave; consentita la divulgazione e la duplicazione in alcuna forma o qualsiasi utilizzo differente dai fini personali, ad esempio commerciale, di rivendita o di noleggio, senza un permesso scritto dall\'Autore dell\'Opera nonch&eacute; detentore dei Diritti su di essa.</p>
<p>&nbsp;</p>

<table border="0" style="page-break-after:always"><tr><td></td></tr></table>
';

// Variables to be imported
$ID_categoria = 2;
$maxQuestions = 110;
$questionsPerPage = 10;
$contentsAtlas = 'pages';
$language = 'it';

$query = '
SELECT *
FROM ' . $db->prefix . 'quiz_questions
WHERE
    categorie LIKE \'%|' . $ID_categoria . '|%\'
    OR categorie LIKE \'%|' . $ID_categoria . '\'
    OR categorie LIKE \'' . $ID_categoria . '|%\'
    OR categorie  = \'' . $ID_categoria . '\'
ORDER BY RAND()
' . ($maxQuestions > 0 ? 'LIMIT ' . (int) $maxQuestions : '') .'
';

$db->setQuery($query);
if (!$db->executeQuery('select')) {
    echo 'Errore nella query';
    return;
}

$ids = '';
$questions = array();
$correctAnswers = array();
$answersReview = array();

$numPage = 0;
$progressiveQuestion = 11;

while ($linea = mysqli_fetch_array($db->getResultAsObject())) {

    if ($progressiveQuestion >= $questionsPerPage){
        $numPage++;

        if ($numPage !== 1){
                echo '<table border="0" style="page-break-after:always"><tr><td></td></tr></table>';

        };

        echo '<h1>Scheda N.' . $numPage . '</h1>';
        $progressiveQuestion = 1;

    }else{
        $progressiveQuestion++;
    }

    $questions .= $linea['ID'];
    $risposte = array();

    $ids .= $linea['ID'];

    echo '
        <div style="border:1px solid black; padding: 1px; margin-top:4px; page-break-inside:avoid !important; ">
            <div style="margin-top:-10px; padding-left:2px;">
                <div style="float:left; padding: 1px; font-size:22px; border:1px solid black; width:65px; text-align: center; background-color: ' . $background . '">
                ' . $numPage . '-' . $progressiveQuestion . '
                </div>

                <div style="margin-left:70px"><span style="font-size:12px">' .
                    $linea['domanda'] . '</span>
                </div>
            </div>
        ';


    $risposte[] = '__CORRECT__' . $linea['risposta_1'];
    $risposte[] = $linea['risposta_2'];
    $risposte[] = $linea['risposta_3'];
    $risposte[] = $linea['risposta_4'];

    $answersReview[] = $linea['pagine'];

    shuffle($risposte);

    $i = 0;
    $x = 0;

    foreach ($risposte as $risposta) {
        $i === 0 ? ($bgColor = "#ddFFDD") : ($bgColor = "#FFFFE5"); // The italian way
        $i === 0 ? ($i = 1) : ($i = 0); //

        switch ($x){
            case 0:
                $questionLetter = 'A';
                $correctTemp = 'A';
                break;
            case 1:
                $questionLetter = 'B';
                $correctTemp = 'B';
                break;
            case 2:
                $questionLetter = 'C';
                $correctTemp = 'C';
                break;
            case 3:
                $questionLetter = 'D';
                $correctTemp = 'D';
                break;
        }

        if (false !== strpos($risposta, '__CORRECT__')){
            $risposta = str_replace('__CORRECT__', '', $risposta);
            $correctAnswers[] = $correctTemp;
        }

        $x++;


        echo '
        <div style="clear:left"></div>

        <div style="background-color:' . $bgColor . ';min-height:30px; padding:0px">
            <div style="font-size:18px; text-align:center; float:left; width:30px; min-height: 30px;">' . $questionLetter . '</div>
            <div style="border-left: 1px solid black; padding-left: 4px; min-height: 30px; margin-left:45px;">' . $risposta . '</div>
            <div style="clear:left"></div>
        </div>';
    }


    echo '</div>';
}

$page     = 1;
$progressiveQuestion = 1;
foreach ($correctAnswers as $correctAnswer){

    $correctArray[$page][] = $progressiveQuestion . ': ' . $correctAnswer;

    $progressiveQuestion++;
    $totalProgressive++;
    if ($progressiveQuestion > $questionsPerPage){
        $progressiveQuestion = 1;
        $page++;
    }
}

// Page break
echo '<table border="0" style="page-break-after:always"><tr><td></td></tr></table>';

$totalProgressive = 0;
foreach ($correctArray as $page => $questions ){

    echo '<h1>Risposte alla scheda ' . $page . '</h1>';

    echo '
        <div class="theTable">
        <table style="width:100%;" border="0">
            <thead>
                <tr>
                    <th style="width:50px;background-color: #004200; color:white;">Domande</th>
                    <th style="background-color: #004200; color:white;">Link di approfondimento</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($questions as $singleAnswer){

        echo '<tr>
            <td style="width:50px; border-bottom:1px solid black; background-color: #EFFFEF">' . $singleAnswer . '</td>
            <td style="border-bottom:1px solid black; background-color: #EBFFEB">';

        // Loading answers from the module contents
        $pageIDS = $answersReview[$totalProgressive];
        if (strlen($pageIDS > 0)){
            $pageIDS = explode('|', $pageIDS);

            $query = 'SELECT * FROM ' . $db->prefix . 'contents_' . $contentsAtlas . ' WHERE ';

            foreach ($pageIDS as $singleID){
                $query .= ' ID = "' . $singleID . '" OR';
            }

            $query = substr($query, 0, -3);


            $db->setQuery($query);
            if (!$result = $db->executeQuery('select')){
                echo 'Query error: ' . $query;
            }else{
                while ($row = mysqli_fetch_array($db->getResultAsObject())){
                    echo '&bull; <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('contents', $language) . '/' . $row['name_link_' . $language] . '/">' . $row['name_' . $language] . '</a> (' . $URI->getBaseUri() . $core->router->getRewriteAlias('contents', $language) . '/' . $row['name_link_' . $language]  . '/) <br/>';
                }
            }
        }

        $totalProgressive++;
    }

    echo '&nbsp;</td>
        </tbody>
        </table>
        </div>
        <br/>';

}


echo '
        <table border="0" style="page-break-after:always"><tr><td></td></tr></table>
        <em>Grazie per aver utilizzato questo Testo. Spero, sinceramente, che ti sia servito. <br/>Fabrizio</em>
        <br/>
        <br/>
        <br/>
        <br/>
        <div style="margin: 0pt auto; text-align: center">
            <em>Copyright (&copy;) 2013 Fabrizio Crisafulli / LaCellula.net. <br/> Libro pubblicato a cura dell\'autore.</em>
        </div>
    </body>
</html>';

$theOutput = ob_get_contents();
ob_end_clean();


echo $theOutput;
return;
$dompdf = new DOMPDF();
$dompdf->load_html($theOutput);
$dompdf->render();
$dompdf->stream("sample.pdf");
