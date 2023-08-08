<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/03/2017
 * Time: 15:54
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('FabSense', 'admin.php?module=fabsense');
$template->navBarAddItem('Editor');

if ( $_GET['command'] === 'new' )
{
    $buttonAction = 'Save hook';
    $formAction = 'admin.php?module=fabsense&op=hooks&command=new&save';

    if (isset($_GET['save'])){
        if (!isset($_POST['dummy'])){
            echo 'Reload detected';
            return;
        }

        $hookName = $core->in($_POST['hookName'], true);
        $hookLang = $core->in($_POST['hookLanguage'], true);
        $hookEnabled = $core->in($_POST['hookEnabled'], true);

        $query = 'INSERT INTO ' . $db->prefix . 'sense_hooks 
                  (hook, lang, enabled)
                  VALUES
                  (
                      \'' . $hookName . '\',
                      \'' . $hookLang . '\',
                      \'' . $hookEnabled . '\'
                  )';

        if (!$db->query($query)){
            echo '
                <div class="alert alert-warning">
                    <strong>Error!</strong> Query error. ' .  $query .'
                </div>';
        } else {
            echo '
                <div class="alert alert-warning">
                    <strong>Success!</strong> Hook was inserted. <a href="admin.php?module=fabsense&op=hooks&command=edit&ID=' . $dbinsert_id . '">Click here</a> to edit.
                </div>';
            return;
        }
    }
} elseif ($_GET['command'] == 'edit') {
    if (!isset($_GET['ID'])){
        echo 'ID was not passed';
        return;
    }

    if (!isset($_GET['ID'])) {
        echo '
        <div class="alert alert-warning">
            <strong>Error!</strong> ID was not passed.
        </div>';
        return;
    }

    $ID = (int) $_GET['ID'];
    $buttonAction = 'Update Hook';
    $formAction = 'admin.php?module=fabsense&op=hooks&command=edit&ID=' . $ID . '&save';

    // update
    if (isset($_GET['save'])){

        if (!isset($_POST['dummy'])){
            echo 'Reload detected';
            return;
        }

        $hookName = $core->in($_POST['hookName'], true);
        $hookLang = $core->in($_POST['hookLanguage'], true);
        $hookEnabled = $core->in($_POST['hookEnabled'], true);

        $query = 'UPDATE ' . $db->prefix . 'sense_hooks 
              SET lang = \'' . $hookLang . '\',
              hook = \'' . $hookName . '\',
              enabled = \'' . $hookEnabled . '\'
              WHERE ID = ' . $ID . ' LIMIT 1;';

        if (!$db->query($query)){
            echo 'Error. ' . $query;
            return;
        }

        echo '
        <div class="alert alert-success">
            <strong>Success!</strong> Hook was updated.
        </div>';

    }


    $query = 'SELECT * 
              FROM ' . $db->prefix . 'sense_hooks 
              WHERE ID = ' . $ID . ';';

    $result = $db->query($query);

    if (!$db->affected_rows){
        echo 'No hook.';
        return;
    }

    $row = mysqli_fetch_array($result);
} else {
    echo 'No handler';
    return;
}

// Build language select
$languageSelect = '
<div class="form-group">
   <div class="row">
        <label class="col-md-4 control-label" for="selectbasic">Language</label>
        <div class="col-md-4">
        <select id="hookLanguage" name="hookLanguage" class="form-control form-control-sm">';

foreach ($conf['langAllowed'] as $singleLang){
    $languageSelect .= '      <option ' . ($singleLang === $row['lang'] ? 'selected="selected"' : '') . 'value="' . ($singleLang) . '">' . $singleLang . '</option>';
}

$languageSelect .= '    </select>
    </div>
  </div>
</div>';

echo '
<h2>Hook editor</h2>
<form method="post" action="' . $formAction . '" class="form-horizontal">
    <fieldset>
    <input type="hidden" name="dummy" value="dummy">
    
    <div class="form-group">
        <div class="row">
          <label class="col-md-4 control-label" for="hookName">Hook name</label>  
          <div class="col-md-4">
          <input id="hookName" name="hookName" value="' . $row['hook'] . '" type="text" placeholder="Hook Name" class="form-control form-control-sm">
          <span class="help-block">Hook name</span>  
          </div>
      </div>
    </div>

'. $languageSelect . '

    <div class="form-group">
        <div class="row">
              <label class="col-md-4 control-label" for="hookEnabled">Enabled</label>
              <div class="col-md-4">
                <select id="hookEnabled" name="hookEnabled" class="form-control form-control-sm">
                  <option ' . ( (int) $row['enabled'] == 1 ? 'selected' : '' ) . ' value="1">Enabled</option>
                  <option ' . ( (int) $row['enabled'] == 0 ? 'selected' : '' ) . ' value="2">Disabled</option>
                </select>
              </div>
         </div>
    </div>
    
    <div class="form-group">
        <div class="row">
          <label class="col-md-4 control-label" for="button1id">Actions</label>
          <div class="col-md-8">
            <button id="button1id" name="button1id" type="submit" class="btn btn-success">' . $buttonAction . '</button>
            <button id="button2id" name="button2id" class="btn btn-danger">Delete hook</button>
          </div>
      </div>
    </div>
    
    </fieldset>
</form>';

// Gets all the banner associated
if ($_GET['command'] === 'edit') {
    $query = 'SELECT * 
          FROM ' . $db->prefix . 'sense_banner 
          WHERE hook_ID = ' . $ID . ';';

    if (!$result = $db->query($query)){
        echo 'Query error. ' . $query;
        return;
    }

    if (!$db->affected_rows){
        echo 'No banner are associated';
        return;
    }

    echo '<table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Start</th>
        <th>End</th>
        <th>Probability</th>
        <th>Hits</th>
        <th>Operation</th>
      </tr>
    </thead>
    <tbody>';

    while ($row = mysqli_fetch_assoc($result)){
        echo '<tr>
        <td>' . $row['ID'] . '</td>
        <td>' . $row['probability_progression_start'] . '</td>
        <td>' . $row['probability_progression_end'] . '</td>
        <td>' . $row['probability'] . '</td>
        <td>' . $row['hits'] . '</td>
        <td><a href="admin.php?module=fabsense&op=banner&command=edit&ID=' . $row['ID'] . '">Edit</a> - Delete</td>
      </tr>';
    }

    echo '
    </tbody>
  </table>';
}

if ($_GET['command'] !== 'new'){
    echo '<div class="row">
            <div class="col-md-12">
                <a href="admin.php?module=fabsense&op=banner&command=new&hook_ID=' . $ID . '" class="float-right button button-default">New banner</a>
            </div>
          </div>';
}
