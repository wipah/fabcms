<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/03/2016
 * Time: 18:51
 */

if (!$core->loaded)
    die ("Direct access.");

// Check if trackback has been passed
if (!isset($path[3])){
    echo 'No trackback';
    return;
}

$this->addScript($core->getConfig('formazione','commonHeadJS', 'extended_value'), 'text/javascript', true);

$trackback = $core->in($path[3], true);

// Get ID from trackback
$video_ID = $formazione->getVideoIdFromTrackback($trackback);

// Check if user has access to the video
if (false == $formazione->userHasMediaAccess($video_ID)){
    echo 'Gentile utente. Il video &egrave; disponibile solo attraverso una sottoscrizione.';
    return;
} else { // User has access
    // Get the video
    $video = $formazione->getMediaInfo($video_ID);

    switch ($video['subtype']){
        case 1: // youtube
            $video_spot =  '<div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item"
                                        src="https://www.youtube.com/embed/' . $video['youtube_ID'] . '?autoplay=1">
                                </iframe>
                            </div>';
            break;
        default:
            echo 'Video has no handler';
            break;
    }
}


// Get the courses avaliable for the selected media
$courses = $formazione->getCoursesFromMedia($video_ID);

$coursesList = '';
foreach($courses as $singleCourse){

    $coursesList .= '<span style="color:' . ($singleCourse['status'] == 'enabled' ? 'green' : 'red' ) . '">&#9632;</span>
                        <a href="' .
                                  $URI->getBaseUri() .
                                  $this->routed .
                                  '/corso/' .
                                  $singleCourse['name_trackback'] .
                                  '/">' . $singleCourse['name'] . '</a>';
}

$this->addMetaData('description', $video['description_seo']);
$this->addTitleTag($video['name']);

// Build the navbar
$template->navBar[] = '<a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/">Formazione</a>';
$template->navBar[] = '<em>Video: ' . $video['name'] . '</em>';

echo '<h2>Video: ' . $video['name'] . '</h2>';

echo '
<div class="row">
    <div class="col-md-9">
        ' . $video_spot . '
    </div>
    <div class="col-md-3">
    <h4>Corsi associati al video</h4>
    ' . $coursesList . '
    </div>

</div>
<div class="row">
    <div class="col-md-12">
    <div style="background-color:#DEDEDE; padding: 8px; margin-top:12px"><strong>Descrizione del video</strong></div>
    ' . $video['description'] . '</div>
</div>
';

$stats->write(['IDX' => $video_ID, 'module' => 'formazione', 'submodule' => 'videoView']);