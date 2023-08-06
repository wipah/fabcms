<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 08/12/14
 * Time: 9.53
 */


if (!$core->loaded)
    die();

$thePage = __DIR__ . '/singlepage.html';
$theData = __DIR__ . '/data.php';

$core->jsVar['fabcms_isFullPage'] = 1;

if(!file_exists( __DIR__ . '/singlepage.html')){
    echo $language->get('singlepage','noPageSet');
    return;
} else {
    $pageData = file_get_contents($thePage);
}

if (file_exists($theData))
    include $theData;

// Hooks (https://bitbucket.org/thewiper/fabcms/issue/8/sidebar-interactions && https://bitbucket.org/thewiper/fabcms/wiki/Hooks)
$theRegex = '#\[!hook=([a-z0-9\-\_]+)!\](.*?)\[!endhook!\]#ism';
$pageData = preg_replace_callback($theRegex,
    function ($theFirstMatch) {
        global $template;
        for ($i = 0; $i < count($theFirstMatch[0]); $i++) {
            $template->hooks[] = $theFirstMatch[1];
            $template->hooksData[] = $theFirstMatch[2];
        }
    }, $pageData);

echo $pageData;

$this->addTitleTag ($conf['singlepage']['title']);
$this->addMetaData('description', $conf['singlepage']['description']);
