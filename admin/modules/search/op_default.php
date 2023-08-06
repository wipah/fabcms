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

$template->navBar[] = '<a href="admin.php?module=search">Search</a>';

$query = 'SELECT (
    SELECT count(ID) FROM `' . $db->prefix . 'search_logs` WHERE `date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 day)
    ) AS today,
(
        SELECT count(ID) FROM `' . $db->prefix . 'search_logs` WHERE `date` BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 day) AND CURDATE()

    ) AS yesterday';
$db->setQuery($query);
$db->executeQuery('select');
$row = $db->getResultAsArray();
$totalSearchYesterday = $row['yesterday'];
$totalSearchToday = $row['today'];

$template->sidebar .= $template->simpleBlock('Statistics', '<strong>Today\'s searches</strong>: ' . $totalSearchToday . '<br/><strong>Yesterday\'s searches</strong>: ' . $totalSearchYesterday);
echo '
<h1 class="FabCMSH1">Search module</h1>
<button id="filterButton">Filter</button>
<script type="text/javascript">
$("#filterButton").button({icons: {primary: "ui-icon-search"}});

$( "#filterButton" ).click(function() {
  $( "#theFilter" ).toggle( "slow" );
});
</script>
<div  style="display: none; background-color:#DEFADE" id="theFilter">
    <div class="form-horizontal">
    <fieldset>

    <!-- Form Name -->
    <legend>Filter</legend>

    <!-- Text input-->
    <div class="form-group">
      <label class="col-md-4 control-label" for="filterPhrase">Phrase</label>
      <div class="col-md-4">
      <input id="filterPhrase" name="filterPhrase" type="text" placeholder="Phrase" class="form-control input-md" required="">
      <span class="help-block">Filter by Phrase</span>
      </div>
    </div>

    <!-- Text input-->
    <div class="form-group">
      <label class="col-md-4 control-label" for="filterIP">IP</label>
      <div class="col-md-4">
      <input id="filterIP" name="filterIP" type="text" placeholder="IP" class="form-control input-md" required="">
      <span class="help-block">Filter by IP. IE: 192.168 -> %192.168%</span>
      </div>
    </div>

    <!-- Select Basic -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="filterResult">Result</label>
      <div class="col-md-4">
        <select id="filterResult" name="filterResult" class="form-control">
          <option value="10">Latest 10</option>
          <option value="100">Latest 100</option>
          <option value="0">All</option>
        </select>
      </div>
    </div>

    <!-- Button -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="singlebutton">Search</label>
      <div class="col-md-4">
        <button onclick="updateSearch();" id="singlebutton" name="singlebutton" class="btn btn-primary">Search</button>
      </div>
    </div>

    </fieldset>
    </div>

</div>

<div id="mainResult">
    Loading data. Please wait.
</div>
<script type="text/javascript">
    updateSearch();

    $("#filterPhrase, #filterIP").keyup(function(event){
        if(event.keyCode == 13){
            updateSearch();
        }
    });

    function updateSearch(){
        var filterPhrase = $("#filterPhrase").val();
        var filterResult = $("#filterResult").val();
        var filterIP = $("#filterIP").val();
        $.post( "admin.php?module=search&op=ajaxSearch", { filterPhrase: filterPhrase, filterResult: filterResult, filterIP: filterIP })
            .done(function( data ) {
            $("#mainResult").html(data);
            $("#searchAjaxTable").dataTable();
        });
    }
</script>';