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

$template->navBar[] = '<a href="admin.php?module=quiz">Quiz</a>';
$template->navBar[] = '<em>Nuova domanda</em>';

echo '
<a name="top">
<a href="#searchWiki" style="float:right; margin-top:12px;">Cerca domanda &darr;</a>
<h1>Gestione domande</h1>';

include $conf['path']['baseDir'] . 'modules/quiz/config.php';

// Datatables
$this->addJsFile($URI->getBaseUri(true) . 'lib/datatables/js/jquery.dataTables.min.js');


switch ($_GET['op']) {
    case 'nuova':

        if (isset($_GET['salva'])) {

            $domanda = $core->in($_POST['domanda']);
            $corretta = $core->in($_POST['corretta']);
            $ris_2 = $core->in($_POST['ris_2']);
            $ris_3 = $core->in($_POST['ris_3']);
            $ris_4 = $core->in($_POST['ris_4']);
            $argomenti = $core->in($_POST['argomenti']);

            // Controlla gli appunti collegati
            if (!is_array($_POST['anarray'])) {
                $appunti = '|' . (int)$_POST['anarray'] . '|';
            } else {
                $appunti = '|';
                foreach ($_POST['anarray'] AS $appuntoID) {
                    $appunti .= $appuntoID . '|';
                }
            }
            $appunti = rtrim($appunti, '|');
            $appunti = ltrim($appunti, '|');

            // Controlla le pagine correlate
            if (!is_array($_POST['anarrayPagine'])) {
                $pagine = '|' . (int)$_POST['anarrayPagine'] . '|';
            } else {
                $pagine = '|';
                foreach ($_POST['anarrayPagine'] AS $paginaID) {
                    $pagine .= $paginaID . '|';
                }
            }
            $pagine = rtrim($pagine, '|');
            $pagine = ltrim($pagine, '|');


            if (!is_array($_POST['categorie'])) {
                $categorie = '|' . (int)$_POST['categorie'] . '|';
            } else {
                $categorie = '|';
                foreach ($_POST['categorie'] AS $cat) {
                    $categorie .= $cat . '|';
                }
            }

            $categorie = rtrim($categorie, '|');
            $categorie = ltrim($categorie, '|');

            $query = '
    INSERT INTO ' . $db->prefix . 'quiz_questions
    (domanda,risposta_1,risposta_2,risposta_3,risposta_4,categorie,argomenti,pagine)
    VALUES
    (
        \'' . $domanda . '\',
        \'' . $corretta . '\',
        \'' . $ris_2 . '\',
        \'' . $ris_3 . '\',
        \'' . $ris_4 . '\',
        \'' . $categorie . '\',
        \'' . $argomenti . '\',
        \'' . $pagine . '\'
    );
    ';

            if (!$db->query($query)) {
                $log->write('quiz_new_question_save_error','quiz', 'Query: ' . $query );
                echo 'Errore ' . $db->lastError . '<br/>' . $query;
                return;
            }

            echo '
            <div class="content-box">
                <div class="response-msg success ui-corner-all">
                    <span>La domanda è stata inserita con successo</span> <br/>
                    &bull; <a href="admin.php?module=quiz&op=nuova">Aggiungi una nuova domanda</a>;<br/>
                    &bull; <a href="admin.php?module=quiz&op=editaDomanda&ID=' . $db->lastInsertID . '">Modifica la domanda appena inserita
                </div>
            </div>';

            $log->write('quiz_new_question_save_ok','quiz', 'ID: ' . $db->lastInsertID);

            return;
        }


        $action = 'admin.php?module=quiz&op=nuova&salva';

        // Categoria nuova
        $query = 'SELECT * FROM ' . $db->prefix . 'quiz_categories ORDER BY nome ASC';

        $db->query($query);
        $cat = '';
        while ($linea = mysqli_fetch_array($db->getResultAsObject())) {
            $cat .= ' <input name="categorie[]" id="cat_' . $linea['ID'] . '" type="checkbox" value="' . $linea['ID'] . '"/>' . '<span onclick="check(\'' . $linea['ID'] . '\');">' . $linea['nome'] . '</span> ';
        }
        break;
    case 'editaDomanda':
        $ID = (int)$_GET['ID'];

        if (isset($_GET['salva'])) {

            if (!isset($_POST['dummy'])){
                echo 'Reload detected';
                return;
            }

            $domanda = $core->in($_POST['domanda']);
            $corretta = $core->in($_POST['corretta']);
            $ris_2 = $core->in($_POST['ris_2']);
            $ris_3 = $core->in($_POST['ris_3']);
            $ris_4 = $core->in($_POST['ris_4']);
            $argomenti = $core->in($_POST['argomenti']);

            if (!is_array($_POST['categorie'])) {
                $categorie = '|' . (int)$_POST['categorie'] . '|';
            } else {
                $categorie = '|';
                foreach ($_POST['categorie'] AS $cat) {
                    $categorie .= $cat . '|';
                }
            }

            $categorie = rtrim($categorie, '|');
            $categorie = ltrim($categorie, '|');


            // Controlla le pagine correlate
            if (!is_array($_POST['anarrayPagine'])) {
                $pagine = '|' . (int)$_POST['anarrayPagine'] . '|';
            } else {
                $pagine = '|';
                foreach ($_POST['anarrayPagine'] AS $paginaID) {
                    $pagine .= $paginaID . '|';
                }
            }
            $pagine = rtrim($pagine, '|');
            $pagine = ltrim($pagine, '|');


            $query = 'UPDATE ' . $db->prefix . 'quiz_questions
                      SET
                      domanda = \'' . $domanda . '\',
                      risposta_1 = \'' . $corretta . '\',
                      risposta_2 = \'' . $ris_2 . '\',
                      risposta_3 = \'' . $ris_3 . '\',
                      risposta_4 = \'' . $ris_4 . '\',
                      categorie = \'' . $categorie . '\',
                      argomenti = \'' . $argomenti . '\',
                      pagine = \'' . $pagine . '\'
                      WHERE ID = \'' . $ID . '\'
                      LIMIT 1;';

            $db->setQuery($query);
            if (!$db->executeQuery('insert')) {
                $log->write('quiz_new_question_modify_error','quiz', 'Query: ' . $query);
                echo 'errore' . $query;
                return;
            }
            $log->write('quiz_new_question_modify_ok','quiz', 'ID: ' . $ID);

            echo '
    La domanda è stata modificata con successo con successo!<br/>
    &bull; <a href="admin.php?module=quiz&op=nuova">Aggiungi una nuova domanda</a>;<br/>
    &bull; <a href="admin.php?module=quiz&op=editaDomanda&ID=' . $ID . '">Modifica la domanda appena modificata;';
            return;
        }

        $action = 'admin.php?module=quiz&op=editaDomanda&ID=' . $ID . '&salva';

        // Crea la query
        $query = '
        SELECT QUESTION.*
        FROM ' . $db->prefix . 'quiz_questions AS QUESTION
        WHERE QUESTION.ID = \'' . $ID . '\'
        LIMIT 1
        ';

        $db->setQuery($query);
        if (!$result = $db->executeQuery('select')) {
            echo 'Errore nella query.' . $query;
            return;
        }

        if (!$db->affected_rows) {
            echo 'La domanda non esiste';
            return;
        }

        $lineaDomanda = mysqli_fetch_assoc($result);
        $arrayCategorieDomanda = explode('|', $lineaDomanda['categorie']);

        // Categoria modifica
        $query = 'SELECT * FROM ' . $db->prefix . 'quiz_categories ORDER BY nome ASC';

        $db->setQuery($query);
        $resultCategories = $db->executeQuery('select');
        $cat = '';
        while ($linea = mysqli_fetch_assoc($resultCategories)) {
            $cat .= ' <input ' . (in_array($linea['ID'], $arrayCategorieDomanda) ? 'checked="checked"' : '') . ' name="categorie[]" id="cat_' . $linea['ID'] . '" type="checkbox" value="' . $linea['ID'] . '"/>' . '<span onclick="check(\'' . $linea['ID'] . '\');">' . $linea['nome'] . '</span> ';
        }


        // Pagine correlate
        if (!isset($lineaDomanda['pagine'])) {
            # Nessuna pagina
        } elseif (!strpos($lineaDomanda['pagine'], '|')) {
            $arrayPagine = array($lineaDomanda['pagine']);
        } else {
            $arrayPagine = array();
            $arrayTemp = explode('|', $lineaDomanda['pagine']);
            foreach ($arrayTemp AS $paginaID) {
                $arrayPagine[] = $paginaID;
            }
        }

        $pagineBox = '';
        if (is_array($arrayPagine)) {
            $queryWhere = ' WHERE ID = \'';
            foreach ($arrayPagine AS $paginaID) {
                $queryWhere .= $paginaID . '\' OR ID = \'';
            }
            $queryWhere = substr($queryWhere, 0, -9);
        } else {
            $queryWhere = ' WHERE ID = \'' . $lineaDomanda['pagine'];
        }

        $query = 'SELECT ID, 
                         title, 
                         trackback
                  FROM ' . $db->prefix . 'wiki_pages
        ' . $queryWhere . ';';


        $db->setQuery($query);
        if (!$resultPagine = $db->executeQuery('select')) {
            $pagineBox = 'Errore nella query ' . $query;

        } else {
            while ($lineaPagine = mysqli_fetch_assoc($resultPagine)) {

                $pagineBox .= '
                    <div id="div_' . $lineaPagine['ID'] . '" style="border: 1px solid black; padding: 2px; background-color: rgb(192, 255, 192); float: left;" class="ui-corner-all">
                                <input type="hidden" value="' . $lineaPagine['ID'] . '" name="anarrayPagine[]">
                                <input type="checkbox" value="' . $lineaPagine['ID'] . '" name="pagine" id="inputPagine_' . $lineaPagine['ID'] . '">' . $lineaPagine['title'] .
                    '</div>';
            }
        }

        break;
}



