<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBar[] = '<a href="admin.php?module=user">User</a>';
$template->navBar[] = '<em>Editor</em>';


if ($_GET['op'] === 'edit') {

    // Are we going to update?
    if (isset($_GET['saveUpdate'])) {
        $this->noTemplateParse = true;

        if (!isset($_POST['ID'])) {
            echo 'No ID passed.';

            return;
        }
        $ID = (int)$_POST['ID'];

        if (strlen($_POST['username']) < 0) {
            echo 'Username is missing';

            return;
        }

        $username = $core->in($_POST['username'], true);

        if (strlen($_POST['email']) < 0 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo 'Email is missing or is abnormal';

            return;
        }

        $tags = $core->in($_POST['tags'], true);
        $email = $core->in($_POST['email'], true);
        $name = $core->in($_POST['name'], true);
        $surname = $core->in($_POST['surname'], true);
        $group = (int)$_POST['group'];

        $_POST['enabled'] === 'true' ? $enabled = 1 : $enabled = 0;
        $_POST['newsletter'] === 'true' ? $newsletter = 1 : $newsletter = 0;

        // Obtain the user
        $query = 'SELECT * FROM ' . $db->prefix . 'users WHERE ID = \'' . $ID . '\' LIMIT 1;';

        $result = $db->query($query);
        $row = mysqli_fetch_assoc($result);

        echo '<h1>Updating the user ' . $row['ID'] . ' (<em>' . $row['username'] . ' <em>)</h1>';

        if ($row['email'] !== $email && $user->checkIfEmailExists($email)) {
            echo 'This email is already registered';

            return;
        }

        if ($row['username'] !== $username && $user->checkIfUserExists($username)) {
            echo 'This username (' . htmlentities($username) . ') is already registered';

            return;
        }

        // Update tags
        $tags = explode(',', $tags);
        if ($user->updateTags($ID, $tags) !== 1) {
            echo 'Error. ';
        }

        $query = 'UPDATE ' . $db->prefix . 'users
        SET
            group_ID = \'' . $group . '\',
            username = \'' . $username . '\',
            name = \'' . $name . '\',
            surname = \'' . $surname . '\',
            email = \'' . $email . '\',
            enabled = \'' . $enabled . '\',
            newsletter = \'' . $newsletter . '\'
            WHERE ID = ' . $ID . '
            LIMIT 1';

        if ($db->query($query)) {
            $log->write('user_update', 'user', 'ID:' . $ID);

            echo '<div class="alert alert-success">
                    <strong>Success!</strong>The user was updated.
                </div>';

            if ($group === 1) {
                echo '<div class="alert alert-warning">
                        <strong>Warning!</strong> User was set as <strong>admin</strong> with full access on website.
                      </div>';
            }
        } else {
            echo 'Query error: ' . $query;
        }

        return;
    }

    if (!isset($_GET['ID'])) {
        echo 'No ID was passed';

        return;
    }

    $postCommand = '&op=edit&saveUpdate&ID=' . $ID . '&save';

    $ID = (int)$_GET['ID'];

    $query = 'SELECT U.*,
                     GROUP_CONCAT(DISTINCT T.tag SEPARATOR \', \') AS tags 
              FROM ' . $db->prefix . 'users AS U
              LEFT JOIN ' . $db->prefix . 'users_tags AS T
                ON U.ID = T.user_ID 
              WHERE U.ID = ' . $ID . ' LIMIT 1;';

    if (!$result = $db->query($query)) {
        echo 'Query error: ' . $query;

        return;
    }
    $row = mysqli_fetch_assoc($result);
} else {

    $postCommand = '&op=new&save';

    if (isset($_GET['save'])) {
        $this->noTemplateParse = true;

        // @todo: Clean up this code. Is redundant.

        $username = $core->in($_POST['username'], true);

        if (strlen($_POST['email']) < 0 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo 'Email is missing or is abnormal';

            return;
        }
        $tags = $core->in($_POST['tags'], true);
        $email = $core->in($_POST['email'], true);
        $name = $core->in($_POST['name'], true);
        $surname = $core->in($_POST['surname'], true);
        $group = (int)$_POST['group'];

        $_POST['enabled'] === 'true' ? $enabled = 1 : $enabled = 0;
        $_POST['newsletter'] === 'true' ? $newsletter = 1 : $newsletter = 0;

        // Obtain the user
        $query = 'SELECT * FROM ' . $db->prefix . 'users WHERE ID = \'' . $ID . '\' LIMIT 1;';

        $result = $db->query($query);
        $row = mysqli_fetch_assoc($result);

        echo '<h1>Updating the user ' . $row['ID'] . ' (<em>' . $row['username'] . ' <em>)</h1>';

        if ($row['email'] !== $email && $user->checkIfEmailExists($email)) {
            echo 'This email is already registered';

            return;
        }

        if ($row['username'] !== $username && $user->checkIfUserExists($username)) {
            echo 'This username (' . htmlentities($username) . ') is already registered';

            return;
        }

        // Generate a random password
        $password = bin2hex(random_bytes(6));

        $query = '
        INSERT INTO ' . $db->prefix . 'users 
        (
            group_ID,
            username,
            password,
            name,
            surname,
            email,
            enabled,
            newsletter
        )
        VALUES
        (
            ' . $group . ',
            \'' . $username . '\',
            \'' . ($user->getPasswordHash($password)) . '\',
            \'' . $name . '\',
            \'' . $surname . '\',
            \'' . $email . '\',
            ' . $enabled . ',
            ' . $newsletter . '
        )';

        if (!$db->query($query)) {
            echo 'Query error. ' . $query;
        } else {


            // Update tags
            $tags = explode(',', $tags);
            if ($user->updateTags($db->insert_id, $tags) !== 1) {
                echo 'Error. ';
            }

            echo 'User inserted. Password is: ' . $password;

            return;
        }
    }
}


// Build registered group select
$query = 'SELECT * 
          FROM ' . $db->prefix . 'users_groups
          ORDER by group_order ASC';

if (!$resultGroups = $db->query($query)) {
    echo 'Query error while selecting groups. ' . $query;

    return;
}

if (!$db->affected_rows) {
    echo 'No groups';

    return;
}

while ($rowGroups = mysqli_fetch_assoc($resultGroups)) {
    $selectGroup .= ' <option ' . ((int)$row['group_ID'] === (int)$rowGroups['ID'] ? 'selected="selected"' : '') .
        ' value="' . $rowGroups['ID'] . '">' . $rowGroups['group_name'] .
        '</option>';
}

echo '
<div class="form-horizontal">
    <fieldset>
    
    <legend>User detail</legend>
    
      
    <div class="form-group row">
      <label class="col-md-4 control-label" for="username">Username</label>  
      <div class="col-md-4">
      <input id="username" name="username" type="text" placeholder="Username" class="form-control input-md" value ="' . $row['username'] . '" required="">
      <span class="help-block">Username</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="name">Name</label>  
      <div class="col-md-4">
      <input id="name" name="name" type="text" placeholder="Name" class="form-control input-md" value ="' . $row['name'] . '">
      <span class="help-block">Name of the user</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="surname">Surname</label>  
      <div class="col-md-4">
      <input value="' . $row['surname'] . '" id="surname" name="surname" type="text" placeholder="Surname" class="form-control input-md">
      <span class="help-block">Surname of the user</span>  
      </div>
    </div>
    
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="group">Group</label>
      <div class="col-md-4">
        <select id="group" name="group" class="form-control">
            ' . $selectGroup . '
        </select>
      </div>
    </div>
    
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="tags">Tags</label>  
      <div class="col-md-4">
      <input value="' . $row['tags'] . '" id="tags" name="tags" type="text" placeholder="Tags" class="form-control input-md">
      <span class="help-block">Tags, comma separated. IE: tag1, tag2, tag3</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="email">Email</label>  
      <div class="col-md-4">
      <input value="' . $row['email'] . '"  id="email" name="email" type="text" placeholder="Email" class="form-control input-md" required="">
      <span class="help-block">Email of the user</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="newseltter">Newsletter</label>
      <div class="col-md-4">
        <select id="newsletter" name="newsletter" class="form-control">
          <option ' . ((int)$row['newsletter'] === 0 ? 'selected="selected"' : '') . ' value="0">Not enabled</option>
          <option ' . ((int)$row['newsletter'] === 1 ? 'selected="selected"' : '') . ' value="1">Enabled</option>
        </select>
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="enabled">Enabled</label>
      <div class="col-md-4">
        <select id="enabled" name="enabled" class="form-control">
          <option ' . ((int)$row['enabled'] === 0 ? 'selected="selected"' : '') . '  value="0">User not enabled</option>
          <option ' . ((int)$row['enabled'] === 1 ? 'selected="selected"' : '') . '  value="1">User enabled</option>
        </select>
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="buttonSubmit">Operation</label>
      <div class="col-md-4">
        <button onclick="updateUser();" id="buttonSubmit" name="buttonSubmit" class="btn btn-primary">Update</button>
      </div>
    </div>
    
    </fieldset>
</div>


<div class="form-horizontal">
<fieldset>

    <legend>Password change</legend>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="new_password">Password</label>  
      <div class="col-md-4">
      <input id="new_password" name="new_password" type="password" placeholder="Password" class="form-control input-md" required="">
      <span class="help-block">Password</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="new_password_confirm">New password</label>  
      <div class="col-md-4">
      <input id="new_password_confirm" name="new_password_confirm" type="password" placeholder="New password" class="form-control input-md" required="">
      <span class="help-block">Password confirm</span>  
      </div>
    </div>
    
    <div class="form-group row">
      <label class="col-md-4 control-label" for="buttonSubmit">Operation</label>
      <div class="col-md-4">
        <button onclick="passwordChange();" id="buttonSubmit" name="buttonSubmit" class="btn btn-primary">Update password</button>
      </div>
    </div>

</fieldset>
</div>

<a name="#updateResult" />
<div id="userUpdateResult"></div>

<script type="text/javascript">
    function updateUser(){
        
        username    = $("#username").val();
        name        = $("#name").val();
        tags        = $("#tags").val();
        surname     = $("#surname").val();
        email       = $("#email").val();
        group       = $("#group").val();
  
        $("#enabled").val()     === "1" ? enabled       = true : enabled    = false;
        $("#newsletter").val()  === "1" ? newsletter    = true : newsletter = false;

        if (username.length < 1){
            alert ("No username");
            return;
        }

        if (email.length < 1){
            alert ("No password");
            return;
        }

        $.post( "admin.php?module=user' . $postCommand . '", { username  : username, 
                                ' . ($_GET['op'] === 'edit' ? 'ID        : ' . $ID . ',' : '') . '
                                                               tags,
                                                               name      : name,
                                                               surname   : surname,
                                                               email     : email,
                                                               enabled   : enabled,
                                                               newsletter: newsletter,
                                                               group     : group,
                                                               })
            .done(function( data ) {
                $("#userUpdateResult").empty().append(data);
        });
    }

    function passwordChange(){
        password = $("#new_password").val();
        password_confirm = $("#new_password_confirm").val();

        if (password !== password_confirm){
            alert ("Password mismatch");
            return;
        }

        $.post( "admin.php?module=user&op=updatePassword&ID=' . $ID . '&save", { password: password})
            .done(function( data ) {
                $("#userUpdateResult").empty().append(data);
        });
    }
</script>';