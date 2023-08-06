 <?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 18/10/2016
 * Time: 15:50
 */



function plugin_showfromtag($options) {
    global $user;
    global $core;

    if ($user->isAdmin) {
        $return = $options['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($options['parseInAdmin']) && $core->adminLoaded)
        return $return;


    global $core;
    global $db;
    global $fabMedia;
    global $lang;

    $whereFilter = '';
    if (!isset($options['tag']))
        return 'No tag set.';

    $tags = explode(', ', $options['tag']);
    foreach ($tags as $singleTag){

        $singleTag = $core->in($singleTag, true);

        $whereFilter .= 'tag = \'' . $singleTag . '\' OR
                         tag LIKE \', '. $singleTag . ',%\' OR
                         tag LIKE \'%, '. $singleTag . ',\' OR
                         tag LIKE \'%, '. $singleTag . ',%\'';
    }


    return 'Query tag: ' . $whereFilter;
}