// Linking diretto per le ultime 10 pagine inserite
$query = '
SELECT PAGES.*
FROM ' . $db->prefix . 'wiki_pages PAGES
WHERE PAGES.visible = 1
ORDER BY ID DESC
LIMIT 10;
';
$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error.' . $query;
    return;
}

if (!$db->affected_rows) {
    $linkDirettoPagine = '<ul class="side-menu layout-options"><li>Non ci sono, ancora, pagine!</li></ul>';
} else {
    $linkDirettoPagine = '<ul class="side-menu layout-options">';
    while ($linea = mysqli_fetch_assoc($result)) {
        $linkDirettoPagine .= '<li>
                                    <a href="javascript:aggiungiPagina(' . $linea['ID'] . ',\'' . str_replace('\'', '\\\'', $linea['title']) . '\')">' . $linea['title'] . ' </a> - '.'
                               </li>';
    }
}
$linkDirettoPagine .= '</ul>';

echo '
<script type="text/javascript">
function check(ID){
    var cb = document.getElementById("cat_"+ID);
    if (cb.checked==true){
        cb.checked = false;
    }else{
        cb.checked = true;
    }
}
</script>';

// Ultime domande
$query = '
SELECT ID
FROM ' . $db->prefix . 'quiz_questions
ORDER BY ID desc
LIMIT 5
';
$db->setQuery($query);
$ultimeDomande = '<ul class="side-menu layout-options">';
if (!$db->executeQuery('select')) {
    $ultimeDomande .= '<li>Errore nella query</li>';
}
while ($linea = mysqli_fetch_array($db->getResultAsObject())) {
    $ultimeDomande .= '<li>
                         <a href="admin.php?module=quiz&op=editaDomanda&ID=' . $linea['ID'] . '">#' . $linea['ID'] . '</a>
                       </li>';
}
$ultimeDomande .= '</ul>';

