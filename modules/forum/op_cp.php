<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/11/2017
 * Time: 09:26
 */

if (!$core->loaded)
    die ("Direct call");

if ($path[3] == 'updateavatar') {
    require_once 'op_cp_ajax_update_avatar.php';

    return;
}

if ($path[3] == 'updatenotify') {
    require_once 'op_cp_ajax_update_notify.php';

    return;
}

$template->navBarAddItem('Forum',$URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($language->get('forum', 'cpControlPanel') );

if (!$user->logged) {
    echo '<div class="alert alert-warning" role="alert">
            ' . (sprintf($language->get('forum', 'cpMustBeLogged', null), $URI->getBaseUri() . 'user/')) . '
          </div>';

    return;
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'forum_user_config 
          WHERE user_ID = ' . $user->ID . ' 
          LIMIT 1';


if (!$result = $db->query($query)) {
    echo 'Query error.';

    return;
}

if (!$db->affected_rows) {
    echo '<div class="alert alert-info">
            <strong>Info.</strong> ' . $language->get('forum', 'cpPreferencesAreNotPresent') . '.
          </div>';
}

$row = mysqli_fetch_assoc($result);

echo '
<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#home">Avatar</a></li>
  <li class=""><a data-toggle="tab" href="#notify">Notify</a></li>
</ul>

<div class="tab-content">
  <div id="home" class="tab-pane fade in active">
    <p>
        
        
        <div class="form-horizontal">
            <fieldset>
            
            <!-- Form Name -->
            <legend>Avatar selection</legend>
            
            <!-- Multiple Radios -->
            <div class="form-group">
              <label class="col-md-4 control-label" for="radios">' . $language->get('forum', 'cpAvatarType') . '</label>
              <div class="col-md-4">
              <div class="radio">
                <label for="radios-0">
                  <input disabled ' . ((int)$row['user_avatar_type'] == 0 ? ' checked ' : '') . ' type="radio" name="radios" id="radios-0" value="1" checked="checked">
                  Avatar
                </label>
                </div>
              <div class="radio">
                <label for="radios-1">
                  <input ' . ((int)$row['user_avatar_type'] == 1 ? ' checked ' : '') . ' type="radio" name="radios" id="radios-1" value="2">
                  Custom avatar
                </label>
                </div>
              </div>
            </div>
            
            <!-- File Button --> 
            <div class="form-group">
              <label class="col-md-4 control-label" for="filebutton">Upload avatar</label>
              <div class="col-md-4">
                <input id="sortpicture" name="sortpicture" class="input-file" type="file">
              </div>
              
              <div class="col-md-4">
                <input type="button" id="upload" value="Upload">
              </div>
             
            </div>
            
            <div class="row">
                <div id="avatarPhoto" class="col-md-12">
                
                    <div class="alert alert-info clearfix">
                        <strong>' . $language->get('forum', 'cpCurrentAvatar') . '.</strong> ' . $language->get('forum', 'cpCurrentAvatarDescription') . '    
                        <img class="img-fluid float-right" style="width:50px; height:50px;" src="' . $URI->getBaseUri(true) . '/modules/forum/assets/custom_avatars/' . $row['user_avatar'] . '" alt="profilephoto">
                     </div>
                </div> 
            </div>
            </fieldset>
        </div>
        
        
    </p>
  </div>
  <div id="notify" class="tab-pane">
    <p>
    <div class="form-horizontal">
        <fieldset>
        
        <!-- Form Name -->
        <legend>Notify settings</legend>
        
        <!-- Select Basic -->
        <div class="form-group">
          <label class="col-md-4 control-label" for="selectbasic">Status</label>
          <div class="col-md-4">
            <select id="emailNotifySelect" name="emailNotifySelect" class="form-control">
              <option ' . ((int)$row['email_notify'] == 0 ? 'selected' : '') . ' value="0">Disabled</option>
              <option ' . ((int)$row['email_notify'] == 1 ? 'selected' : '') . ' value="1">Enabled</option>
            </select>
          </div>
          
          <div class="col-md-4"><input type="button" id="updateNotifyPreferences" value="Update"></div>
        </div>
        <div class="row">
            <div class="col-md-12" id="notifyResult"></div>
        </div>
        </fieldset>
    </div>
    </p>
  </div>
</div>
';

$theScript = '

$(\'#upload\').on(\'click\', function() {
    var file_data = $(\'#sortpicture\').prop(\'files\')[0];   
    var form_data = new FormData();                  
    form_data.append(\'file\', file_data);
           
    $("#avatarPhoto").html("Upload...");                             
    $.ajax({
                url: "' . $URI->getBaseUri() . '/forum/cp/updateavatar/", 
                dataType: \'text\', 
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,                         
                type: \'post\',
                success: function(php_script_response){
                    $("#avatarPhoto").html(php_script_response); // display response from the PHP script, if any
                }
     });
});

$(\'#updateNotifyPreferences\').on(\'click\', function() {
           
   $("#notifyResult").html("Updating..."); 
                                
   var status = $("#emailNotifySelect").val(); 
                               
   $.post(  "' . $URI->getBaseUri() . '/forum/cp/updatenotify/", { status: status})
    .done(function( data ) {
        $("#notifyResult").html(data);
    });

});
';
$this->addScript($theScript);