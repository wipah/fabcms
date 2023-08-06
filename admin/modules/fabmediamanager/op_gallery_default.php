<?php

if (!$core->adminBootCheck())
    die("Check not passed");

$query = 'SELECT 
	MASTER.ID AS MASTER_ID,
	MEDIA.filename,
	MEDIA.extension,
    MEDIA.user_ID,
    GALLERY.title,
    GALLERY.ID,
	(
	SELECT COUNT(ID) AS total_items
	FROM ' . $db->prefix . 'fabmedia_galleries_items ITEM
	WHERE ITEM.gallery_ID = GALLERY.ID
	) total_items
	
FROM ' . $db->prefix . 'fabmedia_galleries_masters MASTER
LEFT JOIN ' . $db->prefix . 'fabmedia_galleries_galleries GALLERY
	ON GALLERY.master_ID = MASTER.ID
LEFT JOIN ' . $db->prefix . 'fabmedia AS MEDIA
	ON GALLERY.cover_ID = MEDIA.ID
ORDER BY GALLERY.`order` ASC';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}
echo '<h2>Galleries created</h2>';

if (!$db->numRows){
    echo 'No galleries were created.';
} else {
    $i = 0;
    echo '<div class="row">';

    while ($row = mysqli_fetch_assoc($result)) {
        $pos = strrpos($row['filename'], '.' . $row['extension']);
        $imageThumbPath     = substr_replace($row['filename'], '_thumb.' . $extension, $pos, strlen('.' . $extension));
        echo '<div class="col-md-4">
                <h3>' . $row['title']  .'</h3>
                    <a href="admin.php?module=fabmediamanager&op=Gallery&command=galleryEditor&ID=' . $row['ID'] . '">
                        <img src="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageThumbPath . '">
                        <br/>' .  $row['total_items'] .'
                    </a>
              </div>';

        if ($i === 3) {
            echo '</div>
            <div class="row">';
        }
    }

    echo '</div>';
}