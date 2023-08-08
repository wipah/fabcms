<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/06/2017
 * Time: 15:50
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->sidebar.= $template->simpleBlock('Quick op.', '&bull;<a href="admin.php?module=wiki">Wiki</a> <br/>
                                                 &bull;<a href="admin.php?module=wiki&op=categories&command=new">New category</a> <br/> 
                                                 <hr/>
                                                 &bull;<a href="admin.php?module=wiki&op=editor">Editor</a> <br/>');

if ($_GET['command'] == 'edit'){


    $formButtonCaption = 'Update';

    if (!isset($_GET['master_ID']) || !isset($_GET['ID'])){
        echo 'No master or ID passed';
        return;
    }

    $master_ID = (int) $_GET['master_ID'];
    $ID = (int) $_GET['ID'];


    // Should we save?
    if (isset($_GET['save'])){

        if (!isset($_POST['dummy'])){
            echo 'Reload detected';
            return;
        }

        $query = 'UPDATE ' . $db->prefix . 'wiki_categories_details 
        SET name = \'' . $core->in($_POST['nameCategory'], true) . '\',
        description = \'' . $core->in($_POST['descriptionCategory'], true) . '\',
        lang = \'' . $core->in($_POST['langCategory'], true) . '\'
        
        WHERE master_ID = ' . $master_ID . ' AND ID=' . $ID . ' LIMIT 1;';

        if (!$result = $db->query($query)){
            echo 'Query error.' . $query;
            return;
        }

        if (!$db->affected_rows){
            echo 'Nessuna modifica.';

        } else {
            echo 'Update OK';
        }

    }

    $formAction = 'admin.php?module=wiki&op=categories&command=edit&ID=' . $ID . '&master_ID=' . $master_ID . '&save';

    $query = 'SELECT * FROM ' . $db->prefix .'wiki_categories_masters AS M 
              LEFT JOIN ' . $db->prefix . 'wiki_categories_details AS D
                ON D.master_ID = M.ID 
              WHERE M.ID = ' . $master_ID . ' AND D.ID = ' . $ID . ' LIMIT 1;';

    if (!$result = $db->query($query)){
        echo 'Query error.';
        return;
    }

    if (!$db->affected_rows){
        echo 'No row';
        return;
    }

    $row = mysqli_fetch_array($result);
} elseif ($_GET['command'] == 'new'){
    $formButtonCaption = 'Save';

    $row['lang'] = $core->in($_GET['lang'], true);

    if (isset($_GET['master_ID'])){
        $formAction = 'admin.php?module=wiki&op=categories&command=new&master_ID=' . ( (int) $_GET['master_ID']) . '&save';
    } else {
        $formAction = 'admin.php?module=wiki&op=categories&command=new&save';
    }

    if (isset($_GET['save'])){
        if (!isset($_POST['dummy'])){
            echo 'Reload detected';
            return;
        }


        if (!isset($_GET['master_ID']) ) {
            $query = 'INSERT INTO ' . $db->prefix . 'wiki_categories_masters (type) VALUES (1)';

            if (!$result = $db->query($query)){
                echo 'Query error.';
                return;
            }

            $master_ID = $db->insert_id;
        } else {
            $master_ID = (int) $_GET['master_ID'];
        }

        $name = $core->in($_POST['nameCategory'],true);
        $description = $core->in($_POST['descriptionCategory'],true);
        $lang = $core->in($_POST['langCategory'],true);

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_categories_details
         (
             name,
             description,
             lang,
             master_ID
         )
         VALUES
         (
             \'' . $name . '\',
             \'' . $description . '\',
             \'' . $lang . '\',
             \'' . $master_ID . '\'
         );';

        if (!$db->query($query)){
            echo 'Query error. ' . $query;
        }

        echo $db->insert_id;

    }

}


/*
 * Build language select
 */

$languageSelect = '
<!-- Select Basic -->
<div class="form-group">
  <label class="col-md-4 control-label" for="langCategory">Language</label>
  <div class="col-md-4">
    <select id="langCategory" name="langCategory" class="form-control">';

foreach ($conf['langAllowed'] as $singleLang){
    $languageSelect .= '<option ' . ( ($row['lang'] == $singleLang || $_GET['lang'] == $singleLang ) ? 'selected="selected"' : '' ) . ' value="' . $singleLang . '">' . $singleLang . '</option>';
}
$languageSelect .='    </select>
  </div>
</div>';

echo '

<form class="form-horizontal" method="post" action="' . $formAction . '">
    <fieldset>
    <input type="hidden" name="dummy" id="dummy">    
    <!-- Form Name -->
    <legend>Category editor</legend>
    
    <!-- Text input-->
    <div class="form-group">
      <label class="col-md-4 control-label" for="nameCategory">Name</label>  
      <div class="col-md-4">
      <input id="nameCategory" value="' . $row['name'] . '" name="nameCategory" type="text" placeholder="Category Name" class="form-control input-md">
      <span class="help-block">Category name</span>  
      </div>
    </div>
    
    
    ' . $languageSelect . '
    <!-- Textarea -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="descriptionCategory">Description</label>
      <div class="col-md-4">                     
        <textarea class="form-control" id="descriptionCategory" name="descriptionCategory">' . $row['description'] . '</textarea>
      </div>
    </div>
    
    <!-- Button -->
    <div class="form-group">
      <label class="col-md-4 control-label" for="singlebutton">Operations</label>
      <div class="col-md-4">
        <button id="singlebutton" name="singlebutton" class="btn btn-primary">' . $formButtonCaption .'</button>
      </div>
    </div>
    
    </fieldset>
</form>';