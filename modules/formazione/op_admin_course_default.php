<?php
if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

$template->navBarAddItem('Formazione', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/');
$template->navBarAddItem('Admin', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/');
$template->navBarAddItem('Courses', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/course/');
$template->navBarAddItem('Homepage');


echo '<h1>Course list</h1>';

$query = 'SELECT COURSES.*, 
            (SELECT COUNT(ID) 
             FROM ' . $db->prefix .'formazione_courses_media
             WHERE course_ID= COURSES.ID) total_media
          FROM ' . $db->prefix . 'formazione_courses COURSES';



if (!$result = $db->query($query)){
    echo '<pre>' . $query . '</pre>';
    return;
}

if (!$db->affected_rows){
    echo 'No courses are present';
    return;
}

echo '<div class="table-responsive">
        <table class="table table-bordered table-responsive table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Total video</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>
';

while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' . $row['ID'] . '</td>
                <td>' . $row['name'] . '</td>
                <td>' . $row['total_media'] . '</td>
                <td><a href="' . $URI->getBaseUri() . $this->routed . '/admin-cp/course/' . $row['ID'] . '/video-order/">
                        Video order
                    </a>
                </td>
              </tr>';
}

echo '    </tbody>
    </table>
</div>';
return;