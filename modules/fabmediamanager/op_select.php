<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/09/2015
 * Time: 12:59
 */


if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

$this->noTemplateParse = true;

echo '
<div id="html5_uploader">Your browser doesn\'t support native upload.</div>
';

return;
echo '
<h2>TEST</h2>
<form id="uploader" action="' . $URI->getBaseUri() . 'fabmediamanager/upload/" method="post" enctype=\'multipart/form-data\'>
<input id="fabmediamanager" name="fabmediamanager[]" type="file" multiple="multiple">
<div id="dropZone"></div>
<input type="submit" value="carica">
</form>';