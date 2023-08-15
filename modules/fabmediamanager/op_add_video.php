<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/10/2017
 * Time: 12:38
 */

$this->noTemplateParse = true;

$provider       = (int) $_POST['provider'];
$provider_ID    = $core->in($_POST['provider_ID'], true);
$title          = $core->in($_POST['title']);

$fabMedia->addExternalVideo($provider, $provider_ID, $title);