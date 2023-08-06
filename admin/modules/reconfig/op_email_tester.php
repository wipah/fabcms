<?php

if (!$user->isAdmin)
    die ("Only admins");

if (isset($_GET['send'])){
    if (!isset($_POST['dummy']))
        die ("Reload");

    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $text = $_POST['text'];

    $fabmail->addTo($to);
    $fabmail->addSubject($subject);
    $fabmail->addMessage($text);

    if ($fabmail->sendEmail()) {
        echo 'Mail sent.';
    } else {
        echo $fabmail->lastError;
    }
}

echo '
<form method="post" action="admin.php?module=reconfig&op=emailTester&send">
    <input type="hidden" name="dummy" />

    To: <input name="to" type="email">
    Subject: <input name="subject" type="text">
    <br/>
    Text<br/><textarea name="text"></textarea>
    <button type="submit">Send</button>
</form>';