<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 02/04/2016
 * Time: 08:50
 */

/*
 * ************
 * GET COURSES
 * ************
 *
 */


// Push description and title
$this->addMetaData('description', $conf['formazione']['homepage']['seo_description']);
$this->addTitleTag($conf['formazione']['homepage']['title']);

$this->addScript($core->getConfig('formazione','commonHeadJS', 'extended_value'), 'text/javascript', true);

$courses = $formazione->getCourses();

$coursesList = '<div class="row"> <!-- Start row -->';
$i = 0;
foreach($courses as $singleCourse){
    $i++;

    // Check if course has a thumbnail
    if (file_exists( __DIR__ . '/thumbnails/course/' . $singleCourse['ID'] . '.jpg')){
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/thumbnails/video/' . $singleCourse['ID'] . '.jpg';
    } else{ // Generic thumbnail
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/res/png/play.png';
    }

    $coursesList .= '<div class="col-md-3">
            <div class="thumbnail">
                  <img src="' . $thumbnail . '" alt="' . $singleCourse['name'] . '" />
                  <div class="caption">
                    <h4>' . $singleCourse['name'] . '</h4>
                    <p>';


    $coursesList .= '<span style="color:' . ($singleCourse['account_status'] == 'enabled' ? 'green' : 'red' ) . '">&#9632;</span>
                        <a href="' .
    $URI->getBaseUri() .
    $this->routed .
    '/corso/' .
    $singleCourse['name_trackback'] .
    '/">' . $singleCourse['name'] . '</a>';

    $coursesList .= '<br/>
     ' . $singleCourse['short_description'] . '
                    </p>
                  </div>
            </div>
          </div>';

    if ($i == 4){
        $i = 0;
        $coursesList .= '</div> <!-- End video row -->
              <div class="row"> <!-- Start video row -->';
    }

}
$coursesList .= '</div> <!-- End Row -->';

/*
 * *****************
 * GET LATEST VIDEOS
 * *****************
 *
 */
$videos = $formazione->getMediaList(null, 1, 3);

foreach ($videos as $singleVideo){

    // Check if video has a thumbnail
    if (file_exists( __DIR__ . '/thumbnails/video/' . $singleVideo['ID'] . '.jpg')){
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/thumbnails/video/' . $singleVideo['ID'] . '.jpg';
    } else{ // Generic thumbnail
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/res/png/play.png';
    }

    // Check if user can view the video
    if ( (int) $singleVideo['access_level'] === 0 || $course['hasSubscription'] == true){
        $singleVideo['has_access'] = true;
    } else {
        $singleVideo['has_access'] = false;
    }


    $videosThumbnail .= '
            <div class="thumbnail">
                  <img src="' . $thumbnail . '" alt="' . $singleVideo['name'] . '" />
                  <div class="caption">
                    <h4>' . $singleVideo['name'] . '</h4>
                    <p>
                        <a href="' . ( $singleVideo['has_access'] === true ? ($URI->getBaseUri() . 'formazione/video/' . $singleVideo['name_trackback'] . '/') : 'javascript:subscription();') . '"
                            class="' . ($singleVideo['has_access'] === true ? 'btn btn-success' : 'btn btn-warning')  . '"
                            role="button">Guardalo</a> -
                    </p>
                  </div>
            </div>
          ';

}

$template->sidebar .= '<h2>Ultimi video inseriti</h2>' . $videosThumbnail;

echo '
<div class="row">
    <div class="col-md-12"><h1>Formazione</h1>
    ' . $conf['formazione']['homepage']['intro'] . '
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h3>Corsi a catalogo</h3>
        ' . $coursesList . '
    </div>
</div>


';