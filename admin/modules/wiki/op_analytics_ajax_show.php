<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 18/05/2018
 * Time: 16:27
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$grouping       =   (int) $_POST['filterGrouping'];
$compareWith    =   (int) $_POST['filterCompareWith'];

if (!empty($_POST['filterFrom']))
    $whereDateMain .= ' AND date >= \'' . implode('-', array_reverse(explode('-',$core->in($_POST['filterFrom'])))) . '\'';

if (!empty($_POST['filterTo']))
    $whereDateMain .= ' AND date <= \'' . implode('-', array_reverse(explode('-',$core->in($_POST['filterTo']))))  . '\'';


if (!empty($_POST['filterTags'])) 
{

    $tagsArray = explode (', ', $_POST['filterTags']);

    $whereTag = ' PT.tag IN (';

    foreach ($tagsArray AS $singleTag){
        $whereTag .= '\'' . $core->in($singleTag, true) . '\', ';
    }

    $whereTag = substr($whereTag,0, - 2) . ') ';

    if (empty( $where ))
        $where = ' AND ';

    $where .= $whereTag;

    $mainJoin .= 'LEFT JOIN fabcms_wiki_pages_tags PT
       ON PT.page_ID = FWPM.ID ';

    $subJoin .= 'LEFT JOIN ' . $db->prefix . 'wiki_pages_tags PT
              ON PT.page_ID = WP.ID';

    if (!empty($mainWhere) )
        $mainWhere .= ' AND ';

    $mainWhere .= $whereTag . ' ';
}


if (!empty($_POST['filterWordsMoreThan'] || !empty($_POST['filterWordsLessThan'])))
{
    $mainJoin .= ' LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS PSTAT ON PSTAT.page_ID = FWPM.ID ';

}

if ( !empty($_POST['filterWordsMoreThan'] )) {
    if (empty($mainWhere) )
        $mainWhere .= ' AND ';

    $mainWhere .= ' AND PSTAT.words >= ' . ( (int) $_POST['filterWordsMoreThan']) . ' ';
}

if (!empty($_POST['filterWordsLessThan'])) {
    if (empty($mainWhere) )
        $mainWhere .= ' AND ';

    $mainWhere .= ' AND PSTAT.words <= ' . ( (int) $_POST['filterWordsLessThan']) . ' ';
}

switch ($grouping) {
    default:
    case 1: // Daily
        $selectHits = 'SD.hits';
        $selectDate = 'SD.date';
        $periodName = 'Date';
        $groupBy    = '';
        break;
    case 2:
        $selectHits = 'SUM(SD.hits) AS hits';
        $selectDate = 'WEEK(SD.date) AS date';
        $groupBy    = 'GROUP BY WEEK(SD.date), WP.ID';
        $periodName = 'Week';
        break;
    case 3:
        $selectHits = 'SUM(SD.hits) AS hits';
        $selectDate = 'MONTH(SD.date) AS date';
        $groupBy    = 'GROUP BY MONTH(SD.date), WP.ID';
        $periodName = 'Month';
        break;
    case 4:
        $selectHits = 'SUM(SD.hits) AS hits';
        $selectDate = 'YEAR(SD.date) AS date';
        $groupBy    = 'GROUP BY YEAR(SD.date), WP.ID';
        $periodName = 'Year';
        break;
}

switch ($compareWith){
    case 1:     // No compare
        break;
    case 2:     //
        $compare = true;
        $substractRange = ' 1 DAY';
        break;
    case 3:
        $compare = true;
        $substractRange = ' 1 WEEK';
        break;
    case 4:
        $compare = true;
        $substractRange = ' 1 MONTH';
        break;
    case 5:
        $compare = true;
        $substractRange = ' 3 MONTH';
        break;
    case 6:
        $compare = true;
        $substractRange = ' 4 MONTH';
        break;
    case 7:
        $compare = true;
        $substractRange = ' 6 MONTH';
        break;
    case 8:
        $compare = true;
        $substractRange = ' 1 YEAR';
        break;
}

$wherePeriod_1 = '';
if (!empty($_POST['filterFrom'])) {
    $wherePeriod_1 .= ' AND SD.date >= DATE_SUB( \'' . implode('-', array_reverse(explode('-',$core->in($_POST['filterFrom'])))) . '\', INTERVAL ' . $substractRange . ' ) ';
}

