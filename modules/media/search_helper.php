<?php
if (!$core->loaded)
    die();

require_once 'lib/class_fabmedia.php';
$fabMedia = new CrisaSoft\FabCMS\FabMedia();

$result = $fabMedia->searchMedia($phrase);
$result = json_decode($result, true);

$fabMediaResult = '';

if ($result['status'] == 404) {
    $fabMediaResult = $language->get('search', 'searchImageSearchNoImage');
}elseif ($result['status'] == 500){
    $fabMediaResult = 'Query error';
} else {
    foreach($result['media'] as $media){
        switch ($media['type']){
            case 'image':

                if (empty($media['filename']) || empty($media['extension']))
                    continue;

                $fabMediaResult .= '
                    <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('fabmediamanager') .
                    '/showimage/' .
                    $media['ID'] . '-' . $media['trackback'] . '/">
                    
                    <img style="width:90px;" 
                         class="fabMediaThumbnail img-thumbnail" 
                         src="'. $fabMedia->getThumbnailPath($media['filename'], $media['extension'], $media['user_ID'] ) .'">
                    </a>
                    <br/>';
                break;
        }

    }

    $scriptFragment = '
    $(".fabMediaThumbnail").mouseover(function(){
        $(this).animate({
            width: 180,
            height: 180
        }, 30 );
    });

    $(".fabMediaThumbnail").mouseout(function(){
        $(this).animate({
            width: 90,
            height: 90
        }, 30 );
    });
';

    $module->addScript($scriptFragment);
}
