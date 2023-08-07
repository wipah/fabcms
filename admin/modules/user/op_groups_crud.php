<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/07/2018
 * Time: 10:12
 */


if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBar[] = '<a href="admin.php?module=user">User</a>';
$template->navBar[] = '<a href="admin.php?module=user&op=groups">Groups</a>';
$template->navBar[] = '<em>Editor</em>';

if ( $_GET['command'] === 'edit'){

    if (!isset($_GET['ID'])){
        echo 'ID is missing';
        return;
    }

    $ID = (int) $_GET['ID'];

    if ($ID < 3){
        echo 'Cannot edit this group because it is a system group (ID 1, 2 or 3).';
        return;
    }

    // Should we save?
    if (isset($_GET['save'])) {

        if (!isset($_POST['dummy'])){
            echo 'Reload detected';
            return;
        }

        $groupName      =   $core->in($_POST['groupName'], true);
        $groupType      =   (int) $_POST['groupType'];
        $groupOrder     =   (int) $_POST['groupOrder'];

        $query = 'UPDATE ' . $db->prefix . 'users_groups 
                  SET group_name    = \'' . $groupName    . '\',
                      group_type    =   ' . $groupType    . ',
                      group_order   =   ' . $groupOrder   . '
                  WHERE ID = ' . $ID . '
                  LIMIT 1
                ';

        if (!$db->query($query)){
            echo 'Query error. ' . $query;
            return;
        } else {
            echo 'Update completed.';
        }

    }

    $action = 'admin.php?module=user&op=groups&command=edit&ID=' . $ID . '&save';

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'users_groups
              WHERE ID = ' . $ID . ' LIMIT 1';

    if (!$result = $db->query($query)){
        echo 'Query error.' . $query;
        return;
    }

    if (!$db->affected_rows){
        echo 'No row';
        return;
    }

    $row = mysqli_fetch_assoc($result);


}   else    {
    $action = 'admin.php?module=user&op=groups&command=new&save';

    // Should we save?
    if (isset($_GET['save'])){
        $groupName      =   $core->in($_POST['groupName'], true);
        $groupType      =   (int) $_POST['groupType'];
        $groupOrder     =   (int) $_POST['groupOrder'];

        $query = 'INSERT INTO ' . $db->prefix . 'users_groups 
                  (
                    group_name, 
                    group_type, 
                    group_order
                  )
                  VALUES
                  (
                    \'' . $groupName . '\',
                    \'' . $groupType . '\',
                    \'' . $groupOrder . '\'
                  )';

        if (!$db->query($query)){
            echo 'Query error. ' . $query;
            return;
        }

        echo 'Group created. <a href="admin.php?module=user&op=groups&command=edit&ID=' . $db->lastInsertID . '">Click here</a> to edit the group';
        return;
    }
}

echo '
<form method="post" action="' . $action . '" class="form-horizontal">
<fieldset>
<input type="hidden" name="dummy" id="dummy">

<!-- Form Name -->
<legend>Group editor</legend>

<!-- Text input-->
<div class="form-group row">
  <label class="col-md-4 control-label" for="groupName">Group name</label>  
  <div class="col-md-4">
  <input value="' . $row['group_name'] . '" id="groupName" name="groupName" type="text" placeholder="Group name" class="form-control input-md" required="">
  <span class="help-block">Group name</span>  
  </div>
</div>

<!-- Select Basic -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="groupType">Group Type</label>
  <div class="col-md-4">
    <select id="groupType" name="groupType" class="form-control">
      <option ' . ( (int) $row['group_type'] === 1 ? 'selected' : '' ) . ' value="1">Admin</option>
      <option ' . ( (int) $row['group_type'] === 2 ? 'selected' : '' ) . ' value="2">Registered</option>
      <option ' . ( (int) $row['group_type'] === 3 ? 'selected' : '' ) . ' value="3">Guest</option>
    </select>
  </div>
</div>

<!-- Text input-->
<div class="form-group row">
  <label class="col-md-4 control-label" for="groupOrder">Group order</label>  
  <div class="col-md-4">
  <input value="' . $row['group_order'] . '" id="groupOrder" name="groupOrder" type="text" placeholder="Group order" class="form-control input-md">
  <span class="help-block">Group order</span>  
  </div>
</div>

<!-- Button -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="singlebutton">Operations</label>
  <div class="col-md-4">
    <button type="submit" id="singlebutton" name="singlebutton" class="btn btn-primary">Update</button>
  </div>
</div>

</fieldset>
</form>';