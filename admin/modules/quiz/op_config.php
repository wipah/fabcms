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

echo '<h1>Configurazione</h1>';

$template->navBar[] = '<a href="admin.php?module=quiz">Quiz</a>';
$template->navBar[] = '<em>Configurazione</em>';

$configFile = $conf['path']['baseDir']  . '/modules/quiz/config.php';

// Controlla se è stato passato il comando di salvataggio
if (isset($_GET['salva'])) {

    if (!isset($_POST['dummy'])) {
        echo 'Rilevato reload. Configurazione non salvata';
    } else {
        // Ottiene i dati
        $bannerSchedaRisultatoLaterale = str_replace('\'', '\\\'', $_POST['bannerSchedaRisultatoLaterale']);
        $bannerSchedaSelezioneLaterale = str_replace('\'', '\\\'', $_POST['bannerSchedaSelezioneLaterale']);
        $bannerFormUnderTheDescription = str_replace('\'', '\\\'', $_POST['bannerFormUnderTheDescription']);
        $bannerFormSelectionBetweenSecondAndThirdCategory = str_replace('\'', '\\\'', $_POST['bannerFormSelectionBetweenSecondAndThirdCategory']);

        $defaultAtlas = $core->in($_POST['defaultAtlas'], true);
        $defaultAtlasLanguage = $core->in($_POST['defaultAtlasLanguage'], true);
        $configData = <<<EOT
<?php
/*
 * File di configurazione autogenerato
 */

/* Collegamento agli atlanti */
\$conf['quiz']['defaultAtlas'] = '$defaultAtlas';
\$conf['quiz']['defaultAtlasLanguage'] = '$defaultAtlasLanguage';

/* BANNER */
\$conf['quiz']['banner']['schedaRisposteBannerlaterale'] = '$bannerSchedaRisultatoLaterale';
\$conf['quiz']['banner']['schedaSelezioneLaterale'] = '$bannerSchedaSelezioneLaterale';
\$conf['quiz']['banner']['formUnderTheDescription'] = '$bannerFormUnderTheDescription';
\$conf['quiz']['banner']['formSelectionBetweenSecondAndThirdCategory'] = '$bannerFormSelectionBetweenSecondAndThirdCategory';
EOT;

        if (!file_put_contents($configFile, $configData)) {
            $log->write('quiz_configuration_save_error','quiz', 'Filename: ' . $configFile . ', DATA: ' . $configData );
            echo ('Non è stato possibile salvare il file');
        } else {
            $log->write('quiz_configuration_save_ok','quiz', 'Filename: ' . $configFile . ', DATA: ' . $configData );
            echo ('Il file di configurazione è stato salvato con successo');
        }
    }
}

// Controlla, se esiste, il file di configurazione
if (!file_exists($configFile)) {
    echo '<div style="border: 1px solid red; background-color: #ffff00; padding: 4px">Il file di configurazione non esiste e sarà creato dopo il salvataggio</div>';
} else {
    include $configFile;
}

echo '
<script>
	$(function() {
		$( "#tabs" ).tabs();
	});
	</script>

    <div id="tabs">
        <ul>
            <li><a href="#tabs-1">Generale</a></li>
            <li><a href="#banner">Gestione banner</a></li>
        </ul>

        <form name="frmSalva" id="frmSalva" action="admin.php?module=quiz&op=config&salva" method="post">
            <input type="hidden" name="dummy" value="dummiest" />
            <div id="tabs-1">
                <p>
                    <div class="cfgInfo">
                        Atlante di default
                    </div>
                    <div class="cfgInput">
                        <input type="text" id="defaultAtlas" name="defaultAtlas" value="' . $conf['quiz']['defaultAtlas'] . '" />
                    </div>
                    <div class="cfgInfo">
                        Lingua atlante di default
                    </div>
                    <div class="cfgInput">
                        <input type="text" id="defaultAtlasLanguage" name="defaultAtlasLanguage" value="' . $conf['quiz']['defaultAtlasLanguage'] . '" />
                    </div>
                </p>
            </div>
            <div id="banner">
                <p>
                Gestione banner
                <div class="cfgInfo">
                    Singola scheda, risultato laterale (PLUGINABLE)
                </div>
                <div class="cfgInput">
                    <textarea class="xmlCode" name="bannerSchedaRisultatoLaterale" id="bannerSchedaRisultatoLaterale">' . $conf['quiz']['banner']['schedaRisposteBannerlaterale'] . '</textarea>
                </div>
                <hr />

                <div class="cfgInfo">
                    Scheda, selezione laterale
                </div>
                <div class="cfgInput">
                    <textarea class="xmlCode" name="bannerSchedaSelezioneLaterale" id="bannerSchedaSelezioneLaterale" style="width:100%; height: 80px;">' . $conf['quiz']['banner']['schedaSelezioneLaterale'] . '</textarea>
                </div>
                <hr />

                <div class="cfgInfo">
                    Form, under the description
                </div>
                <div class="cfgInput">
                    <textarea class="xmlCode" name="bannerFormUnderTheDescription" id="bannerFormUnderTheDescription" style="width:100%; height: 80px;">' . $conf['quiz']['banner']['formUnderTheDescription'] . '</textarea>
                </div>
                <hr />

                <div class="cfgInfo">
                    Form, between second and third category.
                </div>
                <div class="cfgInput">
                    <textarea class="xmlCode" name="bannerFormSelectionBetweenSecondAndThirdCategory" id="bannerFormSelectionBetweenSecondAndThirdCategory" style="width:100%; height: 80px;">' . $conf['quiz']['banner']['formSelectionBetweenSecondAndThirdCategory'] . '</textarea>
                </div>
                <hr />
                </p>
            </div>
            <input type="submit" />
        </form>
    </div>

<script src="' . $URI->getBaseUri(true) . '/lib/codemirror/lib/codemirror.js"></script>
<link rel="stylesheet" href="' . $URI->getBaseUri(true) . '/lib/codemirror/lib/codemirror.css">

<script src="' . $URI->getBaseUri(true) . '/lib/codemirror/mode/xml/xml.js"></script>

<script>
jQuery(document).ready(function($) {
            var code_type = \'\';

            $(\'.xmlCode\').each(function(index) {
                $(this).attr(\'id\', \'code-\' + index);

                var editor = CodeMirror.fromTextArea(document.getElementById(\'code-\' + index), {
                        mode: "xml",
                        lineNumbers: true,
                        tabMode: "indent"
                    }

                );
                editor.refresh();
                editor.focus();
            });
        });
</script>
';