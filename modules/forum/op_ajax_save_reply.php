<?php
if (!$core->loaded)
    die ("Direct call");

$this->noTemplateParse = true;

if (!$user->logged) {
    echo '<!--error-->User not logged';
    return;
}

if (!isset($_POST['topic_ID'])){
    echo '<!--error-->Topic ID not passed';
    return;
}
$topic_ID = (int) $_POST['topic_ID'];

// Check if the topic is locked and the user is not an administrator
if ( true === $fabForum->isTopicLocked($topic_ID) && $user->isAdmin !== true ){
    echo '<!--error-->Topic was locked.';
    return;
}

if (!isset($_POST['message'])) {
    echo '<!--error-->Message not passed';
    return;
}

if ($fabForum->userBanned){
    echo '<!--error-->User is banned';
    return;
}

// Check if we should use recaptcha
if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1 && $fabForum->getReplyTopicCountByUser($user->ID) < 5){

    if (!$core->reCaptchaValidateCode('curl', 'grecaptcharesponse')){
        echo '<!--error-->' . $language->get('forum', 'topicCrudRecaptchaIsNotValid', null);
        return;
    }
}

$message    = $_POST['message'];
$message    = $fabForum->cleanHtmlCode($message);
$result     = $fabForum->postReply($topic_ID, $message);

if ($result['status'] === -1){
    echo '<!--error--><div class="alert alert-danger">
            <strong>Error!</strong> Something gone wrong while posting.
          </div>';
    return;
}

echo '<div class="alert alert-success">
  <strong>' . $language->get('forum', 'topicSaveReplyOkShort') . '</strong> ' . $language->get('forum', 'topicSaveReplyOk', null) . '
</div>';

echo '
<script>
window.setTimeout(function(){
    window.location.href = "' . $result['page'] .'";
}, 3000);
</script>';