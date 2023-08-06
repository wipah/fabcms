<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/04/2018
 * Time: 11:10
 */

if (!$core->loaded || !$user->isAdmin)
    die("Security");

/*
 *
 * [TYPE]
 * 0 = debug / info;
 * 1 = log;
 * 2 = warning;
 * 3 = error;
 * 4 = critical;
 *
 */

$query = 'SELECT DISTINCT(module) 
          FROM ' . $db->prefix . 'relog';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error.';

    return;
}

if (!$db->numRows) {
    $optionModules = 'No module';

    return;
}

$optionModules = '';
while ($row = mysqli_fetch_assoc($result)) {
    $optionModules .= '<option value="' . $row['module'] . '">' . $row['module'] . '</option>';
}

echo '<h2>Relog monitor</h2>
<div class="row fabCMSFilterBox">
    
    <div class="col-md-3">
        Error level: <br/>
        <select class="form-control" multiple="multiple" id="type">
            <option value="0">Debug / Info</option>
            <option value="1">Log</option>
            <option value="2">Warning</option>
            <option value="3">Error</option>
            <option value="4">Critical</option>
        </select>
    </div>
    
    <div class="col-md-3">
        Modules: <br/>
        <select class="form-control" multiple id="module">
        ' . $optionModules . '
        </select>
    </div>
    
    <div class="col-md-3">
        From date: <br/>
            <input class="form-control" id="fromDate"> <br/>
        To Date: <br/>
            <input class="form-control" id="toDate">
    </div>
    
    <div class="col-md-1">
        <button class="form-control" onclick="search();" type="button">Search</button>
    </div>
</div>

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Error log</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="relogContent" class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<h2>Result</h2>

<div id="relogResult"></div>

<script type="text/javascript">
$( function() {
    $( "#fromDate, #toDate" ).datepicker( { dateFormat: \'yy-mm-dd\' });
} );
    
function show(ID) {
    $.post( "admin.php?module=relog&op=ajaxShow", { ID: ID})
        .done(function( data ) {
            $("#relogContent").html(data);
    });
    
    $(\'#exampleModalCenter\').modal(\'toggle\')
}

function search() {
    type        =   $("#type").val();
    module      =   $("#module").val();
    fromDate    =   $("#fromDate").val();
    toDate      =   $("#toDate").val();
    
    $("#relogResult").html("Searching for logs. Please wait.");
   
    $.post( "admin.php?module=relog&op=ajaxSearch", { type: type,
                                                     module: module,
                                                     fromDate: fromDate,
                                                     toDate : toDate 
                                                     })
    .done(function( data ) {
        $("#relogResult").html(data);
      
        $(\'#tableRelog\').DataTable( {
        "order": [[ 0, "desc" ]]
    } );
     
    });
}  
</script>';