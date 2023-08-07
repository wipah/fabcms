<?php
if (!$core->loaded) {
    die('Direct call detected');
}

if (!$user->logged){
    echo 'I think you should be logged';
    return;
}

switch ($path[3]) {
    case 'updatephoto':
        $this->noTemplateParse = TRUE;

        $uploaddir = $conf['path']['baseDir'] .'cache/users/profile';
        $filename = basename($_FILES['file']['name']);

        $filename = str_replace('..', '', $filename);
        $filename = str_replace('.html', '', $filename);
        $filename = str_replace('.htm', '', $filename);
        $filename = str_replace('.js', '', $filename);
        $filename = str_replace('.php', '', $filename);
        $filename = str_replace('.cgi', '', $filename);
        $filename = str_replace('.java', '', $filename);
        $filename = str_replace('.php', '', $filename);
        $filename = str_replace('.php3', '', $filename);
        $filename = str_replace('.php4', '', $filename);

        $parts = pathinfo($_FILES['file']['name']);

        if ($parts['extension'] !== 'jpeg' && $parts['extension'] !== 'jpg' && $parts['extension'] !== 'png'){
            echo 'Estensione non autorizzata';
            return;
        }
        if (!is_dir($uploaddir)){
            mkdir($uploaddir, 077, true);
        }

        unlink(md5($user->ID . $conf['security']['siteKey'] ) . '.png');
        unlink(md5($user->ID . $conf['security']['siteKey'] ) . '.jpg');
        unlink(md5($user->ID . $conf['security']['siteKey'] ) . '.jpeg');

        $fileEnc = md5($user->ID . $conf['security']['siteKey'] ) . '.' . strtolower($parts['extension']);
        $uploadfile = $uploaddir . '/' . $fileEnc;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            echo '<img class="img-fluid" style="width:150px; height:150px;" src="' . $URI->getBaseUri(true) . '/cache/users/profile/' . $fileEnc . '" alt="profilephoto">';
        } else {
            echo "Possible file upload attack!\n";
        }

        return;
        break;
    case 'updatepassword':
		$this->noTemplateParse = TRUE;
		
		if ($_POST['password'] !== $_POST['password_confirm']){
			echo $language->get('user', 'userControlPanelPasswordMismatch');
			return;
		}
		
		if (strlen($_POST['password']) < $conf['security']['minimumPasswordLenght'] ){
			echo sprintf($language->get('user', 'userControlPanelPasswordTooShort'), $conf['security']['minimumPasswordLenght']);
			return;
		}
		
		$password = $user->getPasswordHash($_POST['password']);
		$query = 'UPDATE ' . $db->prefix . 'users 
				  SET password = "' . $password . '"
				  WHERE ID = ' . $user->ID . '
				  LIMIT 1';
		
		
		if (!$db->query($query)){
            echo '<div class="alert alert-danger">
                    ' . $language->get('user','cpPasswordUpdateKo') . '
                  </div>';
		} else {
			echo '<div class="alert alert-success">
                    ' . $language->get('user','cpPasswordUpdateOk') . '
                  </div>';
		}
		
		return;
		break;
		
    case 'saveprofile':
        $this->noTemplateParse = TRUE;
        $name    = $core->in($_POST['name'], true);
        $surname = $core->in($_POST['surname'], true);
        $birthdate = $core->in($_POST['birthdate'], true);
        $article_signature = $core->in($_POST['article_signature'], true);
        $privacy_profile_level = (int) $_POST['privacy_profile_level'];

        $short_biography = $core->in(
            str_ireplace(
                array(
                    '<script',
                    '<frame',
                    '<iframe',
                    '<link',
                    '<title',
                    '<style',
                    '<object',
                    '<!--'
                ),
                array(
                    '<&lt;script',
                    '<&lt;frame',
                    '<&lt;iframe',
                    '<&lt;link',
                    '<&lt;title',
                    '<&lt;style',
                    '<&lt;object',
                    '<&lt;!--'
                ), $_POST['short_biography']));
        
        $biography = $core->in(
                        str_ireplace(
                            array(
                                '<script',
                                '<frame',
                                '<iframe',
                                '<link',
                                '<title',
                                '<style',
                                '<object',
                                '<embed',
                                '<meta',
                                '<!--'
                            ),
                            array(
                                '<&lt;script',
                                '<&lt;frame',
                                '<&lt;iframe',
                                '<&lt;link',
                                '<&lt;title',
                                '<&lt;style',
                                '<&lt;object',
                                '<&lt;embed',
                                '<&lt;meta',
                                '<&lt;!--'
                        ), $_POST['biography']));

        $query = '
        UPDATE ' . $db->prefix . 'users
        SET name                    = \'' . $name . '\',
            surname                 = \'' . $surname . '\',
            birthdate               = \'' . $birthdate . '\',
            article_signature       = \'' . $article_signature . '\',
            short_biography         = \'' . $short_biography . '\',
            biography               = \'' . $biography . '\',
            privacy_profile_level   = \'' . $privacy_profile_level . '\'
        WHERE ID = ' . $user->ID . '
        LIMIT 1;
        ';


        
        if ($db->query($query)){
            $log->write('user_update_info_ok', 'user', 'ID: ' . $user->ID);

            echo $template->getCustomBox(['title'   => $language->get('user', 'cpProfilesUpdatedTitle', null),
                                          'message' => $language->get('user', 'cpProfilesUpdatedText', null),
                                          'class' => 'info']);

        } else {
            echo $template->getCustomBox('error', 'Internal error', 'Profile was not updated. We apologize!');
            $log->write('user_update_info_error', 'user', 'ID: ' . $user->ID . ', query: ' . $query);

            if ($user->isAdmin){
                echo 'Admin: ' . $query;
            }
        }
        return;
        break;

}
$template->moduleH1 = 'Control panel';
$template->navBarAddItem($language->get('user', 'user'), $URI->getBaseUri() . $this->routed);
$template->navBarAddItem($language->get('user', 'userControlPanel'));

