<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/12/2016
 * Time: 09:32
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$content = $core->in($_POST['content']);

$titles = array();

$content = preg_replace_callback( '#\[\[(.[^\]\]]*?)\|(.*?)\]\]#im',
    function ($matches) use (&$titles) {
        global $core;

        if (in_array($matches[1], $titles))
            return;

        $titles[] = $core->in($matches[1], true);

    },
    $content
);

$content = preg_replace_callback( '#\[\[(.*?)\]\]#im',
    function ($matches) use (&$titles) {
        global $core;

        if (in_array($matches[1], $titles))
            return;

        $titles[] = $core->in($matches[1], true);

    },
    $content
);

if (count($titles) == 0)
    return;

foreach ($titles as $title){
    $queryTrackback .= 'title  = \'' . $title . '\' OR ';
}
$queryTrackback = substr($queryTrackback, 0, -3);

echo '<h4>Pages ok</h4>';
// Build the query
$query = 'SELECT * FROM ' . $db->prefix . 'wiki_pages 
WHERE `language` = \'' . $core->shortCodeLang . '\'
AND (' . $queryTrackback . ')';

if (!$result = $db->query($query)){
    echo 'Query error' . $query;
    return;
}

$pagesFound = array();
while ($row = mysqli_fetch_array($result)){
    echo ( (int) $row['visible'] == 0 ? '<del>' : '') . '&bull; <a target="_blank" href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['title'] . '</a>' . ((int) $row['visible'] == 0 ? '</del>' : '') ;
    $pagesFound[] = $row['title'];
}


echo '<h4>Pages ko</h4>';
$pageDiff = array_udiff($titles, $pagesFound, 'strcasecmp');
foreach ($pageDiff as $singlePage){
    echo '&bull; ' . $singlePage;
}