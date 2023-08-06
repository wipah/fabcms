<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/12/2016
 * Time: 09:57
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$language = $core->in($_POST['language'], true);
$title = $core->in($_POST['title']);

$trackback = $URI->getBaseUri(false, $language) . $core->router->getRewriteAlias('wiki') . '/' . $core->getTrackback($title) . '/';

echo $trackback;