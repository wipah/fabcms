<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 04/11/2016
 * Time: 15:09
 */

function plugin_wordpress_load($dataArray) {
    global $core;
    global $db;
    global $contents;
    global $user;
    global $URI;

    // Load configuration file
    if (!file_exists(__DIR__ . '/wordpress_config.php'))
        return 'wordpress_config.php not exists!';

    include __DIR__ . '/wordpress_config.php';

    require_once ($conf['wordpress']['wpload_path']);

    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    }
    else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    $filter = array();

    if (isset($dataArray['numberposts'])){
        $filter['numberposts'] = (int) $dataArray['numberposts'];
    } else {
        $filter['numberposts'] = 2;
    }

    $filter['post_status'] = 'publish';

    if (isset($dataArray['tags'])){
        $tags = $core->in($dataArray['tags']);
        $filter_tags = explode('|', $tags);
        foreach ($filter_tags as $single_tag){
            $filter['tag'][] = $single_tag;
        }
    }

    if ( (int) $dataArray['showImage'] == 1) {
        $showImage = true;
    }else{
        $showImage = false;
    }

    $recent_posts = wp_get_recent_posts($filter);

    $theBuffer = '';
    foreach($recent_posts as $post) {
        if ($showImage === true){
            $image =  '<img style="float:left;margin-right: 4px; width:75px; height: 75px;" class="img-fluid" >';

        }
        $theBuffer .= '

                    <div class="media">
                        <a class="float-left" href="'. get_permalink($post['ID']) . '">
                        <img class="media-object" src="' . str_replace ('.jpg', '-150x150.jpg',  wp_get_attachment_url( get_post_thumbnail_id($post['ID']))) . '" width="75" height="75" alt="image"></a>
                        <div class="media-body">
                            <h4 class="media-heading"><a href="'. get_permalink($post['ID']) . '">' . $post['post_title'] . '</a></h4>
                            <small>October 9, 2013</small>
                        </div>
                    </div>
                  ';
    }
    return $theBuffer;
}
