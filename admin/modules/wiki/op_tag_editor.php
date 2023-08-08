<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/03/2017
 * Time: 17:25
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Tag editor', 'admin.php?module=wiki&op=tag');

if ($_GET['command'] == 'edit'){
    if (!isset($_GET['ID'])){
        echo 'ID not passed';
        return;
    }

    $ID = (int) $_GET['ID'];

    $post = 'admin.php?module=wiki&op=tag&command=edit&ID=' . $ID . '&save';

    if (isset($_GET['save'])){
        if (!isset($_POST['dummy'])) {
            echo 'Reload detected';
            return;
        }

        $query = 'UPDATE ' . $db->prefix . 'wiki_tags_menu
        SET
        language = \'' . $core->in($_POST['lang'], true) . '\',
        depth = ' . $core->in($_POST['depth'], true) . ',
        tag = \'' . $core->in($_POST['tag'], true) . '\',
        URI = \'' . $core->in($_POST['uri'], true) . '\',
        name = \'' . $core->in($_POST['name'], true) . '\'
        WHERE ID = ' . $ID . '
        LIMIT 1';

        if (!$db->query($query)){
            echo '<pre>' . $query . '</pre>';
        } else {
            echo 'Update ok.';
        }
    }

    $query = 'SELECT * FROM ' . $db->prefix . 'wiki_tags_menu WHERE ID = ' . $ID . ' LIMIT 1;';

    if (!$result = $db->query($query)){
        echo '<pre>' . $query . '</pre>';
        return;
    }

    if (!$db->affected_rows){
        echo 'No menu. ' . $query;
        return;
    }

    $row = mysqli_fetch_assoc($result);

    // Build lang option
    foreach ($conf['langAllowed'] as $singleLang) {
        $langOption .= '<option ' . ($row['lang'] == $singleLang ? 'selected="selected"' : '') . ' value="' . $singleLang . '">' . $singleLang . '</option>';
    }
} else {

    if (isset($_GET['save'])){

        if (!isset($_POST['dummy'])){
            echo 'Reload detected.';
            return;
        }

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_tags_menu
        (language, 
         depth, 
         tag, 
         URI, 
         name)
        VALUES
        (
        \'' . $core->in($_POST['lang']) .'\',
        \'' . $core->in($_POST['depth']) .'\',
        \'' . $core->in($_POST['tag']) .'\',
        \'' . $core->in($_POST['uri']) .'\',
        \'' . $core->in($_POST['name']) .'\'
        )
        ';


        if (!$db->query($query)){
            echo 'Query error. ' . $query;
            return;
        }

        echo 'Menu was inserted. <a href="admin.php?module=wiki&op=tag&command=edit&ID=' . $db->insert_id . '">Click here</a> to edit.';
        return;

    } else {
        $post = 'admin.php?module=wiki&op=tag&command=new&save';
        // Build lang option
        foreach ($conf['langAllowed'] as $singleLang) {
            $langOption .= '<option value="' . $singleLang . '">' . $singleLang . '</option>';
        }

    }

}

echo '<form method="post" action="' . $post . '">
<input type="hidden" id="dummy" name="dummy">
     <div class="form-group ">
      <label class="control-label requiredField" for="lang">
       Language
       <span class="asteriskField">
        *
       </span>
      </label>
      <select class="select form-control" id="lang" name="lang">
      ' . $langOption . '
      </select>
     </div>
     <div class="form-group ">
      <label class="control-label requiredField" for="depth">
       Depth
       <span class="asteriskField">
        *
       </span>
      </label>
      <input class="form-control" id="depth" name="depth" value="' . $row['depth'] . '" type="text"/>
     </div>
     <div class="form-group ">
      <label class="control-label " for="tag">
       Tag
      </label>
      <input class="form-control" id="tag" name="tag" value="' . $row['tag'] . '" type="text"/>
     </div>
     <div class="form-group ">
      <label class="control-label " for="uri">
       URI
      </label>
      <input class="form-control" id="uri" name="uri" value="' . $row['URI'] . '" type="text"/>
     </div>
     <div class="form-group ">
      <label class="control-label " for="name">
       Name
      </label>
      <input class="form-control" id="name" name="name" value="' . $row['name'] . '"  type="text"/>
     </div>
     <div class="form-group">
      <div>
       <button class="btn btn-primary " name="submit" type="submit">
        Submit
       </button>
      </div>
     </div>
    </form>';