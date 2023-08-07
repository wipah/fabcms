<?php
if (!$isInstalling)
    die ("Not installing");

foreach ($languagesArray AS $language) {
    $query = 'INSERT INTO ' . $db->prefix . 'licenses_licenses 
              (
                master_ID, 
                lang, 
                name, 
                description
              ) 
              VALUES 
              (
                  1,
                  \'' . $language . '\',
                  \'General license\',
                  \'General license text.\'
              )';

    

    if (!$db->query($query)){
        echo 'Cannot insert. ' . $query;
        die();
    }
}
