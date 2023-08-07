<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!$core->adminLoaded) {
    die('Direct call detected');
}

echo '<h1>Quiz - Categorie</h1>';

$template->navBarAddItem('Quiz', 'admin.php?module=quiz');
$template->navBarAddItem('Gestione categorie',);

switch ($_GET['command']) {

    case 'modifica':
    case 'nuova':

        // Obtain the data
        if (($_GET['command'] == 'nuova' || $_GET['command'] == 'modifica') && $_GET['save'] == 'true') {
            if (strlen($_POST['nome']) === 0) {
                echo 'No name was passed. Using <em>noname</em><br/>';
                $name = 'noname';
            } else {
                $name = $core->in($_POST['nome']);
            }

            if (strlen($_POST['lang']) === 0) {
                echo 'No lang was passed, using <em>en</em><br/>';
                $lang = 'en';
            } else {
                $lang = $core->in($_POST['lang']);
            }

            $shortDescription = $core->in($_POST['shortDescription']);
            $longDescription = $core->in($_POST['longDescription']);
            $icon = $core->in($_POST['icon']);
            $title = $core->in($_POST['title'], true);
            $metadata_description = $core->in($_POST['metadata_description'], true);

            /*
             * @TODO: Reorganize $visibile, $visible_update and $visible_insert
             */
            if ($_POST['enabled'] == 'true') {
                $visible = 'visibile = \'1\'';
                $visible_insert = '1';
                $visible_update = '1';
            } else {
                $visible = 'false';
                $visible_insert = '0';
                $visible_update = '0';
            }
        }

        if (isset($_GET['save']) && $_GET['command'] == 'modifica') {
            if (!isset($_GET['ID'])) {
                echo 'ID was not passed';
                return;
            }

            $ID = (int)$_GET['ID'];

            if (!isset($_POST['dummy'])) {
                echo 'Reload detected';
                return;
            }


            $query = 'UPDATE ' . $db->prefix . 'quiz_categories
            SET nome = \'' . $name . '\',
            icon = \'' . $icon . '\',
            title = \'' . $title . '\',
            meta_description = \'' . $metadata_description . '\',
            lang = \'' . $lang . '\',
            short_description = \'' . $shortDescription . '\',
            long_description = \'' . $longDescription . '\',
            visibile = \'' . $visible_update . '\'
            WHERE ID = \'' . $ID . '\'
            LIMIT 1';

            if (!$db->query($query)) {
                echo 'Query error. <br>' . $db->lastError . ' ' . $query;
                return;
            }

            echo '<div style="padding:4px;" class="ui-state-highlight">Category updated</div>';
        }

        if ($_GET['command'] == 'nuova' && $_GET['save'] == 'true') {

            // Crea la query
            $query = '
        INSERT INTO ' . $db->prefix . 'quiz_categories
            (nome, title, meta_description, lang, icon, visibile, short_description, long_description)
        VALUES
        (
            \'' . $name . '\',
            \'' . $title . '\',
            \'' . $metadata_description . '\',
            \'' . $lang . '\',
            \'' . $icon . '\',
            \'' . $visible_insert . '\',
            \'' . $shortDescription . '\',
            \'' . $longDescription . '\'
        );
        ';

            if (!$db->query($query)) {
                $log->write('quiz_new_category_insert_error', 'quiz', 'Query: ' . $query);
                echo '<div class="ui-corner-all" style="border:1px solid red; background-color: #FFC0C0; padding:4px;">Errore nella query.<br/>' . $query . '</div>';
            } else {
                $log->write('quiz_new_category_insert_ok', 'quiz', 'ID: ' . $db->lastInsertID);
                echo '<div class="ui-corner-all" style="border:1px solid green; background-color: #C0FFC0; padding:4px;">Categoria inserita con successo</div>';
            }
        }

        if ($_GET['command'] == 'modifica') {

            $ID = (int)$_GET['ID'];

            $action = 'admin.php?module=quiz&op=categoria&command=modifica&save=true&ID=' . $ID;

            $query = 'SELECT * FROM ' . $db->prefix . 'quiz_categories WHERE ID = \'' . $ID . '\' LIMIT 1;';

            if (!$result = $db->query($query)) {
                echo 'Query error. ' . $query;
                return;
            }

            if (!$db->affected_rows) {
                echo 'No category found, using the ID ' . $ID;
                return;
            }

            $row = mysqli_fetch_assoc($result);
        }

        if ($_GET['command'] == 'nuova') {
            $action = 'admin.php?module=quiz&op=categoria&command=nuova&save=true';
        }
        break;
    default:
        unset ($linea);
}

