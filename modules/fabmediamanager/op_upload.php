<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/09/2015
 * Time: 12:57
 */


if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

if (!$core->loaded)
    die("No direct access.");
$this->noTemplateParse = TRUE;

$fabMedia->module = $core->in($path[3], true);
$fabMedia->upload();