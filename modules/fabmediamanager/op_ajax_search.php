<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/02/2016
 * Time: 12:51
 */

if (!$core->loaded)
    die();

$keyword = $_POST['keywords'];
$result = ($fabMedia->searchMedia($keyword));

$result = json_decode($result, true);

if ($result['status'] == 404){
    echo  'No result';
}elseif ($result['status'] == 500){
    echo 'Query error';
} else {
    foreach($result['media'] as $media){
        switch ($media['type']){
            case 'image':
                echo '<a class="" href="' . $URI->getBaseUri() .
                    $core->router->getRewriteAlias('fabmediamanager') .
                    '/showimage/' .
                    $media['ID'] . '-' . $media['trackback'] . '">
                    <img style="width:90px;" class="fabMediaThumbnail img-thumbnail" src="'  .
                    $fabMedia->getThumbnailPath($media['filename'], $media['extension'], $media['user_ID'] ) .'">
                </a>';
                break;
        }

    }
}