$userInfoTab = '
<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'username') . '
    </div>

    <div class="col-md-9">
        <div class="input-group">
            <input
            id="username"
            type="text"
            class="form-control"
            placeholder="' . $language->get('user', 'username') . '"
            disabled="disabled"
            value="' . $user->username . '">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'cpProfileArticleSignature') . '
    </div>

    <div class="col-md-9">
        <div class="input-group">
            <input
            id="articleSignature"
            maxlength="32"
            type="text"
            class="form-control"
            placeholder="' . $language->get('user', 'cpProfileArticleSignature') . '"
            value="' . $user->articleSignature . '">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'name') . '
    </div>

    <div class="col-md-9">
        <div class="input-group">
            <input id="name" type="text" class="form-control" placeholder="' . $language->get('user', 'name') . '" value="' . $user->name . '">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'surname') . '
    </div>
    <div class="col-md-9">
        <div class="input-group">
            <input id="surname" class="form-control" placeholder="' . $language->get('user', 'name') . '" type="text" value="' . $user->surname . '">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'userControlPanelBirthDate') . '
    </div>
    <div class="col-md-9">
        <div class="input-group">
            <input id="birthdate" class="form-control" type="text" value="' . $user->birthdate . '">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'email') . '
    </div>

    <div class="col-md-9">
        <div class="input-group">
            <input
            id="email"
            type="text"
            class="form-control"
            placeholder="' . $language->get('user', 'email') . '"
            disabled="disabled" value="' . $user->email. '">
        </div>
    </div>
</div>
';

$privacyTab = '
<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'privacyLevel') . '
    </div>
    <div class="col-md-9">
    <select class="form-control" id="privacy_profile_level">
        <option value="1" ' . ($user->profile_privacy_level === 1 ? 'selected="selected"' : '') . ' >' . $language->get('user', 'privacyLevelVisible') . '</option>
        <option value="2" ' . ($user->profile_privacy_level === 2 ? 'selected="selected"' : '') . ' >' . $language->get('user', 'privacyLevelVisibleNoSearchEngines') . '</option>
        <option value="3" ' . ($user->profile_privacy_level === 3 ? 'selected="selected"' : '') . ' >' . $language->get('user', 'privacyLevelVisibleNoSearchEnginesCrypted') . '</option>
        <option value="4" ' . ($user->profile_privacy_level === 4 ? 'selected="selected"' : '') . ' >' . $language->get('user', 'privacyLevelHidden') . '</option>
    </select>
    </div>
</div>
';

$biographyTab = '

<div class="row">
    <div class="col-md-3">
            ' . $language->get('user', 'cpShortBiography') . '
        </div>
    
        <div class="col-md-9">
            <div class="input-group">
                <textarea
                id="shortBiography"
                class="form-control"
                placeholder="' . $language->get('user', 'cpShortBiography') . '">' . $user->shortBiography . '</textarea>
            </div>
    </div>
