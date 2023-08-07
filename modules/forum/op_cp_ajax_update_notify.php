<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/11/2017
 * Time: 12:19
 */
if (!$core->loaded)
    die ("Direct call");

$this->noTemplateParse = true;

// Update the database
$query = 'SELECT ID 
          FROM ' . $db->prefix . 'forum_user_config 
          WHERE user_ID = ' . $user->ID . ' LIMIT 1';



if (!$result = $db->query($query)) {
    $relog->write(['type'      => '4',
                   'module'    => 'FORUM',
                   'operation' => 'forum_cp_select_photo_query_error',
                   'details'   => 'Unable to insert a new topic. ' . $query,
    ]);
    echo 'Query error while updating photo.';

    return;
}

if (!$db->affected_rows) {
    $query = 'INSERT INTO ' . $db->prefix . 'forum_user_config (user_ID, email_notify)
            VALUES
            (
                ' . $user->ID . ',
                ' . (int)$_POST['status'] . '
            );';

    

    if (!$result = $db->query($query)) {


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
                email_notify = ' . (int)$_POST['status'] . '
              WHERE ID = ' . $config_ID . ' 
                AND user_ID = ' . $user->ID . '
              LIMIT 1';
    
    if (!$db->query($query)) {
        echo 'Query error';

        return;
    }
}

echo '<div class="alert alert-success clearfix">
        <strong>Update ok!</strong> ' . $language->get('forum', 'cpNotifyUpdateO') . '.     
      </div>';