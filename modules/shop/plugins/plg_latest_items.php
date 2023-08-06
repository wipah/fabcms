<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 24/11/2017
 * Time: 17:00
 */

function plugin_latest_items($dataArray)
{
    global $db;
    global $core;
    global $user;
    global $URI;
    global $conf;
    global $module;

    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    return 'A - b - c';
}