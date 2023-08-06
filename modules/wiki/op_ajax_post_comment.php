<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/04/2017
 * Time: 16:34
 */

if (!$core->loaded)
    die('Direct call detected.');

$this->noTemplateParse = true;

$page_ID    = (int) $_POST['page_ID'];
$comment    = $core->in($_POST['comment'], true);
$authorType = (int) $_POST['authorSelect'];
$url = $_POST['url'];
$comment    = substr($comment, 0, 500);


if (strlen($url) > 0) {
    echo 'Comment is under review. Eternal review.';
    return;
}

if (strlen($comment) < 10) {
    echo $language->get('wiki', 'commentPostLenghtLessThanTenChars');
    return;
}

// Check if we have to use recaptcha
/*
if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') !== 1 ) {
    if (md5( $conf['security']['siteKey'] . $page_ID) !==  $_POST['securityHash'] ){
        echo 'Security hack.';
        return;
    }

    if (($conf['security']['siteKey'] . date('Y-m-d-H')) !== $_POST['SH1']) {
        echo 'Security hack. Err. 2';
        return;
    }
} else {
    if (!$core->reCaptchaValidateCode('curl', 'grecaptcharesponse')) {
        echo '<!--error-->' . $language->get('wiki', 'pageShowCommentCommentRecaptchaFailed', null);
        return;
    }
}
*/

if ( $authorType > 0) {
    $author_ID = $user->ID;
} elseif ( $authorType === -1) {
    $author = $core->in($_POST['author'], true);
}

$query = '
INSERT INTO ' . $db->prefix . 'wiki_comments
(
    page_ID, 
    author, 
    author_ID, 
    comment, 
    `date`, 
    IP, 
    visible 
)
VALUES
(
    ' . $page_ID . ',
    \'' . $author . '\',
    \'' . $author_ID . '\',
    \'' . $comment . '\',
    \'' . date('Y-m-d H:i:s') . '\',
    \'' . $_SERVER['REMOTE_ADDR'] . '\',
    0
)';

$db->setQuery($query);

if (!$db->executeQuery('insert')){
    echo '<!--error-->' . 'Query error.';
} else {
    echo '<!--ok-->' .  $language->get('wiki', 'commentUnderModerationQueue');
}