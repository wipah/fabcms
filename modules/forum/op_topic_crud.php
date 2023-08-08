<?php

if (!$core->loaded)
    die();

if (!$user->logged){
    if (in_array('forumTopicNewTopicUserNotLogged', $template->hooks)) {
        echo '<!--FabCMS-hook:forumTopicNewTopicUserNotLogged-->';
    } else {
        echo '<div style="margin-top: 24px;" class="well">
             ' . sprintf($language->get('forum', 'topicCannotPostNewTopicUserNotLogged', null),
                        $URI->getBaseUri()  . 'user/register/',
                           $URI->getBaseUri() . 'user/' ) . '
             </div>';
    }
    return;
}

if ($fabForum->userBanned){
    echo '<!--error-->User is banned';
    return;
}

// Check if we should use recaptcha
if (  (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1 && $fabForum->getReplyTopicCountByUser($user->ID) < 5 ){
    $this->addJsFile('https://www.google.com/recaptcha/api.js', true);
    $useRecaptacha = true;
}

$this->addJsFile('//cdn.tinymce.com/4/tinymce.min.js',true);
$this->addJsFile($URI->getBaseUri(true) . 'modules/fabmediamanager/js/plupload/plupload.full.min.js', false);
$this->addJsFile($URI->getBaseUri(true) . 'modules/fabmediamanager/js/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js', false);


$template->sidebar.= $template->simpleBlock($language->get('forum', 'topicCrudLateralSearch', null),'
                                              <form action="' . $URI->getBaseUri() . $this->routed . '/search/" 
                                                   method="post">
                                                   
                                                   <input name="forumSearch" type="text" class="">
                                                    <button class="button" type="submit">' . $language->get('forum', 'topicCrudSearchSidebar', null) . '</button>      
                                              </form>');
if ($path[2] == 'new-topic') {

    if (!isset($_GET['thread_ID'])){
        echo 'Thread ID wasn\'t passed.';
        return;
    }

    $thread_ID = (int) $_GET['thread_ID'];

    $query = 'SELECT * 
          FROM ' . $db->prefix . 'forum_threads
          WHERE visible = 1
            AND ID = ' . $thread_ID . '
          LIMIT 1;';

    
    if (!$result = $db->query($query)) {
        $relog->write(['type'      => '4',
                       'module'    => 'FORUM',
                       'operation' => 'forum_topic_crud_select_query_error',
                       'details'   => 'Unable to select threads. ' . $query,
        ]);

        echo 'Query error while selection thread.';
        return;
    }

    if (!$db->affected_rows){
        $relog->write(['type'      => '3',
                       'module'    => 'FORUM',
                       'operation' => 'forum_categories_search_thread_not_exists',
                       'details'   => 'The thread doesn\'t exists. ' . $query,
        ]);

        echo 'This thread doesn\'t exist.';
        return;
    }
    $lineThread = mysqli_fetch_assoc($result);

    $action = $URI->getBaseUri() . $this->routed . '/new-topic/?thread_ID=' . $thread_ID . '&post';
    $buttonText = $language->get('forum', 'topicCrudSaveNewPost', null);

    // Should we save?
    if ( isset ($_GET['post']) ) {
        $this->noTemplateParse = true;

        $title = $core->in($_POST['title'], true);
        $tags = $core->in($_POST['tags'], true);

        if ($useRecaptacha){

          if($core->reCaptchaValidateCode('curl','grecaptcharesponse')){
             echo $language->get('forum', 'topicCrudRecaptchaIsNotValid', null);
             return;
            }

        }

        $message = $fabForum->cleanHtmlCode($_POST['message']);

        if (strlen($title) < 3) {
            echo $language->get('forum', 'topicCrudTitleIsTooShort', null);
            return;
        }

        if (strlen($message) < 10) {
            echo $language->get('forum', 'topicCrudTextIsTooShort', null);
            return;
        }

        $trackback = $core->getTrackback($title);

        $query = '
              INSERT INTO ' . $db->prefix . 'forum_topics 
                (thread_ID, 
                reply_count, 
                topic_trackback, 
                user_ID, 
                topic_title, 
                tags, 
                date_created,
                date_latest_update, 
                topic_message, 
                visible, 
                ' . ($user->isAdmin === true ? 'pinned, ' : '') . ' 
                IP)
              VALUES (
              ' . $thread_ID . ',
              0,
              \'' . $trackback . '\',
              \'' . $user->ID . '\',
              \'' . $title . '\',
              \'' . $tags . '\',
              NOW(),
              NOW(),
              \'' . $message . '\',
              1,
              ' . ($user->isAdmin === true ? (int)$_POST['pinned'] . ',' : '') . '
              \'' . $_SERVER['HTTP_HOST'] . '\'
              )';

        

        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_topic_crud_insert_topic_query_error',
                           'details'   => 'Unable to insert a new topic. ' . $query,
            ]);


            echo 'Query error';
        } else {
            $topic_ID = $db->insert_id;
            echo '<!--success-->';

            $link = $URI->getBaseUri() . $this->routed . '/' .
                    $lineThread['ID'] . '-' . $lineThread['thread_trackback'] . ' /' .
                    $topic_ID . '-' . $trackback . '/';

            $message = sprintf($language->get('forum', 'topicCrudTopicPosted', null), $link);

            echo sprintf('
            <div class="alert alert-success">
                %s
            </div>', $message);

            $fabForum->updatePostCount($user->ID);
            $fabForum->updateTopicCount($thread_ID);
            $fabForum->updateLastTopic($thread_ID);
            $fabForum->setTopicSubscription($topic_ID, $user->ID, false);
        }

        return;
    }


} elseif ($path[2] == 'edit-topic') {

    if (!isset($_GET['topic_ID']))
    {
        echo 'No topic ID was passed';
        return;
    }

    $topic_ID = (int) $_GET['topic_ID'];

    // Should we save?
    if (isset($_GET['save']))
    {
        $this->noTemplateParse = true;

        $title = $core->in($_POST['title'], true);
        $tags = $core->in($_POST['tags'], true);

        $message = $fabForum->cleanHtmlCode($_POST['message']);

        $trackback = $core->in($title);

        $query = '
        UPDATE ' . $db->prefix . 'forum_topics
        SET topic_title = \'' . $title. '\',
            date_latest_update  = NOW(),
            tags                = \'' . $tags. '\',
            topic_message       = \'' . $message . '\'
        ' . ($user->isAdmin === true ? ',pinned = ' . (int) $_POST['pinned']  : '') . '
        WHERE ID = ' . $topic_ID . '
        LIMIT 1;
        ';

        
        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FORUM',
                           'operation' => 'forum_topic_crud_update_topic_query_error',
                           'details'   => 'Unable to update the topic. ' . $query,
            ]);

            echo 'Query error.';
            return;
        } else {
            echo 'Updated';
            return;
        }
    }

    $action = $URI->getBaseUri() . $this->routed . '/edit-topic/?topic_ID=' . $topic_ID . '&save';
    $buttonText = $language->get('forum', 'topicCrudUpdateTopic', null);

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'forum_topics 
              WHERE ID = ' . $topic_ID . ' AND (locked != 1 OR locked IS NULL)
              ' . ( $user->isAdmin !== true ? ' AND user_ID = ' . $user->ID  : '') . '
              ;';

    

    if (!$resultTopic = $db->query($query)){

        $relog->write(['type'      => '4',
                       'module'    => 'FORUM',
                       'operation' => 'forum_topic_select_query_error',
                       'details'   => 'Unable select the topic. ' . $query,
        ]);

        echo 'Query error.';
        return;
    }

    if (!$db->affected_rows) {
        $relog->write(['type'      => '3',
                       'module'    => 'FORUM',
                       'operation' => 'forum_topic_select_no_topic',
                       'details'   => 'Topic was not found. ' . $query,
        ]);
        echo 'No topic.';
        return;
    }

    $rowQuery = mysqli_fetch_assoc($resultTopic);
}


