<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/07/2018
 * Time: 10:18
 */
if (!$core->adminBootCheck())
    die("Check not passed");

if ($_GET['command'] === 'edit') {

    if (!isset($_GET['ID'])){
        echo 'ID is missing';
        return;
    }

    $ID = (int) $_GET['ID'];

    $template->navBarAddItem('FabSense','admin.php?module=fabsense');
    $template->navBarAddItem('Editing banner (ID: ' . $ID . ')','admin.php?module=fabsense&op=banner&command=edit&ID=' . $ID);

    // Should we save?
    if (isset($_GET['save'])) {

        $probability_start  = (int) $_POST['probability_start'];
        $probability_end    = (int) $_POST['probability_end'];
        $probability        = (int) $_POST['probability'];
        $code               = $core->in($_POST['code']);

        $query = '
        UPDATE ' . $db->prefix . 'sense_banner
        SET probability_progression_start   =   '  . $probability_start . ',
            probability_progression_end     =   '  . $probability_end . ',
            probability         =   '  . $probability . ',
            code                =  \''  . $code . '\'
        WHERE ID = ' . $ID . '
        LIMIT 1';

        if (!$db->query($query)){
            echo '<div class="alert alert-warning" role="alert">
                    <strong>Query error!</strong> Query error while updating banner. <pre></pre>
                  </div>';

        } else {
            echo '<div class="alert alert-success" role="alert">
                    <strong>Banner update!</strong> <a href="admin.php?module=fabsense&op=banner&command=edit&ID=' . $ID . '">Edit the banner</a>.
                  </div>';
        }

        return;
    }

    $action = 'admin.php?module=fabsense&op=banner&command=edit&ID=' . $ID . '&save';

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'sense_banner 
              WHERE ID = ' . $ID . ' 
              LIMIT 1';

    if (!$result = $db->query($query)){
        echo 'Query error.';
        return;
    }
    if (!$db->affected_rows){
        echo 'No rows';
        return;
    }

    $row = mysqli_fetch_assoc($result);

} elseif ($_GET['command'] === 'new') {

    if (!isset($_GET['hook_ID'])){
        echo 'Hook ID is missing';
        return;
    }

    $hook_ID = (int) $_GET['hook_ID'];

    $action = 'admin.php?module=fabsense&op=banner&command=new&hook_ID=' . $hook_ID . '&save';

    $template->navBarAddItem('FabSense', 'admin.php?module=fabsense');
    $template->navBarAddItem('New banner', 'admin.php?module=fabsense&op=banner&command=new');

    // Should we save?
    if (isset($_GET['save'])) {
        $probability_start  = (int) $_POST['probability_start'];
        $probability_end    = (int) $_POST['probability_end'];
        $probability        = (int) $_POST['probability'];
        $code               = $core->in($_POST['code']);

        $query = '
        INSERT INTO ' . $db->prefix . 'sense_banner 
        (
            hook_ID,
            probability_progression_start,
            probability_progression_end,
            probability,
            hits,
            code
        )
        VALUES
        (
            ' . $hook_ID . ',
            ' . $probability_start . ',        
            ' . $probability_end . ',        
            ' . $probability . ',        
            \'' . $code. '\',
            0        
        )
        ';

        if (!$db->query($query)){
            echo '<div class="panel panel-warning">
                    <div class="panel-heading">Query error</div>
                    <div class="panel-body">Query error. 
                        <pre>' . $query . '</pre>
                    </div>
                  </div>';

        } else {
            echo '<div class="panel panel-success">
                    <div class="panel-heading">Banner was created</div>
                    <div class="panel-body">The banner was created. <br/> 
                        &bull; <a href="admin.php?module=fabsense&op=banner&command=edit&ID=' . $db->lastInsertID . '">Edit the banner</a></div>
                        &bull; <a href="admin.php?module=fabsense&op=banner&command=new&hook_ID=' . $hook_ID. '">Create new banner</a></div>
                  </div>';
        }
    }

} else {
    echo 'Direct call detected';
    return;
}

$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js', true);
$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/xml/xml.min.js', true);
$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/fold/xml-fold.min.js', true);
$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/javascript/javascript.min.js', true);
$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/htmlmixed/htmlmixed.min.js', true);
$this->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/edit/closetag.min.js', true);
$this->addCSSLink('https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css',false,'all',true);

echo '
<form method="post" action="' . $action . '" class="form-horizontal">
    <fieldset>
    
    <legend>Banner editor</legend>
    
    <div class="form-group">
        <div class="row">
          <label class="col-md-2 control-label" for="hookID">Hook ID</label>  
          <div class="col-md-10">
          <input disabled id="hookID" name="hookID" type="text" placeholder="placeholder" class="form-control form-control-sm" value="' . $hook_ID. '">
          <span class="help-block">Hook associated with the banner</span>  
          </div>
      </div>
    </div>
    
    <div class="form-group">
        <div class="row">
          <label class="col-md-2 control-label" for="probability_start">Probability start</label>
          <div class="col-md-10">
            <input maxlength="3" value="' . $row['probability_progression_start']. '" id="probability_start" name="probability_start" type="text" placeholder="Probability start" class="form-control form-control-sm">
            <span class="help-block">Start of the probability</span>
          </div>
        </div>
    </div>
    
    <div class="form-group">
      <div class="row">
          <label class="col-md-2 control-label" for="probability_end">Probability end</label>
          <div class="col-md-10">
            <input maxlength="3" value="' . $row['probability_progression_end']. '" id="probability_end" name="probability_end" type="text" placeholder="Probability end" class="form-control form-control-sm">
            <span class="help-block">End of the probability</span>
          </div>
      </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label class="col-md-2 control-label" for="probability">Probabilty</label>  
            <div class="col-md-10">
                <input value="' . $row['probability']. '" id="probability" name="probability" type="text" placeholder="probability" class="form-control form-control-sm">
                <span class="help-block">Probability</span>  
            </div>
         </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label class="col-md-2 control-label" for="code">Code</label>
            <div class="col-md-10">                     
                <textarea style="width:100%; height: 400px;" class="form-control" id="code" name="code">' . $row['code'] . '</textarea>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="row">
            <label class="col-md-2 control-label" for="Operation">Update</label>
            <div class="col-md-10">
                <button id="Operation" type="submit" name="Operation" class="btn btn-info">Update</button>
            </div>
         </div>
    </div>
    
    </fieldset>
</form>
<script type="text/javascript">
  var editor = CodeMirror.fromTextArea( document.getElementById("code") , {
    lineNumbers     : true,
    autoCloseTags   : true,
    mode            : "text/html",
    matchBrackets   : true
  });
</script>';