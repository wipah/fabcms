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

// Navigazione
$adminLayout->navigazione[] = '<a href="admin.php?module=quiz">Quiz</a>';
$adminLayout->navigazione[] = '<i>Edita domanda</i>';

echo '
<div class="inner-page-title">
    <h2>Quiz</h2>
    <span>Edita domanda</span>
</div>
<div class="clear"></div>
';

// Controlla se � stato passato l'ID della domanda
if (!isset($_GET['ID'])){
    echo 'Manca l\'ID';
    return;
}
$ID = (int) $_GET['ID'];

if (isset($_GET['salva'])){
    $domanda = $core->in($_POST['domanda']);
    $corretta = $core->in($_POST['corretta']);
    $ris_2 = $core->in($_POST['ris_2']);
    $ris_3 = $core->in($_POST['ris_3']);
    $ris_4 = $core->in($_POST['ris_4']);
    $argomenti = $core->in($_POST['argomenti']);
    
    if (!is_array($_POST['categorie'])){
        $categorie = '|'. (int) $_POST['categorie'] . '|';
    }else{
        $categorie = '|';
        foreach ($_POST['categorie'] AS $cat){
            $categorie .= $cat . '|';
        }
    }

    $categorie = rtrim($categorie,'|');
    $categorie = ltrim($categorie,'|');    


    // Controlla gli appunti collegati
    if (!is_array($_POST['anarray'])){
        $appunti = '|' . (int) $_POST['anarray'] . '|';
    }else{
        $appunti = '|';
        foreach ($_POST['anarray'] AS $appuntoID){
            $appunti .= $appuntoID . '|';
        }
    }

    $appunti = rtrim($appunti,'|');
    $appunti = ltrim($appunti,'|');
    
    $query = '
    UPDATE ' . $db->prefix . 'quiz_questions
    SET
    domanda = \''.$domanda.'\',
    risposta_1 = \''.$corretta.'\',
    risposta_2 = \''.$ris_2.'\',
    risposta_3 = \''.$ris_3.'\',
    risposta_4 = \''.$ris_4.'\',
    categorie = \''.$categorie.'\',
    argomenti = \''.$argomenti.'\',
    appunti = \''.$appunti.'\'
    WHERE ID = \''.$ID.'\'
    LIMIT 1;';
    
    $db->setQuery($query);
    if (!$db->executeQuery('insert')){
        echo 'errore' . $query;
        return;
    }
    
    echo '
    La domanda � stata modificata con successo con successo!<br/>
    &bull; <a href="admin.php?module=quiz&op=nuova">Aggiungi una nuova domanda</a>;<br/>
    &bull; <a href="admin.php?module=quiz&op=editaDomanda&ID='.$ID.'">Modifica la domanda appena modificata;';
    return;
}

// Crea la possibilit� di linking diretto per gli ultimi 5 appunti inseriti
$query = '
SELECT *
FROM cellula_appunti
ORDER BY ID DESC
LIMIT 5;
';
$db->setQuery($query);
$db->executeQuery('select');

if (!$db->numRows){
    $linkDirettoAppunti = '<ul class="side-menu layout-options"><li>Non ci sono, ancora appunti...</li></ul>';
}else{
    $linkDirettoAppunti = '<ul class="side-menu layout-options">';
    while ($linea = mysqli_fetch_array($db->getResultAsObject())){
        $linkDirettoAppunti .= '<li> <a href="javascript:aggiungiAppunto(' . $linea['ID'] . ',\'' . str_replace('\'','\\\'',$linea['titolo']) . '\')">' . $linea['titolo'] .' </a></li>';
    }
}
$linkDirettoAppunti .= '</ul>';

