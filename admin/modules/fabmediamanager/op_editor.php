<?php
if (!$core->adminBootCheck())
    die("Check not passed");

if (!isset($_GET['ID'])){
    echo 'Media ID was not passed';
    return;
}

if (isset($_GET['save'])) {

}

$ID = (int) $_GET['ID'];

$query = 'SELECT * FROM ' . $db->prefix . 'fabmedia WHERE ID = ' . $ID . ' LIMIT 1';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows){
    echo 'No row';
    return;
}

$row = mysqli_fetch_assoc($result);

echo '
<form method="post" action="admin.php?module=fabmediamanager&op=edit&save">
  <div class="form-group row">
    <label for="media_ID" class="col-4 col-form-label">ID</label> 
    <div class="col-8">
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <i class="fa fa-500px"></i>
          </div>
        </div> 
        <input disabled id="media_ID" name="media_ID" placeholder="ID" type="text" class="form-control" aria-describedby="media_IDHelpBlock" value="' . $row['ID'] . '">
      </div> 
      <span id="media_IDHelpBlock" class="form-text text-muted">Media ID</span>
    </div>
  </div>
  
  <div class="form-group row">
    <label for="filename" class="col-4 col-form-label">Filename</label> 
    <div class="col-8">
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <i class="fa fa-file-text-o"></i>
          </div>
        </div> 
        <input id="filename" name="filename" placeholder="Filename" type="text" class="form-control" value="' . $row['filename'] .  '">
      </div>
    </div>
  </div>
  
    <div class="form-group row">
    <label for="filename" class="col-4 col-form-label">Trackback</label> 
    <div class="col-8">
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <i class="fa fa-file-text-o"></i>
          </div>
        </div> 
        <input disabled id="trackback" name="trackback" placeholder="trackback" type="text" class="form-control" value="' . $row['trackback'] .  '">
      </div>
    </div>
  </div>  
  
  <div class="form-group row">
    <div class="offset-4 col-8">
      <button name="submit" type="submit" class="btn btn-primary">Update</button>
    </div>
  </div>
</form>
';