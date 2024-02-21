<?php
if (!$core->loaded)
    die('direct call');

$this->noTemplateParse = true;

$page_ID    = (int) $_POST['page_ID'];
$score      = (int) $_POST['score'];
$comment    = $core->in($_POST['comment']);
$captchaCode    = $_POST['captcha'];

if (!$captcha->checkCaptcha($captchaCode)) {
    echo 'NoCPT.';
    return;
}

$query = 'INSERT INTO ' . $db->prefix . 'wiki_feedback 
          (  page_ID
           , score
           , feedback
           , date
          ) 
          VALUES (
            ' . $page_ID   .  '
           , ' . $score    . ' 
           , \'' . $comment . '\'
           ,  NOW() 
          );';

$db->query($query);

return json_encode(['status' => '1']);