<?php
if (!$core->loaded)
    die('direct call');

$this->noTemplateParse = true;

var_dump(($_POST));

$page_ID    = (int) $_POST['page_ID'];
$score      = (int) $_POST['score'];
$comment   = $core->in($_POST['comment']);

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