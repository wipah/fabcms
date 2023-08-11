<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/03/2016
 * Time: 12:21
 */


if (!$core->loaded)
    die ("No access");

if (!isset($path[3])){
    echo 'No course passed';
    return;
}

$trackback = $path[3];

// Get the course ID
$course_ID = $formazione->getCoursesIdFromTrackBack($trackback);

// Get information about this course
$course = $formazione->getCourseInfo($course_ID);

$this->addScript($core->getConfig('formazione','commonHeadJS', 'extended_value'), 'text/javascript', true);

// Push description and title
$this->addMetaData('description', $course['SEO_description']);
$this->addTitleTag('Corso online di formazione: ' . $course['name']);

// Build the navbar
$template->navBarAddItem('Formazione', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione'));
$template->navBarAddItem($course['name']);

if (false === $formazione->userHasAccess($course_ID)){
    $course['hasSubscription'] = false;
} else{
    $course['hasSubscription'] = true;
}

// Build the sidebar
echo '
<div class="row">
    <div class="col-sm-5">
        <h1>' . $course['name'] . '</h1>
    </div>
    <div class="col-sm-7">
        Disponibile da: ' . $core->getDate($course['avaliable_date']) . '.
        ' . ($course['hasSubscription'] === true
        ? '<span style="color:green">Hai acquistato questo corso</span>'
        : '<span style="color:red">Non hai ancora acquistato questo corso o la sottoscrizione è scaduta</span><br/>
           <span class="button btn-info">
                <i class="fa fa-cart-plus"></i>
                <a href="' . $course['subscription_link']. '" class="btn btn-default"> Compralo subito</a>
           </span>
        ') . '    
    </div>
</div>';

echo '<div class="row">
    <div class="col-md-12">
    <div style="background-color:#DEDEDE; padding: 8px; margin-top:12px"><strong>Descrizione del corso</strong></div>
' . $course['description'] . '</div>
</div>';

// Get all videos
$videos = $formazione->getMediaList($course_ID, 1);

echo '

 <div id="myModal" class="modal fade" data-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Sottoscrizione richiesta</h4>
                </div>
                <div class="modal-body">
                    <p>Per l\'accesso a questo video è necessaria una sottoscrizione.</p>
                    <p class="text-warning"><small>Sblocca subito i video del corso didattivo.</small></p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-large btn-info" href="' . $course['subscription_link'] .'" target="_blank">Iscriviti adesso </a>
                </div>
            </div>
        </div>
</div>

<div class="row">
    <div class="col-md-12">
    <div style="background-color:#DEDEDE; padding: 8px; margin-top:12px; margin-bottom:12px"><strong>Video del corso</strong></div>
    </div>
</div>';

echo '
<div class="row">  <!-- Start video row -->';
$i = 0;
foreach ($videos as $singleVideo){
    $i++;

    // Check if video has a thumbnail
    if (file_exists( __DIR__ . '/thumbnails/video/' . $singleVideo['ID'] . '.jpg')) {
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/thumbnails/video/' . $singleVideo['ID'] . '.jpg';
    } else{ // Generic thumbnail
        $thumbnail = $URI->getBaseUri(true) . 'modules/formazione/res/png/play.png';
    }

    // Check if user can view the video
    if ( (int) $singleVideo['access_level'] === 0 || $course['hasSubscription'] == true) {
        $singleVideo['has_access'] = true;
    } else {
        $singleVideo['has_access'] = false;
    }

    echo '<div class="col-md-3">
            <div class="thumbnail">
                  <img src="' . $thumbnail . '" alt="' . $singleVideo['name'] . '" />
                  <div class="caption">
                    <h4>' . $singleVideo['name'] . '</h4>
                    <p>
                        <a href="' . ($singleVideo['has_access'] === true ? ($URI->getBaseUri() . 'formazione/video/' . $singleVideo['name_trackback'] . '/') : 'javascript:subscription();') . '"
                            class="' . ($singleVideo['has_access'] === true ? 'btn btn-success' : 'btn btn-warning')  . '"
                            role="button">Guardalo</a> -
                    </p>
                  </div>
            </div>
          </div>';

    if ($i == 4){
        $i = 0;
        echo '</div> <!-- End video row -->
              <div class="row"> <!-- Start video row -->';
    }
}

echo '</div> <!-- End video row -->';

$script = '
function subscription(){
    $(\'#myModal\').appendTo("body").modal();
}';

$this->addScript($script);
