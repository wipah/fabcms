<?php

if (!$core->loaded)
    die("Direct call detected");

$trackback = explode('-', $path[3]);

if (!is_numeric($trackback[0]) || empty($trackback[1])) {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>Media not found</h1>
    <!--FabCMS-hook:media-showMedia404NotFound-->';
    return;
}

// Get all the media info
$ID = (int) $trackback[0];
$query = 'SELECT * 
          FROM ' . $db->prefix . 'fabmedia 
          WHERE ID = ' . $ID . ' 
          AND trackback = ' . $trackback . '
          LIMIT 1';



if (!$result = $db->query($query)) {
    echo 'Query error';

    $relog->write(['type'      => '4',
        'module'    => 'media',
        'operation' => 'media_showmedia_query_error',
        'details'   => 'Query error: ' . $query,
    ]);

    if ($user->isAdmin)
        echo $query;

    return;
}

if (!$db->affected_rows) {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>Media not found</h1>
    <!--FabCMS-hook:media-showMedia404NotFound-->';
    return;
}

$row = mysqli_fetch_assoc($result);