// Conta il numero di domande presenti
$query = '
SELECT COUNT(ID) AS ricorrenze
FROM ' . $db->prefix . 'quiz_questions
';
$db->setQuery($query);
$db->executeQuery('select');
$linea = $db->getResultAsArray();

$statistiche = 'Ci sono <i>' . $linea['ricorrenze'] . '</i> domande inserite nel database.';

$template->sidebar .= '

					<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header">Link diretto pagine</div>
						<div class="portlet-content">
						' . $linkDirettoPagine . '
						</div>
					</div>
';


$template->sidebar .= $template->simpleBlock('Ultime domande', $ultimeDomande);
$template->sidebar .= $template->simpleBlock('Statistiche', $statistiche);

echo '

<form name="formDomanda" id="formDomanda" action="' . $action . '" method="post">
    <input type="hidden" name="dummy" id="dummy" value="dummiest" />
    <div style="font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Domanda:</b> <textarea class="tinyMCE" name="domanda" style="width:100%;" id="domanda">' . $lineaDomanda['domanda'] . '</textarea>
    </div>

    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Categorie:</b>' . $cat . '
    </div>

    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Argomenti:</b><input type="text" name="argomenti" id="argomenti" value="' . $lineaDomanda['argomenti'] . '"/>
    </div>
<!--
    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Appunti collegati</b> -
        <a onclick="apriDialog();">Aggiungi</a> -
        <div id="divCorrelati">' . $appuntiBox . '</div>
        <button type="button" onclick="rimuoviAppunti();">Elimina gli appunti</button>
    </div>