if (!empty($_POST['filterTo'])) {
    $wherePeriod_1 .= ' AND SD.date <= DATE_SUB( \'' . implode('-', array_reverse(explode('-',$core->in($_POST['filterTo'])))) . '\', INTERVAL ' . $substractRange . ' ) ';
}

if ($compareWith > 1 ) {
    $selectCompare = ', period_compare_1.date AS date_1,
                      period_compare_1.hits AS compare_1_hits,  
                      (period.hits - period_compare_1.hits) AS delta';

}

$query = '
SELECT
      FWPM.ID,
      FWPM.title,
      FWPM.language,
      period.hits AS hits,
      period.date AS date_s
      ' . $selectCompare . '
 FROM ' . $db->prefix . 'wiki_pages FWPM

 LEFT JOIN(
       SELECT SD.IDX,
              SUM(SD.hits) AS hits,
              WP.language, 
              ' . $selectDate . ',
              WP.ID as page_ID
       FROM ' . $db->prefix . 'stats_daily SD
       LEFT JOIN ' . $db->prefix . 'wiki_pages WP
         ON SD.IDX = WP.ID
       ' . $subJoin . '
       WHERE module    = \'wiki\'
        ' . $where . $whereDateMain . '
       AND submodule = \'pageview\'
       ' . $groupBy . '
  
 )
AS period
ON period.IDX = FWPM.ID
AND period.language = FWPM.language


';

if ($compareWith > 1) {
    $query .= '
     LEFT JOIN(
       SELECT SD.IDX,
              SUM(SD.hits) AS hits,
              WP.language, 
              ' . $selectDate . ',
              WP.ID as page_ID
       FROM ' . $db->prefix . 'stats_daily SD
       LEFT JOIN ' . $db->prefix . 'wiki_pages WP
         ON SD.IDX = WP.ID
         ' . $subJoin . '
       WHERE module    = \'wiki\'
        ' . $wherePeriod_1 . $where . '
       AND submodule = \'pageview\'
      ' . $groupBy . '
  
 )
AS period_compare_1
ON period_compare_1.IDX = FWPM.ID
AND period_compare_1.language = FWPM.language
    ';

    $orderBy = ' ORDER BY delta ASC, `period`.`hits` DESC';
}

$query .= $mainJoin . ' ' . ( empty($mainWhere) ? '' : ' WHERE ') . $mainWhere . $orderBy;


$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $db->lastError . '<pre>' . $query . '</pre>';
    return;
}

if (!$db->numRows) {
    echo 'No rows';
    return;
}

$table = '<table id="analyticsTable" class="table table-striped table-bordered table-sm mt-4">
    <thead>
      <tr>
        <th>' . $periodName . '</th>
        <th>Page</th>
        <th>Lang</th>
        <th>Hits</th>';

if ($compareWith > 1 ){
    $table .= '
        <th>Hits C1</th>
        <th>Delta</th>';
}

$table .= '
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($result)) {

    $totalPeriod += $row['hits'];
    $totalComparePeriod += $row['compare_1_hits'];
    $table .= '
          <tr>
            <td>' . $row['date']  .'</td>
            <td>' . $row['title']  .'</td>
            <td>' . $row['language']  .'</td>
            <td>' . $row['hits']  .'</td>';

    if ($compareWith > 1 ){
        $table .= '
            <td>' . $row['compare_1_hits']  .'</td>
            <td>' . $row['delta']  .'</td>';
    }

    $table .= '
          </tr>';
}

$table .= '
    </tbody>
  </table>';

echo '<div class="row mt-4">
    <div class="col-md">
        <div class="card bg-light">
          <div class="card-header">Period</div>
          <div class="card-body bg-light"><p style="font-size: 4em; color: #384937; text-align: center">' . ((int) $totalPeriod) . '</p></div>
        </div>
    </div>';

    if ($compareWith > 1 ){
        echo '
            <div class="col-md">
                <div class="card bg-light">
                  <div class="card-header">Compared period</div>
                  <div class="card-body bg-light"><p style="font-size: 4em; color: #384937; text-align: center">' . ((int) $totalComparePeriod) . '</p></div>
                </div>
            </div>
            ';
    }

    echo '
</div>';

echo $table;