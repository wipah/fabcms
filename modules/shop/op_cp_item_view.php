<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 08/04/2019
 * Time: 15:30
 */

if (!$user->isAdmin)
    die("Only admin");

$query = '';