-->
    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Pagine collegate</b> -
        <a onclick="apriDialogAtlante();">Aggiungi</a> -
        <div id="divCorrelatiAtlanti">' . $pagineBox . '</div>
        <button type="button" onclick="rimuoviPagine();">Elimina le pagine</button>
    </div>

    <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#C0FFC0" class="ui-corner-all">
        <b>Corretta:</b> <textarea class="tinyMCE" id="corretta" style="width:100%; height:70px;" name="corretta">' . $lineaDomanda['risposta_1'] . '</textarea>
    </div>

    <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
        <b>#2</b> <textarea class="tinyMCE" id="ris_2" style="width:100%; height:70px;" name="ris_2">' . $lineaDomanda['risposta_2'] . '</textarea>
    </div>

    <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
        <b>#3</b> <textarea class="tinyMCE" id="ris_3" style="width:100%; height:70px;" name="ris_3">' . $lineaDomanda['risposta_3'] . '</textarea>
    </div>

    <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
        <b>#4</b> <textarea class="tinyMCE" id="ris_4" style="width:100%; height:70px;" name="ris_4">' . $lineaDomanda['risposta_4'] . '</textarea>
    </div>
    <button id="invia" type="button" onclick="checkForm();">Invia</button>
</form>

<div style="display:none;font-size:12px;" id="boxCollegati">
    <h3>Ricerca appunto</h3>
    Termine: <input type="text" id="termineRicerca" /">
    <button id="ricercaTermine" onclick="cercaTermineAppunti();">Ricerca</button>
    <h3>Risultati</h3>
    <div id="risultatiCorrelati"></div>
</div>    

<div style="display:none;font-size:12px;" id="boxAtlanteCollegati">
    <h3>Ricerca in atlante</h3>
    Termine: <input type="text" id="termineRicercaAtlante" value="" />
    <button id="ricercaTermineAtlante" onclick="cercaTermineAtlante();">Ricerca</button>
    <h3>Risultati</h3>
    <div id="risultatiCorrelatiAtlante">Ricercare un termine</div>
</div>

<a name="searchWiki">
<div style="border:1px solid gray; padding: 4px; margin-top:12px" id="toolSearch">
    <a style="float:right" href="#top" class="ui-icon ui-icon-arrowthick-1-n">&uarr;</a>

    <div style="border:1px solid black; padding:4px;background-color:#EEE">
        Term: <input type="text" id="searchTerm" value="" /> <input type="button" onclick="searchQuestion();" value="Cerca" />
    </div>
    <div id="toolResult"></div>
</div>

<style type="text/css" title="currentStyle">
    @import "' . $URI->getBaseUri(TRUE) . 'lib/datatables/css/jquery.dataTables.css";
</style>

