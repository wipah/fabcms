<?php
$this->noTemplateParse = false;

if (!$core->loaded)
    die();

// Check if a gallery has been passed
if (preg_match('/([0-9]{1,5)\-([a-z\ \-\,\.])/i', $path[3], $matches)) {
    $gallery_ID = $matches[1];
    $gallery_trackback = $core->in($matches[2], true);

    require_once 'op_gallery_show_images.php';
    return;
}
echo '<h1>Fabmedia</h1>';

$this->addTitleTag($core->getConfig('media', 'mediaDefaultPageTitle'));
$template->navBarAddItem('MediaManager',$URI->getBaseUri() . 'media/' );
$this->addMetaData('description', $core->getConfig('media', 'mediaSeoDescription', 'extended_value'));

echo ($core->getDbConfig('media', 'mainPage', $core->shortCodeLang, 'extended_value'));