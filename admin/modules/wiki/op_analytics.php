<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 18/05/2018
 * Time: 15:23
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if ($_GET['command'] === 'show') {
    require_once 'op_analytics_ajax_show.php';

    return;
}

/*
$query = 'SELECT * 
          FROM ' . $db->prefix . 'stats_groups 
          WHERE module = \'wiki\' ';

$db->setQuery($query);

if (!$resultGroups = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;

    return;
}

$selectGroups = '
<!-- Select Basic -->
<div class="form-group">
  <label class="col-md-4 control-label" for="selectbasic">Group</label>
  <div class="col-md-4">
    <select id="group" name="group" class="form-control">';

while ($row = mysqli_fetch_assoc($resultGroups)) {
    $selectGroups .= '<option value="' . $row['ID'] . '">' . $row['group_name'] . '</option>';
}


$selectGroups .= ' </select>
  </div>
</div>';
*/

echo '
<div class="form FabCMS-filterBox">
    <fieldset>    
        <!-- Form Name -->
        <legend>Filters</legend>
        
        <div class="row">
             <div class="col-md-3">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Words min.</span>
                    <input type="text" class="form-control form-control-sm" id="filterWordsMoreThan">
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Words max.</span>
                    <input type="text" class="form-control form-control-sm" id="filterWordsLessThan">
                </div>
            </div>
            
        </div>
        
        <div class="row">
            
            <div class="col-md-2">
                    <div class="form-group">
                        <span class="FabCMS-filterValue">From</span>
                        <input type="text" class="form-control form-control-sm datepicker" id="filterFrom">
                    </div>
            </div>
            
            <div class="col-md-2">
                <div class="form-group">
                    <span class="FabCMS-filterValue">To</span>
                    <input type="text" class="form-control form-control-sm datepicker" id="filterTo">
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Tag</span>
                    <input type="text" class="form-control form-control-sm" id="filterTags">
                </div>
            </div>
            

            
            
            <div class="col-md-2">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Grouping</span>
                    <select  class="form-control form-control-sm" id="filterGrouping">
                        <option value="1">By day</option>
                        <option value="2">By week</option>
                        <option value="3">By month</option>
                        <option value="4">By year</option>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Compare with</span>
                    <select  class="form-control form-control-sm" id="filterCompareWith">
                        <option value="1">No compare</option>
                        <option value="2">Yesterday</option>
                        <option value="3">A week ago</option>
                        <option value="4">A month ago</option>
                        <option value="5">Three months ago</option>
                        <option value="6">Four months ago</option>
                        <option value="7">Six months ago</option>
                        <option value="8">A year ago</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-1">
                <div class="form-group">
                        <div class="col-md-12">
                            <button id="singlebutton" name="singlebutton" class="btn btn-primary" onclick="renderAnalytics();"><br/>Filter</button>
                        </div>
                </div>
            </div>
            
        </div>
        
    </fieldset>
</div>

<div id="resultSet"></div>

<script type="text/javascript">

$( function() {
    $( ".datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: "dd-mm-yy"
    });
  } );

function renderAnalytics(){
 
    filterFrom          = $("#filterFrom").val();
    filterTo            = $("#filterTo").val();
    filterTags          = $("#filterTags").val();
    filterWordsMoreThan = $("#filterWordsMoreThan").val();
    filterWordsLessThan = $("#filterWordsLessThan").val();
    filterGrouping      = $("#filterGrouping").val();
    filterCompareWith   = $("#filterCompareWith").val();

    $("#resultSet").html("<div class=\'mt-5\' style=\'border:1px solid gray; padding: 40px; font-size: 18px; background-color:#EEE\'>Rendering, please wait</div>");
    $.post( "admin.php?module=wiki&op=analytics&command=show", { 
                                                                filterWordsMoreThan  : filterWordsMoreThan, 
                                                                filterWordsLessThan  : filterWordsLessThan, 
                                                                filterGrouping       : filterGrouping, 
                                                                filterCompareWith    : filterCompareWith, 
                                                                filterFrom           : filterFrom, 
                                                                filterTo             : filterTo,
                                                                filterTags           : filterTags})
      .done(function( data ) {
            $("#resultSet").html(data);
            
            $("#analyticsTable").DataTable({
            dom: \'Bfrtip\',
                "order": [[ 3, "desc" ]],
                buttons: [ \'copy\', \'excel\', \'pdf\' ]
            }
    );
      });   
}
</script>';