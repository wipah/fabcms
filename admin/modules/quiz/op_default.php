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
$template->navBar[] = '<a href="admin.php?module=quiz">Quiz</a>';
$template->navBar[]= '<i>Homepage</i>';

// Ultime domande
$query = '
SELECT ID 
FROM ' . $db->prefix . 'quiz_questions
ORDER BY ID desc
LIMIT 5
';

$ultimeDomande = '<ul class="side-menu layout-options">';
if (!$db->query($query)){
    $ultimeDomande .= 'Errore nella query';
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

$db->query($query);
$linea = $db->getResultAsArray();

$statistiche = 'Ci sono <i>' . $linea['ricorrenze'] . '</i> domande inserite nel database.';

$template->sidebar .= $template->simpleBlock('Ultime domande', $ultimeDomande);
$template->sidebar .=  $template->simpleBlock('Statistiche', $statistiche);

echo '
<style type="text/css">
    .sceltaHome{
        text-align:center;margin:0pt auto;padding:4px;float:left;width:250px;border:1px solid grey; background-color:#E0E0E0;color:#F0F0F0;margin:10pt;
    }
</style>

<h2>Quiz</h2>

<div style="margin:0 pt auto;">
    <div class="sceltaHome ui-corner-all">
        <a href="admin.php?module=quiz&op=nuova">Inserisci una domanda</a>
    </div>

    <div class="sceltaHome ui-corner-all">
        <a href="admin.php?module=quiz&op=categoria&command=nuova">Inserisci una categoria</a>
    </div>

    <div class="sceltaHome ui-corner-all">
        <a href="admin.php?module=quiz&op=config">Configura</a>
    </div>
    <div style="clear:left"></div>
</div>
';

$query = 'SELECT * FROM ' . $db->prefix . 'quiz_categories';

$result = $db->query($query);

echo '
<h2>Quality test</h2>
<table border="1">
    <thead>
        <tr>
            <td class="ui-widget-header">Category</td>
            <td class="ui-widget-header">Questions</td>
            <td class="ui-widget-header">% same in 10</td>
            <td class="ui-widget-header">% same in 20</td>
        </tr>
    </thead>
    <tbody>
';
while ($row = mysqli_fetch_array($result)){
    $ID = $row['ID'];
    $category = $row['nome'];
    $visible = (int) $row['visibile'];
    $query = '
    SELECT Count(ID) AS total
    FROM ' . $db->prefix . 'quiz_questions
    WHERE categorie = \''.$ID . '\'
       OR categorie LIKE \'' . $ID . '|%\'
       OR categorie LIKE \'%|' . $ID . '|%\'
       OR categorie LIKE \'%|'. $ID . '\'';


    if (!$result_subquery = $db->query($query)){
        echo 'Subquery error';
    }else{
        $row_subquery = $db->getResultAsArray();
        echo '
            <tr>
                <td>
                ' . ($visible === 1 ? '<span style="color:green;">' :'<span style="color:red;">' ) . $category . '</span>
                 [<a href="admin.php?module=quiz&op=categoria&command=modifica&ID=' . $ID. '">Edit</a>]
                </td>
                <td>' . $row_subquery['total'] . '</td>
                <td>' . ( (int) $row_subquery['total'] > 0 ?  round(10 / (int) ($row_subquery['total']), 5) * 100 : 'NA' ) . '</td>
                <td>' . ( (int) $row_subquery['total'] > 0 ?  round(20 / (int) ($row_subquery['total']), 5) * 100 : 'NA' ) . '</td>
             </tr>';
    }
}
echo '</tbody>
</table>

<h2>Logs</h2>
';

$query = '
SELECT(
    SELECT Count(ID)
    FROM ' . $db->prefix . 'quiz_logs
    ) AS total,
    (
    SELECT count(ID)
    FROM ' . $db->prefix . 'quiz_logs
    WHERE user_ID > 0) AS total_user
    ';

if (!$result = $db->query($query)){
    echo 'Query error. ' . $query;
}else{
    if (!$db->affected_rows){
        echo 'No record!';
    }else{
        $row = mysqli_fetch_array($result);
        echo '
        <table border="1" style="border:1px">
            <tr>
                <td>Total anonymous quiz</td><td> ' . $row['total'] . '</td>
            </tr>
            <tr>
                <td>Total registered quiz</td><td> ' . $row['total_user'] . '</td>
            </tr>
        </table>
        ';
    }
}