// Icon
$icon = '<select onchange="switchIcon();" name="icon" id="icon">';
foreach (glob($conf['path']['baseDir'] . 'modules/quiz/icons/*.png') as $theIcon) {
    $theName = basename($theIcon, '.png');
    $icon .= '<option ' . ($theName == $row['icon'] ? 'selected = "selected"' : '') .
        'value="' . $theName . '">' . $theName . '</option>';
}
$icon .= '</select>
<script type="text/javascript">
// Ok, I am too lazy
switchIcon();

function switchIcon(){
    theValue = $("#icon").val();
    $("#iconPreview").html("<img src=\'' . $URI->getBaseUri(true) . 'modules/quiz/icons/" + theValue + ".png\' alt=\'icon\' />");
}
</script>';

echo '
<div style="margin-right:230px">
    <style>
        .cfgInfo {
            float: left;
            width: 300px;
        }
        
        .cfgInput{
            margin-left: 320px;
            width: 900px;
        }
        .cfgField {
        clear: both;
        }
    </style>
    <form action="' . $action . '" method="post">

        <input type="hidden" name="dummy" id="dummy" value="dummy" />

        <div class="cfgField">
            <div class="cfgInfo">Nome della categoria</div>
            <div class="cfgInput">
                <input type="text" name="nome" id="nome" value="' . $row['nome'] . '"/>
            </div>
        </div>
        
        <div class="cfgField">
            <div class="cfgInfo">Title (browser)</div>
            <div class="cfgInput">
                <input type="text" name="title" id="title" value="' . $row['title'] . '"/>
            </div>
        </div>
        
        <div class="cfgField">
            <div class="cfgInfo">Metadata description</div>
            <div class="cfgInput">
                <input type="text" name="metadata_description" id="metadata_description" value="' . $row['meta_description'] . '"/>
            </div>
        </div>

        <div class="cfgField">
            <div class="cfgInfo">Icon</div>
            <div class="cfgInput">
                <div style="float:right" id="iconPreview"></div>
                ' . $icon . '
            </div>
            <div style="clear:right"></div>
        </div>

        <div class="cfgField">
            <div class="cfgInfo">Lingua</div>
            <div class="cfgInput">
                <input maxlength="2" type="text" name="lang" id="lang" value="' . $row['lang'] . '"/>
            </div>
        </div>

        <div class="cfgField">
            <div class="cfgInfo">Short description</div>
            <div class="cfgInput">
                <textarea style="width:100%" name="shortDescription" id="shortDescription">' . $row['short_description'] . '</textarea>
            </div>
        </div>

        <div class="cfgField">
            <div class="cfgInfo">Long description</div>
            <div class="cfgInput">
                <textarea style="width:100%" name="longDescription" id="longDescription">' . $row['long_description'] . '</textarea>
            </div>
        </div>

        <div class="cfgField">
            <div class="cfgInfo">Enabled</div>
            <div class="cfgInput">
                <input id="enabled" name="enabled" type="checkbox" value="true" ' . ((int)$row['visibile'] === 1 ? 'checked="checked"' : '') . '/>
            </div>
        </div>

        <div style="clear:left"></div>
        <button style="float:right" id="btnInvia" type="submit">Salva</button>
        <div style="clear:right"></div>
    </form>
    
    
    <script type="text/javascript">
        $("#btnInvia").button();
    </script>
    <h2>Categorie già create</h2>
    ';

$query = '
SELECT * 
FROM ' . $db->prefix . 'quiz_categories
';

$db->query($query);

if (!$result = $db->affected_rows) {
    echo 'Nessuna categoria ancora creata';
} else {

    while ($linea = mysqli_fetch_assoc($result)) {
        echo '&bull; <a href="admin.php?module=quiz&op=categoria&command=modifica&ID=' . $linea['ID'] . '">' . $linea['nome'] . '</a> <br/>';
    }
}
echo '    
</div>
';