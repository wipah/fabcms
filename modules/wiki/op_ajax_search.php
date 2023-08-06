<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/01/2019
 * Time: 11:21
 */

if (!$core->loaded)
    die('direct call');

$this->noTemplateParse = true;

$q      = $core->in($_POST['q'], true);
if (isset($_POST['tag'])){
    $where .= ' AND T.tag = \'' . ($core->in($_POST['tag'], true)) . '\' ';
}


$query = ' 
SELECT P.ID,
	   P.trackback,
       P.title,
       MATCH (P.title) AGAINST      (\'' . $q . '\' WITH QUERY EXPANSION) AS title_score,
       MATCH (P.content) AGAINST    (\'' . $q . '\' WITH QUERY EXPANSION) AS content_score
FROM ' . $db->prefix . 'wiki_pages AS P
LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
       ON T.page_ID = P.ID
WHERE MATCH (P.content) AGAINST     (\'' . $q . '\' WITH QUERY EXPANSION)
       OR MATCH (P.title) AGAINST   (\'' . $q . '\' WITH QUERY EXPANSION)
       AND P.service_page != 1
       AND P.language =              \'' . $core->shortCodeLang . '\'
       ' . $where . '
AND P.visible = 1
GROUP BY P.ID
ORDER BY title_score DESC, content_score DESC
LIMIT 15';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    $relog->write(['type'      => '4',
                   'module'    => 'WIKI',
                   'operation' => 'wiki_ajax_search_help',
                   'details'   => 'Cannot query. Query error. ' . $query,
                  ]);

    echo 'Query error. ';
    return;
}

if (!$db->affected_rows){
    echo $language->get('wiiki', 'ajaxSearchNoResult', null);
    return;
}

while ($row = mysqli_fetch_assoc($result)){
    echo '<span class="fabWikiAjaxSearchItem">
            <a href="' . $URI->getBaseUri()  . $this->routed . '/' . $row['trackback'] . '/">' . $row['title'] . '</a>
          </span>
          <br/>';
}