echo '<link rel="stylesheet" type="text/css" href="' . $URI->getBaseUri(true) . 'modules/fabmediamanager/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css">';

if ($user->isAdmin)
    $pinned = '<input type="checkbox" id="pinned" value="1" ' . ( (int) $rowQuery['pinned'] === 1 ? ' checked ' : '' ) . '> Pinned';

$template->navBarAddItem('Forum',$URI->getBaseUri() . $this->routed . '/');

$topicContent = '
     <div class="form-horizontal">
        <fieldset>
        
        <!-- Form Name -->
        <legend>' . $language->get('forum', 'topicCrudPostingOn', null) . $lineThread['thread_name'] . '</legend>
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-4 control-label" for="title">' . $language->get('forum', 'topicCrudTitle', null) . ' </label>  
          <div class="col-md-8">
            <input value="' . $rowQuery['topic_title'] . '" id="title" name="title" required type="text" placeholder="Discussione" class="form-control input-md">
            <span class="help-block">' . $language->get('forum', 'topicCrudTitleHelp', null) . '</span>  
          </div>
        </div>
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-4 control-label" for="tags">' . $language->get('forum', 'topicCrudTags', null) . '</label>  
          <div class="col-md-8">
          <input value="' . $rowQuery['tags'] . '" id="tags" name="tags" type="text" placeholder="Tag1, Tag2, Tag3" class="form-control input-md">
          <span class="help-block">' . $language->get('forum', 'topicCrudTagsHelp', null) . '</span>  
          </div>
        </div>
        
        <!-- Textarea -->
        <div class="form-group row">
          <label class="col-md-4 control-label" for="message">' . $language->get('forum', 'topicCrudMessage', null) . '</label>
          <div class="col-md-8">                     
            <textarea class="form-control" id="message" name="message">' . $rowQuery['topic_message'] . '</textarea>
          </div>
        </div>

