<?php

if (!$core->loaded)
    die ("Direct call");

if (!$user->logged){
    echo $language->get('forum', 'editTopicMustBeLoggedToEdit', null);
    return;
}

if (!isset($_GET['reply_ID'])){
    echo 'No reply ID passed';
    return;
}

$template->navBarAddItem('Forum',$URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($language->get('forum', 'editReplyEditTitle', null) );

$reply_ID = (int) $_GET['reply_ID'];

$template->sidebar.= $template->simpleBlock($language->get('forum', 'topicCrudLateralSearch', null),'<form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="">
                                                    <button class="button" type="submit">' . $language->get('forum', 'topicCrudSearchSidebar', null) . '</button>      
                                              </form>');

$query = 'SELECT * 
          FROM ' . $db->prefix . 'forum_replies 
          WHERE ID = ' . $reply_ID . ' 
          ' . ( $user->isAdmin === true ? '' :  ' AND user_ID = ' . $user->ID ) . '  
          LIMIT 1';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){

    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_reply_edit_select_query_error',
                   'details'   => 'Unable select the reply. ' . $query,
    ]);
    echo 'Query error';
    return;
}

if (!$db->affected_rows){
    $relog->write(['type'      => '3',
                   'module'    => 'FORUM',
                   'operation' => 'forum_topic_reply_edit_select_no_reply',
                   'details'   => 'No reply was found. ' . $query,
    ]);
    echo 'No reply matched.';
    return;
}

$row = mysqli_fetch_assoc($result);

echo '
<div class="form-horizontal">
<fieldset>

<!-- Form Name -->
<legend>' . $language->get('forum', 'editReplyEditReply', null). '</legend>

<!-- Textarea -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="textarea">' . $language->get('forum', 'editReplyReply', null) . '</label>
  <div class="col-md-8">                     
    <textarea class="form-control" id="textarea" name="textarea">' . htmlentities($row['reply']) . '</textarea>
  </div>
</div>
';

if ($user->isAdmin){
    echo '
    <div class="form-group row">
        <label class="col-md-4 control-label" for="checkboxes"></label>
            <div class="col-md-4">
                <div class="checkbox">
                    <label for="checkboxes-0">
                        <input type="checkbox" name="visible" id="visible" value="1" ' . ( (int) $row['visible'] === 1 ? 'checked="checked"' : ''  ) . '"> ' . $language->get('forum', 'editReplyVisible', null). '
                    </label>
	            </div>
            </div>
        </div>';
}


echo    '
<!-- Button -->
<div class="form-group row">
  <label class="col-md-4 control-label" for="singlebutton">' . $language->get('forum', 'editReplyOperations', null) . '</label>
  <div class="col-md-4">
    <button onclick="saveReply();" id="singlebutton" name="singlebutton" class="btn btn-primary">' . $language->get('forum', 'editReplyUpdateButton', null) . '</button>
  </div>
</div>

</fieldset>
</div>
<div id="replyStatus"></div>
';

$this->addJsFile('//cdn.tinymce.com/4/tinymce.min.js',true);

$theScript = '

    function saveReply() {
        reply_ID = ' . $reply_ID . ';
        message = tinymce.activeEditor.getContent();
        ' . ($user->isAdmin ? ' $(\'#visible\').is(":checked") ? visible = 1 : visible = 0;  ' : '')  . '
        $.post( "' . $URI->getBaseUri() . $this->routed . '/reply-update-save/", { reply_ID: reply_ID, 
                                                                                   message: message
                                                                                   ' . ($user->isAdmin === true ? ', visible: visible' : '') . '
                                                                                   })
            .done(function( data ) {
            $("#replyStatus").html(data);
            
            return;
            if (data.startsWith("<!--error-->")){
                $("#replyStatus").html("Error");
            } else {
                $("#replyStatus").html("Post saved");
                $("#postButton").remove();
            }
        });
    }

    $( document ).ready(function() {
        tinymce.init({
      selector: \'textarea\',
      height: 320,
      theme: \'modern\',

      plugins: [
        \'advlist autolink lists link image charmap hr anchor pagebreak\',
        \'searchreplace wordcount visualchars code \',
        \'media nonbreaking table contextmenu directionality\',
        \'emoticons paste textcolor colorpicker textpattern imagetools codesample toc\'
      ],
      toolbar1: \'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image\',
      toolbar2: \'print preview media | forecolor backcolor emoticons | codesample help\',
      image_advtab: true,
      templates: [
        { title: \'Test template 1\', content: \'Test 1\' },
        { title: \'Test template 2\', content: \'Test 2\' }
      ],
      content_css: [
        \'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i\',
        \'//www.tinymce.com/css/codepen.min.css\'
      ]
     });
    });';
$this->addScript($theScript);