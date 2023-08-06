<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/03/2017
 * Time: 11:56
 */
if (!$core->adminBootCheck())
    die("Check not passed");

if (!isset($_GET['ID'])){
    echo 'ID not passed';
    return;
}

$template->navBarAddItem('Wiki','admin.php?module=wiki');
$template->navBarAddItem('Tag editor', 'admin.php?module=wiki&op=tag');

$ID     = (int) $_GET['ID'];
$query  = 'DELETE 
           FROM ' . $db->prefix . 'wiki_tags_menu 
           WHERE ID = ' . $ID . ' 
           LIMIT 1';

if (!$db->query($query)) {
    echo 'Query error. ' . $query;
} else {
    echo 'Menù deleted';
}