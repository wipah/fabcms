<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/05/2018
 * Time: 16:10
 */

if (!$core->loaded)
    die ("Direct call");

$fileName = __DIR__ . '/static/rules.html';

echo '<h2>Privacy</h2>';

if (!file_exists($fileName)) {
    echo $language->get('user', 'rulesRulesNotFound', null);

    return;
}
$this->addTitleTag( $language->get('user', 'showRules'));

echo file_get_contents($fileName);
