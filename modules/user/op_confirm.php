<?php
if (!$core->loaded) {
  die('Direct call detected');
}

if (!isset($path[3])) {
  echo '<div class="ui-corner-all ui-state-error">' . $language->get('user', 'confirmNoIDPassed') . '</div>';
  return;
}
$ID = (int) $path[3];

if (!isset($path[4])) {
  echo '<div class="ui-corner-all ui-state-error">' . $language->get('user', 'confirmNoHashPassed') . '</div>';
  return;
}
$hash = $core->in($path[4]);

$query = 'UPDATE ' . $db->prefix . 'users ' .
  ' SET enabled = \'1\', ' .
  ' optin_IP_confirm = \'' . $_SERVER['REMOTE_ADDR'] . '\' ' .
  ' WHERE ID = \'' . $ID . '\' ' .
  ' AND optin_hash = \'' . $hash . '\' ' .
  ' LIMIT 1';


if (!$db->query($query)) {
    $relog->write(['type'      => '4',
                   'module'    => 'USER',
                   'operation' => 'user_confirm_update_db',
                   'details'   => 'Unable to switch user from unconfirmed to confirmed. ' . $query,
    ]);

    echo '<div style="padding:4px;" class="ui-corner-all">' . $language->get('user', 'confirmQueryError') . '</div>';
    return;
}

if (!$db->affected_rows) {
  echo $language->get('user', 'confirmNoMatch');
} else {
    // Get the email
    $query = 'SELECT * 
              FROM ' . $db->prefix . 'users 
              WHERE ID = ' . $ID . ' LIMIT 1';
    
    $result = $db->query($query);
    $row = mysqli_fetch_array($result);

    /*************
     * Connector *
     ************/
    $connector->callHandler('user_confirmed', array('email' => $row['email']));

    echo '<!--FabCMS-hook:beforeConfirmOptin-->' . $language->get('user', 'confirmConfirmOk') . '<!--FabCMS-hook:afterConfirmOptin-->';
}