<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 16/12/2016
 * Time: 11:53
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$ID = (int) $_POST['ID'];

$query = 'DELETE FROM ' . $db->prefix . 'wiki_comments WHERE ID = ' . $ID . ' LIMIT 1';

if (!$db->query($query)){
    echo 'Failed. ' . $query;
} else {
    echo 'OK';
}