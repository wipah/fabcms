<?php

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Licenses', 'admin.php?module=licenses');

$this->addJsFile('//cdnjs.cloudflare.com/ajax/libs/tinymce/5.0.16/tinymce.min.js',true);

if (isset($_GET['ID'])) {

    $ID = (int) $_GET['ID'];

    $action = 'admin.php?module=licenses&op=crud&ID=' . $ID . '&save';
    $template->navBarAddItem('Edit license (ID: ' . $ID .')');

    if (isset($_GET['save'])){

        $query = 'UPDATE ' . $db->prefix . 'licenses_licenses
        SET name = \''. $core->in($_POST['name']) .'\',
            description = \''. $core->in($_POST['description']) .'\',
            allow_derivate_works = \''. ( (int) $_POST['derivate_works'] ) .'\',
            allow_share = \''. ( (int) $_POST['sharing'] ) .'\',
            mandatory_credits = \''. ( (int) $_POST['mandatory_credits'] ) .'\'
            WHERE ID = ' . $ID . '
            LIMIT 1
        ';
        
        if (!$db->query($query)){
            echo 'Query error. <pre>' . $query . '</pre>';
            return;
        }

        echo 'License was updated';
    }

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'licenses_licenses
              WHERE ID = ' . $ID ;

    if (!$result = $db->query($query)){
        echo '<strong>Query error:</strong>
            <pre>' . $query . '</pre>';

        return;
    }

    if (!$db->affected_rows){
        echo 'No licenses found.';
        return;
    }

    $row = mysqli_fetch_assoc($result);

} else if (isset($_GET['master_ID'])) {
    $action = 'admin.php?module=licenses&op=crud&new&master_ID=' . ( (int) $_GET['master_ID']) . '&saveNew';
} else {
    $action = 'admin.php?module=licenses&op=crud&new&saveNew';
}

if (isset($_GET['saveNew'])) {

    $name        = $core->in($_POST['name'], true);
    $description = $core->in($_POST['description'], true);
    $language = $core->in($_POST['lang'], true);

    if (empty($name)){
        echo 'Name is empty';
        return;
    }

    if (empty($description)){
        echo 'Description is empty';
        return;
    }

    //Check if master_ID is passed and valid
    if (isset($_GET['master_ID'])) {
        $master_ID = (int) $_GET['master_ID'];


        $template->navBarAddItem('Save new license (Master ID: ' . $ID .', language: ' . $language . ')');

        if (empty($language)){
            echo 'Language not passed';
            return;
        }
        $query = 'SELECT ID 
                  FROM ' . $db->prefix .'licenses_master
                  WHERE ID = ' . $master_ID . '
                  LIMIT 1';

        

        if (!$db->query($query)){
            echo '<strong>Query error</strong>. ' . $query;
            return;
        }

        if (!$db->affected_rows){
            echo 'Master ID not exists.';
            return;
        } else {
            echo '&bull; Master ID exists.<br/>';
        }

        // Check if language already exists
        $query = 'SELECT ID 
                  FROM ' . $db->prefix . 'licenses_licenses 
                  WHERE master_ID = ' . $master_ID . '
                  AND lang = \'' . $language . '\'
                  LIMIT 1;';

        

        if (!$db->query($query)){
            echo '&bull; Query error. <pre>' . $query . '</pre>';
            return;
        }

        if ($db->affected_rows){
            echo '&bull; Language ' . $language . ' already exists.';
            return;
        } else {
            echo '&bull; Language ' . $language .' does not exists. <br/>';
        }

        $query = 'INSERT INTO ' . $db->prefix . 'licenses_licenses 
                  (
                  master_ID, 
                  lang, 
                  name, 
                  description, 
                  allow_derivate_works, 
                  allow_share, 
                  mandatory_credits
                  )
                  VALUES
                  (
                  ' . $master_ID . ',
                  \'' . $language . '\',
                  \'' . $name . '\',
                  \'' . $description . '\',
                  ' . ( (int) $_POST['allow_derivate_works'] === 1 ? '1': '0' ) . ',
                  ' . ( (int) $_POST['allow_share']  === 1 ? '1': '0' ) . ',
                  ' . ( (int) $_POST['mandatory_credits']  === 1 ? '1': '0' ) . '
                  )';
        

        if (!$db->query($query)){
            echo 'Query error. ' . $query;
            return;
        }

        echo '&bull; License was added. <a href="admin.php?module=licenses&op=crued&ID=' . $db->insert_id . '">Edit</a>.';
        return;
    } else {

        $template->navBarAddItem('Save new license');

        // We have to create a new master ID
        $query = 'INSERT INTO ' . $db->prefix . 'licenses_master 
                 (
                 ID
                 ) 
                 VALUES 
                 (
                 null
                 );';

        

        if (!$db->query($query)){
            echo '<strong>Query error.</strong>'. $query;
            return;
        }

        $master_ID = $db->insert_id;
        echo '&ebull; Master ID created: ' . $master_ID;


        $query = 'INSERT INTO ' . $db->prefix . 'licenses_licenses 
                  (
                  master_ID,
                  lang, 
                  name, 
                  description, 
                  allow_derivate_works, 
                  allow_share, 
                  mandatory_credits
                  )
                  VALUES
                  (
                  \'' . $master_ID . '\',
                  \'' . $language . '\',
                  \'' . $name . '\',
                  \'' . $description . '\',
                  ' . ( (int) $_POST['allow_derivate_works'] === 1 ? '1': '0' ) . ',
                  ' . ( (int) $_POST['allow_share']  === 1 ? '1': '0' ) . ',
                  ' . ( (int) $_POST['mandatory_credits']  === 1 ? '1': '0' ) . '
                  )';
        

        if (!$db->query($query)){
            echo 'Query error. ' . $query;
            return;
        } else {
            $ID = $db->insert_id;
            echo '&bull; License was added. 
                  <a href="admin.php?module=licenses&op=crud&ID=' .  $ID . '">Edit</a> or 
                  <a href="admin.php?module=liceses&op=crud&master_ID=' . $master_ID . '">add new language</a>.';
            return;
        }

    }
}
echo '
<form method="post" action="' . $action .'">
  <div class="form-group row">
    <label for="lang" class="col-4 col-form-label">Language</label> 
    <div class="col-8">
      <select id="lang" name="lang" class="custom-select">';

