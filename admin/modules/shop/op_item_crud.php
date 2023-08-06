<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/09/2018
 * Time: 08:55
 */

if (!$core->adminLoaded)
    die ("Direct call detected");

if (!$user->isAdmin)
    die ("Not an admin");

