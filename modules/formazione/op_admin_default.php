<?php
if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

echo '<h1>Admin default</h1>';

$template->navBarAddItem('Formazione', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione'));
$template->navBarAddItem('Admin', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/');
$template->navBarAddItem('Homepage');

echo '<h2>Latest video</h2>';

$query = 'SELECT * 
          FROM ' . $db->prefix . 'formazione_media 
          ORDER BY ID DESC
          LIMIT 5';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->numRows){
    echo 'No media. <a href="' . $URI->getBaseUri() . $this->routed . '/admin-cp/viedo/editor/"></a>';
} else {
    echo '
<div class="table-responsive">
    <div class="float-right">
        <a href="' . $URI->getBaseUri() . $this->routed . '/admin-cp/video/editor/">New Video</a>
    </div>

    <table class="table-responsive table table-striped table-bordered">
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>ID</th>
                <th>YouTube ID</th>
                <th>Name</th>
                <th>Access level</th>
                <th>Type</th>
                <th>Subtype</th>
                <th>Filename</th>
                <th>Keywords</th>
                <th>Plugin</th>
            </tr>
        </thead>
        <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
            <td>
                <img class="img-fluid" 
                     src="https://img.youtube.com/vi/' . $row['youtube_ID'] .'/1.jpg" alt="Video thumbnail" />
            </td>
            <td>
                <a href="' . $URI->getBaseUri() . $this->routed . '/admin-cp/video/editor/?ID=' . $row['ID'] . '/">' . $row['ID'] . '</a>
            </td>
            <td>' . $row['youtube_ID'] . '</td>
            <td>' . $row['name'] . '</td>
            <td>' . $row['access_level'] . '</td>
            <td>' . $row['type'] . '</td>
            <td>' . $row['subtype'] . '</td>
            <td>' . $row['filename'] . '</td>
            <td>' . $row['keywords'] . '</td>
            <td>' . $row['plugin_full'] . '</td>
            
          </tr>';
    }

    echo '</tbody>
    </table>';

}