foreach ( $conf['langAllowed']  as $singleLanguage )
{
    echo '<option ' . ($row['lang'] === $singleLanguage ? 'selected' : '') . ' value="' . $singleLanguage . '">' . $singleLanguage . '</option>';
}

echo '    
      </select>
    </div>
  </div>
  <div class="form-group row">
    <label for="name" class="col-4 col-form-label">Name</label> 
    <div class="col-8">
      <div class="input-group">
        <div class="input-group-prepend">
          <div class="input-group-text">
            <i class="fa fa-tag"></i>
          </div>
        </div> 
        <input id="name" name="name" type="text" class="form-control" value="' . $row['name'] . '">
      </div>
    </div>
  </div>
  <div class="form-group row">
    <label for="description" class="col-4 col-form-label">Description</label> 
    <div class="col-8">
      <textarea id="description" name="description" cols="40" rows="5" class="form-control">' . $row['description'] . '</textarea>
    </div>
  </div>
  <div class="form-group row">
    <label class="col-4">Sharing policy</label> 
    <div class="col-8">
      <div class="custom-control custom-checkbox custom-control-inline">
        <input ' . ( (int) $row['allow_derivate_works'] === 1 ? 'checked' : '' ) . ' name="derivate_works" id="derivate_works" type="checkbox" class="custom-control-input" value="1"> 
        <label for="derivate_works" class="custom-control-label">Derivate works</label>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline">
        <input ' . ( (int) $row['allow_share'] === 1 ? 'checked' : '' ) . ' name="sharing" id="sharing" type="checkbox" class="custom-control-input" value="1"> 
        <label for="sharing" class="custom-control-label">Share</label>
      </div>
      <div class="custom-control custom-checkbox custom-control-inline">
        <input ' . ( (int) $row['mandatory_credits'] === 1 ? 'checked' : '' ) . '  name="mandatory_credits" id="mandatory_credits" type="checkbox" class="custom-control-input" value="1"> 
        <label for="mandatory_credits" class="custom-control-label">Mandatory credits</label>
      </div>
    </div>
  </div> 
  <div class="form-group row">
    <div class="offset-4 col-8">
      <button name="submit" type="submit" class="btn btn-primary">Submit</button>
    </div>
  </div>
</form>
<script>
tinymce.init({
  selector: \'textarea#description\',
  height: 500,
  menubar: false,
  plugins: [
    \'advlist autolink lists link image charmap print preview anchor\',
    \'searchreplace visualblocks code fullscreen\',
    \'insertdatetime media table paste code help wordcount\'
  ],
  toolbar: \'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help\',
  content_css: [
    \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
    \'//www.tiny.cloud/css/codepen.min.css\'
  ]
});
</script>';