';

if ( $useRecaptacha === true) {
    $topicContent .= '
    <div class="form-group row">
        <label class="control-label col-md-4" for="g-recaptcha">' . $language->get('forum', 'topicRecaptchaCode') . '</label>
        <div class="col-md-8">
' . $core->reCaptchaGetCode() . '
        </div>
    </div>';
}


$topicContent .= '
        <div class="form-group row">
          <label class="col-md-4 control-label" for="singlebutton">' . $language->get('forum', 'topicCrudOperations', null) . '</label>
          <div class="col-md-8">
            <button id="singlebutton" name="singlebutton" onclick="post();" class="btn btn-primary">' . $buttonText . '</button>
' . $pinned . '  
          </div>
        </div>
        </fieldset>
        </div>';


$mediamanagerContent = '<div class="row">
            <div class="col-md-12">
                <div id="FabMedia">
                    <p>
                        <div id="FabMediaArea">MEDIAMANAGER</div>
                    </p>
                </div>
            </div>';

echo $template->getTabs('forum', ['Topic', 'Mediamanager'], [$topicContent, $mediamanagerContent],  [] );
echo '<div id="resultPost"></div>';


$theScript = /** @lang ECMAScript 6 */
    '
$(function(){
    FabMedia();
})

function FabMedia() {
    $.ajax({
        type: "POST",
        url: "' . $URI->getBaseUri() . 'fabmediamanager/init/forum-topic/",
        data: "customButton=name||action||title||icon",
        success: function(msg){
        $("#FabMedia").html(msg);
    }
    });
}

function fabMediaInit(){
    $(function() {
        // Setup html5 version
        $("#html5_uploader").pluploadQueue({
			// General settings
			runtimes : \'html5\',
			url : "' . $URI->getBaseUri() . 'fabmediamanager/upload/forum-topic/",
			// Removed chunk_size : \'10mb\',
			unique_names : true,
			rename: true,
			multiple_queues: true,
			filters : {
			max_file_size : \'50mb\',
				mime_types: [
                ' . $customFiles . ' 
				]
			},
		});
	});
}';

$this->addScript($theScript);

$script = '

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
});

function post() {
    title   = $("#title").val();
    tags    = $("#tags").val();
    message = tinymce.activeEditor.getContent();
    ' .  ($useRecaptacha === true  ? 'recaptchaCode = grecaptcha.getResponse();' : '').'
    $("#pinned").is(\':checked\') === true ? pinned = 1 : pinned = 0;
    if (title.length === 0){
        alert ("No title");
        return;
    }
    
    $.post( "' . $action . '", {  title                 :   title, 
                                  tags                  :   tags,
                                  message               :   message, 
 ' . ($useRecaptacha === true ? '  grecaptcharesponse  :   recaptchaCode, ' : '') . '                                 
                                  pinned                :   pinned 
                                  
                                  })
        .done(function( data ) {
 
            if (data.startsWith("<!--success-->")) {
                $("#singlebutton").hide();
            }
            
            $("#resultPost").html(data);
    });
}';

$this->addScript($script);