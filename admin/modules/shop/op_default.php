<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/08/2018
 * Time: 08:52
 */

if (!$core->adminLoaded)
    die ("Direct call detected");

if (!$user->isAdmin)
    die ("Not an admin");

echo '<a href="admin.php?module=shop&op=config">Config</a>';
