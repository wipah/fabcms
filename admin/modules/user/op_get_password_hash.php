<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 19/04/2015
 * Time: 09:05
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBar[] = '<a href="admin.php?module=user">User</a>';
$template->navBar[] = '<em>Password hash tool</em>';
if (isset($_GET['send'])) {
    echo 'The hash is:' . $user->getPasswordHash($_POST['password']);
}

echo '
<form method="post" action="admin.php?module=user&op=getPasswordHash&send">
    Password: <input type="text" name="password" value="' . htmlentities($_POST['password']) . '">
    <button type="submit">Get hash</button>
</form>';