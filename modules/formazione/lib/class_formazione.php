<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 13/03/2016
 * Time: 12:23
 */

namespace Crisasoft\FabCMS;


class formazione
{

    public function getCourses($limit = null){
        global $core;
        global $db;
        global $conf;
        global $user;
        global $log;

        $query = 'SELECT C.ID as course_ID,
		 C.name,
		 C.name_trackback,
		 C.tags,
		 C.thumb_image,
		 C.short_description,
		 S.expiring_date,
		 S.purchase_date,
		 
		 IF(S.expiring_date >= CURDATE() AND S.user_ID = ' . ($user->logged === true ? $user->ID : 0) . ', \'enabled\', \'disabled\') AS account_status
FROM fabcms_formazione_courses AS C
LEFT JOIN fabcms_formazione_courses_subscriptions AS S
ON C.ID = S.course_ID
' . (isset($limit) ? 'LIMIT ' . (int) $limit : '') . '';


        
        if (!$result = $db->query($query)){
            $log->write('formazione_get_courses_error','FORMAZIONE', $query);
            return false;
        }

        if (!$db->affected_rows)
            return false;

        $return = array();

        while ($row = mysqli_fetch_array($result)){
            $return[] = array(
                'name_trackback' => $row['name_trackback'],
                'short_description' => $row['short_description'],
                'name' => $row['name'],
                'account_status' => $row['account_status'],
                'access_level' => $row['access_level']
            );
        }

        return $return;

    }

    public function getVideoIdFromTrackback($trackback){
        global $core;
        global $db;

        $query = 'SELECT ID
                  FROM ' . $db->prefix . 'formazione_media
                  WHERE name_trackback = \'' . $core->in($trackback, true) . '\'
                  LIMIT 1;';
        

        if (!$result = $db->query($query)){
            die ("Query error in trackback reverse");
        }

        if (!$db->affected_rows){
            return false;
        } else {
            $row = mysqli_fetch_array($result);
            return (int) $row['ID'];
        }
    }

    public function getCoursesIdFromTrackBack($trackback){
        global $core;
        global $db;

        $query = 'SELECT ID
                  FROM ' . $db->prefix . 'formazione_courses
                  WHERE name_trackback = \'' . $core->in($trackback, true) . '\'
                  LIMIT 1;';
        

        if (!$result = $db->query($query)){
            die ("Query error in trackback reverse");
        }

        if (!$db->affected_rows){
            return false;
        } else {
            $row = mysqli_fetch_array($result);
            return $row['ID'];
        }
    }

    public function userHasAccess($course){
        global $core;
        global $db;
        global $user;

        $query = 'SELECT * FROM ' .  $db->prefix .  'formazione_courses_subscriptions as S
                  WHERE S.course_ID = ' . (int) $course . '
                  AND S.user_ID = ' . ($user->logged === true ? $user->ID : 0) . '
                  AND S.expiring_date > CURDATE()
                  LIMIT 1;
            ';


        
        if (!$result = $db->query($query)){
            die ("Query error in userHasAccess subroutine. " . $query);
        }

        if ($db->affected_rows) {
            return true;
        } else {
            return false;
        }
    }

    public function userHasMediaAccess($media_ID){
        global $core;
        global $db;
        global $user;

        $media_ID = (int) $media_ID;

        $query = 'SELECT * FROM ' . $db->prefix .  'formazione_media WHERE ID = ' . (int) $media_ID . ' LIMIT 1';
        
        $result = $db->query($query);

        $row = mysqli_fetch_array($result);

        if ((int)$row['access_level'] == 0 )
            return true;

        $query = '
        SELECT M.ID as media_ID, C.name AS course_name,
          M.access_level
        FROM ' . $db->prefix . 'formazione_media AS M
        LEFT JOIN  ' . $db->prefix . 'formazione_courses_media AS CM
        ON CM.media_ID = M.ID
        LEFT JOIN ' . $db->prefix . 'formazione_courses AS C
        ON C.ID = CM.course_ID
        LEFT JOIN ' . $db->prefix . 'formazione_courses_subscriptions AS S
        ON S.course_ID = C.ID
        WHERE S.expiring_date >= CURRENT_DATE()
        AND M.ID = ' . $media_ID . '
        AND S.user_ID = ' . ($user->logged === true ? $user->ID : 0 ) . '
        ';

        

        if (!$result = $db->query($query)){
            die ("Query error. " . $query);
        }

        if (!$db->affected_rows){
            return false;
        } else {
            return true;
        }

    }
    public function getMediaList($course_ID = null, $type = null, $limit = null){
        global $db;
        global $core;

        if (isset($course_ID))
            $whereFilter = 'CM.course_ID = \'' . $course_ID . '\'';

        if (isset($type)){
            if (isset($whereFilter))
                $whereFilter .= ' AND ';

            $whereFilter .= 'M.type = \'' . (int) $type . '\'';

        }

        $query = '
        SELECT M.ID as media_ID,
        M.access_level, 
        M.ID AS media_ID,
        M.name, M.name_trackback
        FROM ' . $db->prefix .'formazione_media AS M
        LEFT JOIN ' . $db->prefix .'formazione_courses_media AS CM 
        ON CM.media_ID = M.ID
                ' .  ( isset($whereFilter) ? ' WHERE ' . $whereFilter : '' ). '
                
        ORDER BY `order` ASC
        ' . (isset($limit) ? ' LIMIT ' . (int) $limit : '' ) . ';';

        

        if (!$result = $db->query($query)){
            die ($query);
        }

        if (!$db->affected_rows)
            return false;

        $return = array();

        while ($row = mysqli_fetch_array($result)){
            $return[] = array(
                              'name_trackback' => $row['name_trackback'],
                              'ID' => $row['media_ID'],
                              'name' => $row['name'],
                              'access_level' => $row['access_level']
                             );
        }

        return $return;
    }

    public function getCourseInfo($course_ID){
        global $db;
        global $core;

        $query = 'SELECT * FROM ' . $db->prefix . 'formazione_courses WHERE ID = ' . (int) $course_ID . ' LIMIT 1;';

        

        if (!$result = $db->query($query)){
            die ($query);
        }

        if (!$db->affected_rows){
            return false;
        } else {
            return mysqli_fetch_array($result);
        }
    }

    public function getMediaInfo($ID){
        global $db;
        global $query;

        $query = 'SELECT * FROM ' . $db->prefix . 'formazione_media WHERE ID = ' . (int) $ID. ' LIMIT 1';

        
        if (!$result = $db->query($query)){
            die ("Query error. " . $query);
        }

        return mysqli_fetch_array($result);
    }

    public function getCoursesFromMedia($media_ID){
        global $db;
        global $core;
        global $user;
        $media_ID = (int) $media_ID;

        $query = 'SELECT C.ID as course_ID, M.ID,
		C.name,
		C.name_trackback,
		S.expiring_date,
		IF(S.expiring_date >= CURDATE() AND S.user_ID = ' . ($user->logged === true ? $user->ID : 0)  . ', \'enabled\', \'disabled\') AS account_status
        FROM ' . $db->prefix . 'formazione_courses AS C
        LEFT JOIN ' . $db->prefix . 'formazione_courses_media AS M
        ON C.ID = M.course_ID
        LEFT JOIN ' . $db->prefix . 'formazione_courses_subscriptions AS S
        ON S.course_ID = C.ID
        WHERE M.media_ID = ' . $media_ID . ';';


        
        if(!$result = $db->query($query)){
            die ("Query error. " . $query);
        }

        $return = array();
        while ($row = mysqli_fetch_array($result)){
            $return[] = array('name' => $row['name'], 'name_trackback' => $row['name_trackback'] , 'status' => $row['account_status']);
        }
        return ($return);
    }
}