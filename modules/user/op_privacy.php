<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/05/2018
 * Time: 16:10
 */

if (!$core->loaded)
    die ("Direct call");

$fileName = __DIR__ . '/static/privacy.html';

echo '<h2>Privacy</h2>';

$this->addTitleTag(  sprintf($language->get('user', 'privacyStatement'), $conf['site']['name']) );

if (!file_exists($fileName)) {
    echo $language->get('user', 'privacyPrivacyNotFound', null);

    return;
}

echo file_get_contents($fileName);
