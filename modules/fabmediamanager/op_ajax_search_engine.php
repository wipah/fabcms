<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/11/2018
 * Time: 16:02
 */

if (!$core->loaded)
    die("No way here");

$q      =   $core->in($_POST['q']);
$type   =   $core->in($post['type'], 1);

switch ($_POST['orderBy'])
{
    case '1':
        $orderBy = ' MASTER.upload_date ASC';
        break;
    case 2:
        $orderBy = ' MASTER.upload_date DESC';
        break;
    case 3:
        $orderBy = ' MEDIA.title ASC ';
        break;
    case 4:
        $orderBy = ' MEDIA.title DESC ';
        break;
}

$query = '
SELECT 
	MASTER.ID master_ID,
	MASTER.user_ID user_ID,
	MEDIA.`type`,
	MEDIA.extension,
	MEDIA.filename,
	MEDIA.size,
	MEDIA.ID media_ID,
	MEDIA.title,
	MEDIA.trackback
FROM ' . $db->prefix . 'fabmedia_masters MASTER
LEFT JOIN ' . $db->prefix . 'fabmedia MEDIA
	ON MEDIA.master_ID = MASTER.ID
WHERE MEDIA.enabled = 1 
    AND MEDIA.global_available = 1
    AND 
    (
                MEDIA.title            LIKE    \'%' . $q . '%\'
                OR MEDIA.description   LIKE    \'%' . $q . '%\'
    
    )
    ORDER BY '. $orderBy.'
';



if (!$result = $db->query($query)){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo '<div class="alert alert-warning" role="alert">
            ' . $language->get('fabmediamanager', 'searchNoImageFound', null) . '
          </div>';
    return;
}

$i = 0;

echo '<div class="container">
    <div class="row"><!--start row-->';

while ($row = mysqli_fetch_assoc($result)) {

    $imagePath = $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'];
    $pos = strrpos($imagePath, '.' . $row['extension']);

    $src = substr_replace($imagePath, '_lq.' .  $row['extension'], $pos, strlen('.' .  $row['extension']));

    $i++;
    echo '
        <div class="col-md-3">
            <a href="' . $URI->getBaseUri() . 'fabmediamanager/showimage/' . $row['media_ID'] . '-' . $row['filename'] . '/">
                <img src="' . $src .'" class="img-fluid" alt="" />' . $row['title'] .'
            </a>
        </div>';

    if ($i === 4) {
        $i = 0;
        echo '</div><!-- closing middle row-->
              <div class="row"><!--starting middle row-->';
    }

}

echo '</div><!--closing start row-->
</div> <!-- closing container-->';