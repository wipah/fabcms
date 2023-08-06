<?php
if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");


if (!isset($path[4])) {
    echo 'Course ID not passed';
}
$course_ID = (int) $path[4];

if ($path[6] === 'save'){
    $this->noTemplateParse = true;
    $json = json_decode($_POST['result']);
    $items = $json[0]->items;

    $i = 1;

    foreach ($items as $item) {
        $pieces = explode('||', $item->html);

        preg_match('#ID\:([0-9]+)#', $pieces[0], $ID);

        $query = 'UPDATE ' . $db->prefix . 'formazione_courses_media 
                  SET `order` = ' . $i . '
                  WHERE media_ID = ' . $ID[1] . ' 
                  AND course_ID = ' . $course_ID . ' 
                  LIMIT 1';

        $db->setQuery($query);

        if (!$db->executeQuery('update')) {
            echo 'Query error: <pre>' . $query . '</pre>';
        }

        $i++;
    }


    return;
}

$this->addJsFile($URI->getBaseUri(true) . '/modules/formazione/lib/js/jquery-sortable.js');

$query = 'SELECT * 
          FROM ' . $db->prefix . 'formazione_courses
          WHERE ID = ' . $course_ID . ' 
          LIMIT 1';

$db->setQuery($query);

$template->navBarAddItem('Formazione', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione'));
$template->navBarAddItem('Admin', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/');
$template->navBarAddItem('Courses', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/course/');

if (!$resultCourse = $db->executeQuery('select')) {
    echo '<pre>' . $query . '</pre>';
    return;
}

$rowCourse = mysqli_fetch_assoc($resultCourse);
$template->navBarAddItem($rowCourse['name'], $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/course/3/');
$template->navBarAddItem('Video order', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/course/3/video-order/');

if (!$db->affected_rows){
    echo 'No course';
    return;
}

$query = 'SELECT MEDIA.ID media_ID,
                 MEDIA.youtube_ID,
                 MEDIA.name
          FROM ' . $db->prefix . 'formazione_courses_media COURSE
          LEFT JOIN ' . $db->prefix . 'formazione_media MEDIA
            ON COURSE.media_ID = MEDIA.ID
          WHERE COURSE.course_ID = ' . $course_ID . '
          ORDER BY COURSE.order ASC;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows) {
    echo 'Course has no media. ';
    return;
}

echo '
<style>
body.dragging, body.dragging * {
  cursor: move !important;
}

.dragged {
  position: absolute;
  opacity: 0.5;
  z-index: 2000;
}

ol.sortable {
    list-style-type: none;
}
ol.sortable li {
    padding: 4px;
    border: 1px solid gray;
    margin-top: 6px;
    border-left: 24px solid lightgray;
}

</style>


<ol class="sortable">';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<li><!--ID:' . $row['media_ID'] . '||--> ' . $row['name'] . '</li>';
}

echo '</ol>
<button onclick="serialize();">Salva</button>
<div id="serial">

</div>
';

$script = '$(function  () {
  sortable(".sortable", {
  itemSerializer: (serializedItem, sortableContainer) => {
    return {
      position:  serializedItem.index + 1,
      html: serializedItem.html
    }
  }
});
});

function serialize(){
    var result = JSON.stringify( sortable(".sortable", "serialize"));
    
    $.post( "' . $URI->getBaseUri() . $this->routed . '/admin-cp/course/' . $course_ID . '/video-order/save/", { result: result })
        .done(function( data ) {
        $("#serial").html( data );
    });
    
}
';

$this->addScript($script);