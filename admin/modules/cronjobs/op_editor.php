<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 03/06/2019
 * Time: 12:52
 */

if (!$core->adminLoaded)
    die ("Only admin");

if (!isset($_GET['ID']))
    die ("ID is missing");

$ID = (int) $_GET['ID'];

$query = 'SELECT * 
          FROM ' . $db->prefix . 'cronjobs 
          WHERE ID = ' . $ID . ' 
          LIMIT 1';

if (!$result = $db->query($query)){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No cronjob';
    return;
}

$row = mysqli_fetch_assoc($result);

echo '
<h2>Cronjob editor</h2>
<form>

  <div class="form-group row">
    <label for="module" class="col-sm-2 col-form-label">Module</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="module" name="module" value="' . $row['module'] . '">
    </div>
  </div>

  <div class="form-group row">
    <label for="operation" class="col-sm-2 col-form-label">Operation</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="operation" name="module" value="' . $row['operation'] . '">
    </div>
  </div>
  
  <div class="form-group row">
    <label for="additionalData data" class="col-sm-2 col-form-label">Additional data</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="additionalData" name="" value="' . $row['additional_data'] . '">
    </div>
  </div>

  <div class="form-group row">
    <label for="interval" class="col-sm-2 col-form-label">Interval</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="interval" name="interval" value="' . $row['interval'] . '">
    </div>
  </div>
  
  <div class="form-group row">
    <label for="latestCheck" class="col-sm-2 col-form-label">Latest check</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="latestCheck" name="latestCheck" value="' . $core->getDateTime($row['latest_check']) . '">
    </div>
  </div>
  
  <div class="form-group row">
    <label for="nextRun" class="col-sm-2 col-form-label">Latest check</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nextRun" name="nextRun" value="' . $core->getDateTime($row['next_run']) . '">
    </div>
  </div>
  
  <div class="form-group">
    <label for="lastLog">Last log</label>
    <textarea class="form-control" id="lastLog" rows="3">' . $row['log'] . '</textarea>
  </div>
  
</form>';