</div>
    
<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'biography') . '
    </div>

    <div class="col-md-9">
        <div class="input-group">
            <textarea
            id="biography"
            type="text"
            class="form-control"
            placeholder="' . $language->get('user', 'shortBiography') . '">' . $user->biography . '</textarea>
        </div>
    </div>
</div>
';

$profilePhoto = '
<div class="row">
    <div class="col-md-9">
        <input id="sortpicture" type="file" name="sortpic" />
        <button id="upload">Upload</button>
    </div>
    <div class="col-md-3"><div id="profilePhoto"></div></div>
</div>

<div id="photoUploadStatus"></div>
';

echo $template->getTabs('ss',
    array($language->get('user', 'personalData'),
        'Profile photo',
        $language->get('user', 'privacy'),
        $language->get('user', 'biography')),
    array($userInfoTab, $profilePhoto, $privacyTab, $biographyTab), []);

echo '<button onclick="updateProfile();" type="button" class="btn btn-default btn-lg float-right">
        <span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> ' . $language->get('user', 'updateProfile') . '
      </button>
      <div class="clearfix "></div>
      <div id="updateStatus"></div>';

$updatePasswordHTML = '
<div class="row">
    <div class="col-md-3">
        ' . $language->get('user', 'userControlPanelPassword') . '
    </div>

    <div class="col-md-9">
 		<div class="input-group">
            <input
            id="password"
            type="password"
            class="form-control">
        </div>
    </div>
    
    <div class="col-md-3">
        ' . $language->get('user', 'userControlPanelReinsertPassword') . '
    </div>

    <div class="col-md-9">
 		<div class="input-group">
            <input
            id="password_confirm"
            type="password"
            class="form-control">
        </div>
    </div>        		
</div>
';

echo $template->getTabs('pwd', 
		                 array($language->get('user', 'userControlPanelChangePassword')), 
		                 array($updatePasswordHTML),
		                 []);

echo '<button onclick="updatePassword();" type="button" class="btn btn-default btn-lg float-right">
        <span class="glyphicon glyphicon-floppy-saved" aria-hidden="true"></span> ' . $language->get('user', 'userControlPanelChangePassword') . '
      </button>

      <div id="updatePasswordStatus"></div>';


$this->addJsFile('//cdn.tinymce.com/4/tinymce.min.js');
$theScript = '
tinymce.init({
    selector: "textarea"
 });

$(function() {
    $( "#birthdate" ).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: \'yy-mm-dd\'
    });
});

$(\'#upload\').on(\'click\', function() {
    var file_data = $(\'#sortpicture\').prop(\'files\')[0];   
    var form_data = new FormData();                  
    form_data.append(\'file\', file_data);
                                 
    $.ajax({
                url: "' .  $URI->getBaseUri() . '/user/cp/updatephoto/", 
                dataType: \'text\', 
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,                         
                type: \'post\',
                success: function(php_script_response){
                    $("#profilePhoto").html(php_script_response); // display response from the PHP script, if any
                }
     });
});
		
function updatePassword(){
	password = $("#password").val();		
	password_confirm = $("#password_confirm").val();
		
	if (password !== password_confirm){
		alert ("' . $language->get('user', 'userControlPanelPasswordMismatch') . '");
		return;
	}
				
	$.post( "' . $URI->getBaseUri() . '/user/cp/updatepassword/", {
        password: password,
        password_confirm: password_confirm
        })
        .done(function( data ) {
            $("#updatePasswordStatus").html(data);
    });			
}
		
function updateProfile(){
    name                    =   $("#name").val();
    surname                 =   $("#surname").val();
    article_signature       =   $("#articleSignature").val();
    birthdate               =   $("#birthdate").val();
    privacy_profile_level   =   $("#privacy_profile_level").val();
    short_biography         =   tinyMCE.get(\'shortBiography\').getContent();
    biography               =   tinyMCE.get(\'biography\').getContent();

    
    $.post( "' . $URI->getBaseUri() . '/user/cp/saveprofile/", {
        name                    : name,
        article_signature       : article_signature,
        surname                 : surname,
        birthdate               : birthdate,
        privacy_profile_level   : privacy_profile_level,
        short_biography: short_biography,
        biography: biography
        })
        .done(function( data ) {
            $("#updateStatus").html(data);
    });
}';

$this->addScript($theScript);