$query = '
SELECT * FROM ' . $db->prefix . 'quiz_categories ORDER BY nome ASC';
$db->setQuery($query);
$db->executeQuery('select');
$cat = '';
while ($linea = mysqli_fetch_array($db->getResultAsObject())){
    $cat .= ' <input name="categorie[]" id="cat_'.$linea['ID'].'" type="checkbox" value="'.$linea['ID'].'"/>' . '<span onclick="check(\''.$linea['ID'].'\');">'.$linea['nome'] .'</span> ';
}
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
if (!$db->executeQuery('select')){
    $ultimeDomande .= '<li>Errore nella query</li>';
}
while ($linea = mysqli_fetch_array($db->getResultAsObject())){
    $ultimeDomande .= '<li> <a href="admin.php?module=quiz&op=editaDomanda&ID='.$linea['ID'].'">#' . $linea['ID'] .'</a></li>' ;
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

$adminLayout->sidebar[] = '
					<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header">Link diretto appunti</div>
						<div class="portlet-content">
						' . $linkDirettoAppunti . '
						</div>
					</div>
';


$adminLayout->sidebar[] = '
					<div class="portlet ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
						<div class="portlet-header ui-widget-header">Ultime domande</div>
						<div class="portlet-content">
						' . $ultimeDomande . '
						</div>
					</div>
';

$adminLayout->sidebar[] = '
                    <div class="other-box yellow-box ui-corner-all ui-corner-all">
						<div class="cont tooltip ui-corner-all" title="Check out the sortable examples below !!">
							<h3>Statistiche:</h3>
							<p>' . $statistiche . '</p>
						</div>
					</div>
';

// Crea la query
$query = '
SELECT d.* 
FROM ' . $db->prefix . 'quiz_questions AS d
WHERE d.ID = \''.$ID.'\'
LIMIT 1
'; 
$db->setQuery($query);
if (!$db->executeQuery('select')){
    echo 'Errore nella query.'.$query;
    return;
}

if (!$db->numRows){
    echo 'La domanda non esiste';
    return;
}

$lineaDomanda = $db->getResultAsArray();
$arrayCategorieDomanda = explode('|',$lineaDomanda['categorie']);

$query = '
SELECT * FROM ' . $db->prefix . 'quiz_categories ORDER BY nome ASC';
$db->setQuery($query);
$db->executeQuery('select');
$cat = '';
while ($linea = mysqli_fetch_array($db->getResultAsObject())){
    $cat .= ' <input '.(in_array($linea['ID'],$arrayCategorieDomanda) ? 'checked="checked"' : '').' name="categorie[]" id="cat_'.$linea['ID'].'" type="checkbox" value="'.$linea['ID'].'"/>' . '<span onclick="check(\''.$linea['ID'].'\');">'.$linea['nome'] .'</span> ';
}
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
</script>

<form name="formDomanda" id="formDomanda" action="admin.php?module=quiz&op=editaDomanda&ID='.$ID.'&salva" method="post">
    <div style="font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Domanda:</b> <textarea name="domanda" style="width:100%;" id="domanda">'.$lineaDomanda['domanda'].'</textarea>
    </div>

    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Categorie:</b>'.$cat.'
    </div>

    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Argomenti:</b><input type="text" name="argomenti" id="argomenti" value="' . $lineaDomanda['argomenti'] . '"/>
    </div>

    <div style="margin-top:12px;font-size:14px;border:1px solid grey;padding:4px;background-color:#E0E0E0" class="ui-corner-all">
        <b>Appunti collegati</b> -
        <a href="#" onclick="apriDialog();">Aggiungi</a> -
        <div id="divCorrelati">
';

if (!isset($lineaDomanda['appunti'])){ # Nessun appunto
    
}elseif(!strpos($lineaDomanda['appunti'],'|')){
    $arrayAppunti = array($lineaDomanda['appunti']);
}else{
    $arrayAppunti = array();
    $arrayTemp = explode ('|',$lineaDomanda['appunti']);
    foreach ($arrayTemp AS $appuntoID){
        $arrayAppunti[] = $appuntoID;    
    }   
}

$appuntiBox = '';
if (is_array($arrayAppunti)){
    $queryWhere = ' WHERE ID = \'';
    foreach($arrayAppunti AS $appuntoID){
        $queryWhere .= $appuntoID .'\' OR ID = \'';
    }
    $queryWhere = substr($queryWhere,0,-9);
}else{
    $queryWhere = ' WHERE ID = \''.$lineaDomanda['appunti']; 
}

$query = '
SELECT ID, titolo 
FROM cellula_appunti
'.$queryWhere.';';

$db->setQuery($query);
if (!$db->executeQuery('select')){
   echo 'Errore nella query '. $query;
   return; 
}else{
    while ($linea = mysqli_fetch_array($db->getResultAsObject())){
        echo '<div id="div_'.$linea['ID'].'" style="border: 1px solid black; padding: 2px; background-color: rgb(192, 255, 192); float: left;" class="ui-corner-all"><input type="hidden" value="'.$linea['ID'].'" name="anarray[]"> <input type="checkbox" value="'.$linea['ID'].'" name="appunti" id="input_'.$linea['ID'].'">'.$linea['titolo'].'</div>';
    }
}
echo '
            </div>
            <div style="clear:left"></div>
            <br/>
            <button id="btnEliminaAppunti" type="button" onclick="rimuoviAppunti();">Elimina gli appunti</button> 
        </div>        
        
        <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#C0FFC0" class="ui-corner-all">
            <b>Corretta:</b> <textarea id="corretta" style="width:100%; height:150px;" name="corretta">' . $lineaDomanda['risposta_1'] . '</textarea>
        </div>
        
        <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
            <b>#2</b> <textarea id="ris_2" style="width:100%; height:150px;" name="ris_2">'.$lineaDomanda['risposta_2'].'</textarea>
        </div>
        
        <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
            <b>#3</b> <textarea id="ris_3" style="width:100%; height:150px;" name="ris_3">'.$lineaDomanda['risposta_3'].'</textarea>
        </div>
        
        <div style="margin-top:14px;font-size:14px;border:1px solid grey;padding:4px;background-color:#FFFF80" class="ui-corner-all">
            <b>#4</b> <textarea id="ris_4" style="width:100%; height:150px;" name="ris_4">'.$lineaDomanda['risposta_4'].'</textarea>
        </div>
        <button id="invia" type="button" onclick="checkForm();">Salva modifiche</button>            
    </form>

<div style="display:none;font-size:12px;" id="boxCollegati">
    <h3>Ricerca appunto</h3>
    Termine: <input type="text" id="termineRicerca" /">
    <button id="ricercaTermine" onclick="cercaTermineAppunti();">Ricerca</button>
    <h3>Risultati</h3>
    <div id="risultatiCorrelati"></div>
</div>    

<!-- TinyMCE -->
<script type="text/javascript" src="/include/tiny_mce/tiny_mce_gzip.js"></script>
<script type="text/javascript">
$("#btnEliminaAppunti").button();

function checkForm(){
    document.formDomanda.submit();
}

$("#boxCollegati").dialog({autoOpen: false});

function apriDialog(){
    $("#boxCollegati").dialog("open");
}


function rimuoviAppunti(){
    var counts = 0;
    var counter = 0;
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


function removeWho(who) {
    if(typeof who== \'string\') who=document.getElementById(who);
    if(who && who.parentNode)who.parentNode.removeChild(who);
}

function aggiungiAppunto(ID,titolo){
    var divRicerca = document.getElementById("divCorrelati");
    
    var ck = document.createElement("input");
    var tx = document.createElement("div");
        
    if (document.getElementById("input_"+ID)){
        return;    
    }    
    tx.id = "div_" + ID;               
    tx.innerHTML = "<div class=\"ui-corner-all\" style=\"border:1px solid black; padding:2px; background-color:#C0FFC0;float:left;\"><input type=\'hidden\' name=\'anarray[]\' value=\'"+ID+"\'/> <input id=\"input_"+ID+"\" type=\"checkbox\" name=\"appunti\" value=\""+ID+"\"/> "+ titolo + "</div><div style=\"clear:both;\"></div>";
    
    ck.type = "checkbox";
    ck.value = ID;
    divRicerca.appendChild(tx);   
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
    
    xmlHttp.open("POST","'.$conf['http_sito'].'/admin/includes/ajax/ajax.php?op=quizRicercaTermine",true);
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
tinyMCE_GZ.init({
    theme : "advanced",
    plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,visualchars,imagemanager,wordcount",
    disk_cache: true
});
</script>
<script type="text/javascript">
tinyMCE.init({
    // General options
    mode : "textareas",
    theme : "advanced",
    plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,visualchars,imagemanager,wordcount",
    disk_cache : true,

    // Theme options
    theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true,
    
    // Drop lists for link/image/media/template dialogs
    template_external_list_url : "lists/template_list.js",
    external_link_list_url : "lists/link_list.js",
    external_image_list_url : "lists/image_list.js",
    media_external_list_url : "lists/media_list.js",

    // Relative urls
    relative_urls : false,
    
});
</script>     
';