<script type="text/javascript">
    function searchQuestion(){
        term = $("#searchTerm").val();

        $.post("admin.php?module=quiz&op=ajaxSearch", { term: term, time: "2pm" })
            .done(function(data) {
                $("#toolResult").html(data);
                $("#tableItems").dataTable();
            });

    }

    function checkForm(){
        document.formDomanda.submit();
    }

    $("#boxCollegati").dialog({autoOpen: false});
    $("#boxAtlanteCollegati").dialog({autoOpen: false});

    function apriDialog(){
        $("#boxCollegati").dialog("open");
    }

    function apriDialogAtlante(){
        $("#boxAtlanteCollegati").dialog("open");
    }

    function rimuoviAppunti(){
        var counts = 0;
        if (!confirm("Rimuovere gli appunti selezionati?"))
            return;

        if(!document.formDomanda.appunti.length){
            if (document.formDomanda.appunti.checked){
                removeWho("div_" + document.formDomanda.appunti.value);
            }
            return;
        }

        for (counter = 0; counter < document.formDomanda.appunti.length; counter++){
            if (document.formDomanda.appunti[counter].checked){

                removeWho("div_" + document.formDomanda.appunti[counter].value);
                counter--;
            }
        }

        if(!document.formDomanda.appunti.length){
            if (document.formDomanda.appunti.checked){
                removeWho("div_" + document.formDomanda.appunti.value);
            }
            return;
        }
    }

    // Rimuove le pagine
    function rimuoviPagine(){
        var counts = 0;
        if (!confirm("Rimuovere le pagine selezionate?"))
            return;

        if(!document.formDomanda.pagine.length){
            if (document.formDomanda.pagine.checked){
                removeWho("div_" + document.formDomanda.pagine.value);
            }
            return;
        }

        for (counter = 0; counter < document.formDomanda.pagine.length; counter++){
            if (document.formDomanda.pagine[counter].checked){

                removeWho("div_" + document.formDomanda.pagine[counter].value);
                counter--;
            }
        }

        if(!document.formDomanda.pagine.length){
            if (document.formDomanda.pagine.checked){
                removeWho("div_" + document.formDomanda.pagine.value);
            }
            return;
        }
    }

    function removeWho(who) {
        if(typeof who== \'string\') who=document.getElementById(who);
        if(who && who.parentNode)who.parentNode.removeChild(who);
    }

    function cercaTermineAtlante(){
        var termineAtlante = $("#termineRicercaAtlante").val();

        $.ajax({
            type: "POST",
            url: "admin.php?module=quiz&op=ricercaAtlante",
            data: { termine : termineAtlante }
        }).done(function( msg ) {
             $("#risultatiCorrelatiAtlante").html( msg );
        });
    }

    function cercaTermineAppunti(){
        var xmlHttp;
        var divRicerca = document.getElementById("risultatiCorrelati");

        try{
            // Firefox, Opera 8.0+, Safari
            xmlHttp=new XMLHttpRequest();
        }
        catch (e){
            // Internet Explorer
            try{
                xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e){
                try{
                    xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                catch (e){
                    alert("Your browser does not support AJAX!");
                    return false;
                }
            }
        }

        var termine = document.getElementById("termineRicerca").value;

        var parametri = "";
        parametri += "termine=" + encodeURIComponent(termine)

        xmlHttp.open("POST","' . 'admin.php?module=quiz&op=ricercaTermine",true);
        xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlHttp.setRequestHeader("Length", parametri.length);
        xmlHttp.setRequestHeader("Connection", "close");
        xmlHttp.send(parametri);

         xmlHttp.onreadystatechange=function(){
            if(xmlHttp.readyState==4){
                divRicerca.innerHTML = xmlHttp.responseText;
            }
         }

        divRicerca.innerHTML = termine + \'<img width="18px" height="18px" src="/admin/layout/loadinfo.gif" alt="" />\'
    }

function aggiungiAppunto(ID,titolo){
    var divRicerca = document.getElementById("divCorrelati");

    var ck = document.createElement("input");
    var tx = document.createElement("div");

    if (document.getElementById("input_" + ID)){
        return;
    }

    tx.id = "div_" + ID;
    tx.innerHTML = "<div class=\"ui-corner-all\" style=\"border:1px solid black; padding:2px; background-color:#C0FFC0;float:left;\"><input type=\'hidden\' name=\'anarray[]\' value=\'"+ID+"\'/> <input id=\"input_"+ID+"\" type=\"checkbox\" name=\"appunti\" value=\""+ID+"\"/> "+ titolo + "\<\/div><div style=\"clear:both;\">\<\/div>";

    ck.type = "checkbox";
    ck.value = ID;
    divRicerca.appendChild(tx);
}

//
// Aggiungi una pagina
//

function aggiungiPagina(ID,titolo){
    var divRicerca = document.getElementById("divCorrelatiAtlanti");

    var ck = document.createElement("input");
    var tx = document.createElement("div");

    if (document.getElementById("inputPagine_"+ID)){
        return;
    }

    tx.id = "div_" + ID;
    tx.innerHTML = "<div class=\"ui-corner-all\" style=\"border:1px solid black; padding:2px; background-color:#C0FFC0;float:left;\"><input type=\'hidden\' name=\'anarrayPagine[]\' value=\'"+ID+"\'/> <input id=\"inputPagine_"+ID+"\" type=\"checkbox\" name=\"pagine\" value=\""+ID+"\"/> "+ titolo + "\<\/div><div style=\"clear:both;\">\<\/div>";

    ck.type = "checkbox";
    ck.value = ID;
    divRicerca.appendChild(tx);
}

</script>

    <!-- TinyMCE -->
        <script type="text/javascript" src="' . $URI->getBaseUri(TRUE) . '/lib/editors/tinymce/tiny_mce.js"></script>
        <script type="text/javascript">
        <!--init-->
        tinyMCE.init({
            // General options
            mode : "specific_textareas",
            editor_selector : "tinyMCE",

            theme : "simple",
            plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,visualchars,wordcount",

            // Theme options
            theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,


            // Relative urls
            relative_urls : false

        });
        </script> ';