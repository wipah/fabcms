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

if (!isset($core->loaded)) {
    die('Direct call detected');
}
include($conf['path']['baseDir'] . 'modules/search/lib/class_search.php');
$search = new CrisaSoft\FabCMS\search();

$template->fullPage = true;
$core->jsVar['fabcms_isFullPage'] = 0;

if (isset($_GET['search'])) {
    $method = 'GET';
    $phrase = $core->in($_GET['search'], true);
} elseif (isset($_POST['search'])) {
    $method = 'POST';
    $phrase = $core->in($_POST['search'], true);
} else {
    $template->moduleH1 = '<h1 class="FabCMSH1">' . $language->get('search', 'search') . '</h1>';
    $template->navBar[] = $language->get('search', 'search');

    echo '
    <form method="post" action="' . $URI->getBaseUri() . '/search/simple/">
      <div class="form-group">
        <label for="simpleSearch">' . $language->get('search', 'termToSearch') . '</label>
        <input type="text" class="form-control" id="search" name="search" placeholder="' . $language->get('search', 'termToSearch') . '">
      </div>
      <button type="submit" class="btn btn-default">' . $language->get('search', 'search') . '</button>
    </form>';

    return;
}

echo '
<div class="row">
    <div class="col-md-12">
        <form action="' . $URI->getBaseUri() . 'search/simple/" method="post">
            <input name="search" type="text" value="' . $phrase . '">
            <button type="submit" value="search">Search</button>
        </form>
    </div>
</div>';

$connector->callHandler('searchSimpleBeforeQuery', array('phrase' => $phrase));

$template->moduleH1 = '<h1 class="FabCMSH1">Searching for: ' . $phrase . '</h1>';

$searchHeadings = array();
$searchResult   = array();

$scanPath = $conf['path']['baseDir'] . ($adminSide == TRUE ? 'admin/' : '') . 'modules';

$search->search($phrase);

$script = '
function prepareModule(module){
    $(".searchPanel_" + module + ":not(#" + module + "_1)").hide();
}

function updatepage(module, pageID){
    $(".searchPanel_" + module).hide();
    $("#" + module + "_" + pageID ).show();
}
 ';
$this->addScript($script);

// Initialize the three main "fields" where search results will be shown to the user.
$searchMain     = '';
$searchOnFocus  = '';
$searchLateral  = '';

/* Nothing found */
if (empty($search->results)) {
    echo '<h2>Nessun risultato</h2>
    Non &egrave; stata trovata nessuna pagina che possa corrispondere alla tua ricerca. Ti preghiamo di riprovare utilizzando un termine specifico.';
    return;
}

foreach ($search->results as $row => $value) {
    $i = 0;
    $searchMain .= ('Modulo: ' . $row . '<br/>');

    $template->scripts[] = '<script>prepareModule("' . $row . '");</script>';

    $theKeys = (array_keys($value));
    $totalResults = count($theKeys);
    $totalPages = ceil($totalResults / 10);

    $searchMain .= '<nav aria-label="Page navigation example">
  <ul class="pagination">';
    for ($y = 1; $y <= $totalPages; $y++) {
        $searchMain .= '<li class="page-item">
                            <a class="page-link" onclick="updatepage(\'' . $row . '\', \'' . $y . '\');">' . $y . '</a>
                        </li>';
    }
    $searchMain .= '</ul></nav>';

    $y = 1;
    $z = 1;

    $changePage = true;

    foreach ($value as $item) {

        if ($changePage === true) {
            $searchMain .= '
                <div id="' . $row . '_' . $z . '" class="searchPanel_' . $row . '">';
            $searchMain .= sprintf($language->get('search', 'paginationPageXofYOfZResults'), $z, $totalPages, $totalResults);
            $z++;
        }
        $element = $theKeys[$i];
        $searchMain .= '
        <div class="searchResult" style="margin-top: 14px;">
        <strong><a href="' . $value[$element]['trackback'] . '/">' . $value[$element]['name'] . '</a></strong><br/>
        <em style="color:green">' . $value[$element]['trackback'] . '/</em><br/>' .
            $value[$element]['snippet'] . '</div>';

        if ($y === 10) {
            $y = 1;
            $changePage = true;
        } else {
            $y++;
            $changePage = false;
        }

        if ($changePage === true) {
            $searchMain .= '</div>';
        }

        $i++;
    }

    if ($changePage !== true)
        $searchMain .= '</div>';
}

$connector->callHandler('searchSimpleAfterPanels', array('phrase' => $phrase));

// Loads default view (simple_default)
echo file_get_contents(__DIR__ . '/views/simple_default.html');

$template->hooks[] = 'searchSimpleMain';
$template->hooksData[] = $searchMain;

echo '<!--FabCMS-hook:searchSimpleAfterPanels-->';
$search->log($phrase, $method, 'simple');