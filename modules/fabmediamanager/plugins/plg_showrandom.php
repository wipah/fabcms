<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/01/2019
 * Time: 11:50
 */

function plugin_showrandom($options)
{
    global $user;
    global $core;
    global $db;
    global $URI;

    if ($user->isAdmin) {
        // $return = $options['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($options['parseInAdmin']) && $core->adminLoaded)
        return $return;


    $where = '';

    if (isset($options['tags']))
    {
        $tags = explode(', ', $options['tags']);


        $tagsSearch = '';

        foreach ($tags AS $tag) {
            $tagsSearch .= ' Y.tags LIKE \'%' . $tag . '%\' OR ';
        }

        $where .= ' ('. substr($tagsSearch,0, -3) . ') ';
    }

    if (isset($options['subtype']))
        $where .= ' MASTER_SUB.subtype = \'' . ($core->in($options['subtype'])) . '\' ';

    switch ($options['rand'])
    {
        case 'month':
            $rand = 'RAND(' . date('ym') . ')';
            break;
        case 'week':
            $rand = 'RAND(' . date('W', strtotime(date('Y-m-d'))) . ')';
            break;
        case 'day':
            $rand = 'RAND(' . date('ymd') . ')';
            break;
        case 'random':
        default:
            $rand = 'RAND()';
    }

    $query = '
      SELECT MIN(ID) AS min,
             MAX(ID) AS max
      FROM ' . $db->prefix . 'fabmedia;';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select'))
    {
        echo 'Query error.' . $query;
        return;
    }

    $row = mysqli_fetch_assoc($result);
    $min = $row['min'];
    $max = $row['max'];

    $query = '                     
    
    SELECT FABMEDIA.master_ID,
            FABMEDIA.ID AS media_ID, 
             FABMEDIA.user_ID, 
             FABMEDIA.title, 
             FABMEDIA.trackback, 
             MASTER.filename, 
             MASTER.extension
    FROM ' . $db->prefix . 'fabmedia FABMEDIA
    
    JOIN ( 
         SELECT ID FROM
                 ( SELECT Y.ID
                 
                   FROM ( SELECT ' . $min . ' + (' . $max . ' - ' . $min .  ' + 1 - 50) * ' . $rand . ' 
                       AS start FROM DUAL ) AS init
                   JOIN ' . $db->prefix . 'fabmedia Y
                   LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS MASTER_SUB
                     ON Y.master_ID = MASTER_SUB.ID
                   WHERE  Y.ID > init.start
                       AND Y.enabled = 1
                        /* Filters */
                        ' . (!empty($where) ? ' AND ' . $where  : '' ). '
                     ORDER BY Y.ID
                     LIMIT 50           -- Inflated to deal with gaps
                 ) Z ORDER BY RAND()
                
                LIMIT 1                -- number of rows desired (change to 1 if looking for a single row)
              ) T ON FABMEDIA.ID = T.ID
    
    LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS MASTER
    ON MASTER.ID = FABMEDIA.master_ID';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')) {
        return 'Query error.<pre>' .$db->lastError  . PHP_EOL . $query . '</pre>';
    }

    if (!$db->affected_rows) {
        return 'No img.';
    }

    $row = mysqli_fetch_assoc($result);
    $pos = strrpos($row['filename'], '.' . $row['extension']);

    $imageFinalLQPath = substr_replace($row['filename'], '_lq.' . $row['extension'], $pos, strlen('.' . $row['extension']));

    $return .= '
            <a href="' . $URI->getBaseUri() . 'fabmediamanager/showimage/' . $row['media_ID'] . '-' . $row['trackback'] . '/">
                <img class="img-fluid" src="' . $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath . '" alt="' . $row['title'] . '">
            </a>';

    return $return;

}