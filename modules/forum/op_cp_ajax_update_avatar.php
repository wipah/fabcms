<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/11/2017
 * Time: 12:18
 */

if (!$core->loaded)
    die ("Direct call");

$this->noTemplateParse = true;

$uploadDir = $conf['path']['baseDir'] . 'modules/forum/assets/custom_avatars/';
$filename = basename($_FILES['file']['name']);

$filename = str_replace('..', '', $filename);
$filename = str_replace('.html', '', $filename);
$filename = str_replace('.htm', '', $filename);
$filename = str_replace('.js', '', $filename);
$filename = str_replace('.php', '', $filename);

$parts = pathinfo($_FILES['file']['name']);

if (strtolower($parts['extension']) !== 'jpeg' && strtolower($parts['extension']) !== 'jpg' && strtolower($parts['extension']) !== 'png') {
    echo 'Estensione non autorizzata';

    return;
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 077, true);
}

unlink(md5($user->ID . $conf['security']['siteKey']) . '.png');
unlink(md5($user->ID . $conf['security']['siteKey']) . '.jpg');
unlink(md5($user->ID . $conf['security']['siteKey']) . '.jpeg');

$fileEnc = md5($user->ID . $conf['security']['siteKey']) . '.' . strtolower($parts['extension']);
$uploadfile = $uploadDir . '/' . $fileEnc;

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {

    // Update the database
    $query = 'SELECT ID 
                  FROM ' . $db->prefix . 'forum_user_config 
                  WHERE user_ID = ' . $user->ID . ' LIMIT 1';

    $db->setQuery($query);

    if (!$result = $db->executeQuery()) {
        echo 'Query error while updating photo.';

        return;
    }

    if (!$db->affected_rows) {
        $query = 'INSERT INTO ' . $db->prefix . 'forum_user_config (user_ID, user_avatar, user_avatar_type)
            VALUES
            (
            ' . $user->ID . ',
            \'' . $fileEnc . '\',
            1
            )
            ';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('INSERT')) {
            echo 'Query error with new.';

            return;
        }

        if (!$db->affected_rows) {
            echo 'Internal error. Code: for-23';

            return;
        }

    } else {

        $row = mysqli_fetch_assoc($result);
        $config_ID = $row['ID'];

        $query = 'UPDATE ' . $db->prefix . 'forum_user_config 
                  SET
                    user_avatar_type = 1,
                    user_avatar = \'' . $fileEnc . '\'
                  WHERE ID = ' . $config_ID . ' 
                    AND user_ID = ' . $user->ID . '
                  LIMIT 1';
        $db->setQuery($query);
        if (!$db->executeQuery('update')) {
            echo 'Query error';

            return;
        }
    }

    echo '
        <div class="alert alert-success clearfix">
            <strong>Upload ok!</strong> ' . $language->get('forum', 'cpUploadNewAvatarOk') . '.     
            <img class="img-fluid float-right" style="width:50px; height:50px;" src="' . $URI->getBaseUri(true) . '/modules/forum/assets/custom_avatars/' . $fileEnc . '" alt="profilephoto">
        </div>';
} else {
    echo "Possible file upload attack!\n";
}
