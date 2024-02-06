<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/11/2016
 * Time: 12:44
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if (!isset($core->adminLoaded)) {
    echo 'Direct call';

    return;
}
$this->noTemplateParse = true;

if (isset($_POST['title']) && strlen($_POST['title']) > 0) {
    $where = true;
    $titleFilter = ' title LIKE \'%' . $core->in($_POST['title'], true) . '%\'';
}

if (isset($_POST['tag']) && strlen($_POST['tag']) > 0) {
    if ($where === true)
        $tagFilter = ' AND ';

    $where = true;
    $tagFilter .= ' T.tag = \'' . $core->in($_POST['tag'], true) . '\'';
}

if (isset($_POST['language']) && strlen($_POST['languageFilter']) > 0) {
    if ($where === true)
        $languageFilter = ' AND ';

    $where = true;
    $languageFilter .= ' language = \'' . $core->in($_POST['language'], true) . '\'';
}

switch ((int) $_POST['visible']) {
    case 1:
        if ($where === true)
            $tagVisible = ' AND ';

        $where = true;
        $tagVisible .= ' visible = 1';
        break;
    case 0:
        if ($where === true)
            $tagVisible = ' AND ';

        $where = true;
        $tagVisible .= ' visible = 0';
        break;
}

if (isset($_POST['status']) && (int) $_POST['status'] > 0) {
    if ($where === true)
        $statusFilter = ' AND ';

    $where = true;
    $statusFilter .= ' P.status_ID = \'' . (int) ($_POST['status']) . '\'';
}

if (isset($_POST['category']) && (int) $_POST['category'] > 0) {
    if ($where === true)
        $categoryFilter = ' AND ';

    $where = true;
    $categoryFilter .= ' P.category_ID = \'' . (int) ($_POST['category']) . '\'';
}

/* Creation date */
if (isset($_POST['createdFrom']) && strlen($_POST['createdFrom']) > 1) {
    if ($where === true) {
        $creationDate = ' AND ';
    }

    $where = true;
    $creationDate .= ' p.creation_date >= \'' . ($core->in($_POST['creation_date'])) . '\'';
}

if (isset($_POST['createdTo']) && strlen($_POST['createdTo']) > 1) {
    if ($where === true)
        $creationDate .= ' AND ';

    $where = true;
    $creationDate .= ' p.creation_date <= \'' . ($core->in($_POST['createdTo'])) . '\'';
}
/* End Creation date */

/* Update date */
if (isset($_POST['lastUpdateTo']) && strlen($_POST['lastUpdateTo']) > 1) {
    if ($where === true)
        $updateDate = ' AND ';

    $where = true;
    $updateDate .= ' p.last_update <= \'' . ($core->in($_POST['lastUpdateTo'])) . '\'';
}


if (isset($_POST['lastUpdateFrom']) && strlen($_POST['lastUpdateFrom']) > 1) {
    if ($where === true)
        $updateDate = ' AND ';

    $where = true;
    $updateDate .= ' p.last_update >= \'' . ($core->in($_POST['lastUpdateFrom'])) . '\'';
}

$query = '
SELECT
    M.ID AS master_ID,
    P.ID,
    P.title,
    P.title_alternative,
    P.visible,
    P.creation_date,
    P.last_update,
    P.seo_score,
    P.metadata_description,
    C.name AS category_name,
    C.lang AS category_lang,
    S.status,
    ST.words,
    P.trackback,
    P.language,
    SUM(DAILY.hits) AS hits,
    GROUP_CONCAT(DISTINCT T.tag ORDER BY T.tag SEPARATOR \', \') AS tag,
    GROUP_CONCAT(DISTINCT K.keyword ORDER BY K.keyword SEPARATOR \', \') AS keywords
FROM
    fabcms_wiki_pages AS P
LEFT JOIN
    fabcms_wiki_masters AS M ON P.master_ID = M.ID
LEFT JOIN
    fabcms_wiki_categories_details AS C ON P.category_ID = C.ID
LEFT JOIN
    fabcms_wiki_pages_status AS S ON P.status_ID = S.ID
LEFT JOIN
    fabcms_wiki_pages_tags AS T ON T.page_ID = P.ID
LEFT JOIN
    fabcms_wiki_pages_keywords AS K ON K.page_ID = P.ID
LEFT JOIN
    (SELECT IDX, SUM(hits) AS hits
     FROM fabcms_stats_daily
     WHERE module = \'wiki\'
       AND submodule = \'pageView\'
       AND date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
     GROUP BY IDX) AS DAILY ON P.ID = DAILY.IDX
LEFT JOIN
    fabcms_wiki_pages_statistics AS ST ON ST.page_ID = P.ID
'. ($where === true ? ' WHERE ' : ' ' ) .
$titleFilter .
$tagFilter .
$tagVisible .
$languageFilter .
$creationDate .
$updateDate .
$categoryFilter .
$statusFilter . ' 
GROUP BY P.ID 
ORDER BY P.ID DESC';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;

    return;
}

if (!$db->affected_rows) {
    echo '<div class="alert alert-warning" role="alert">No page</div>';

    return;
}

echo '<table id="tablesortable" class="table table-condensed table-bordered table-striped table-sm">
    <thead>
      <tr>
        <th>ID</th>
        <th>Language</th>
        <th>Creation date</th>
        <th>Update date</th>
        <th>Page</th>
        <th>Tags</th>
        <th>Words</th>
        <th>Description Lenght</th>
        <th>Keywords</th>
        <th>Score</th>
        <th>Week hits</th>
        <th>Category</th>
        <th>Status</th>
        <th>Operation</th>
      </tr>
    </thead>
    <tbody>';

$totalWords = 0;
$totalHits  = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $totalWords += (int) $row['words'];
    $totalHits  += (int) $row['hits'];

    if ((int) $row['visible'] === 1) {
        $visibleFlag = '&nbsp;<span class="float-right" style="color:green">&checkmark;</span>';
    } else {
        $visibleFlag = '&nbsp;<span class="float-right" style="color:red">&cross;</span>';
    }

    echo '
    <tr>
        <td>' . $row['ID'] . '</td>
        <td>' . $row['language'] . '</td>
        <td>' . $core->getDate($row['creation_date']) . '</td>
        <td>' . $core->getDate($row['update_date']) . '</td>
        <td>' . $row['title'] . (!empty($row['title_alternative']) ? ' - <em>' . $row['title_alternative'] . '</em>' : '') . $visibleFlag . '</td>
        <td>' . $row['tag'] . '</td>
        <td>' . $row['words'] . '</td>
        <td>' . ( strlen($row['metadata_description']) ) . '</td>
        <td>' . $row['keywords'] . '</td>
        <td>' . $row['seo_score'] . '</td>
        <td>' . $row['hits'] . '</td>
        <td>' . $row['category_name'] . '</td>
        <td>' . $row['status'] . '</td>
        <td>
            <a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">Edit</a> | <a target="_blank" href="' . $URI->getBaseUri(true) . $row['language'] . '/' . $core->router->getRewriteAlias('wiki') . '/' . $row['trackback'] . '/">Show</a>
        </td>
    </tr>';
}

echo '</tbody>
    <tfoot>
        <td colspan="6"></td>
        <td>' . $totalWords . '</td>
        <td>' . $totalHits . '</td>
        <td colspan="2"></td>
    </tfoot>
    </table>';