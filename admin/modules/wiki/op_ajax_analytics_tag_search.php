<?php
if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

$tag            =   $core->in($_POST['tag'], true);
$internalTag    =   $core->in($_POST['internalTag'], true);

$query = '
SELECT 
    YEAR (DAILY.date) AS year, 
	MONTH(DAILY.date) AS month,
    COUNT(DAILY.hits) AS total
FROM fabcms_wiki_pages PAGES
LEFT JOIN fabcms_stats_daily DAILY
	ON DAILY.IDX = PAGES.ID
LEFT JOIN fabcms_wiki_pages_tags AS TAGS
	ON TAGS.page_ID = PAGES.ID
LEFT JOIN fabcms_wiki_pages_internal_tags AS INTERNALTAGS
	ON INTERNALTAGS.page_ID = PAGES.ID
WHERE DAILY.module = \'wiki\'
    AND DAILY.submodule = \'pageView\'
    ' .  (strlen($tag) > 0 ? ' AND TAGS.tag = \'' . $tag . '\'' : '' ) . '
    ' .  (strlen($internalTag) > 0 ? ' AND INTERNALTAGS.tag = \'' . $internalTag . '\'' : '' ) . '
    AND DAILY.date >= DATE_SUB( NOW(), INTERVAL 36 MONTH)
    AND DAILY.is_bot != 1
GROUP BY YEAR(DAILY.date), MONTH(DAILY.date)';


$db->setQuery($query);

if (!$resultStats = $db->executeQuery('select')){
    echo 'Query error! <pre>' . $query . '</pre>';
    return;
}

if (!$db->numRows){
    echo '<script>alert("No data");</script>';
    return;
} else {
    $statsMax = 0;
    $statsJsData = 'var data = [';

    while ($rowStats = mysqli_fetch_assoc($resultStats)) {
        if ($statsMax < $rowStats['total'])
            $statsMax = $rowStats['total'];

        $statsJsData .= '
                 {
                 label: "' . $rowStats['month'] . '-' . substr( $rowStats['year'],-2) . '",
				 value: ' .  $rowStats['total']  . ',
				 style: "rgba(255, 120, 120, 0.5)"
				 },';
    }

    $statsJsData .= '];
   			  
    var range = {min:0, max: ' . $statsMax . '};
    var CHART_PADDING = 20;
    var wid;
    var hei;
    
    var chartYData = [
                  {label:"' .  $statsMax . '", value:' . $statsMax . '},';

    $statsStep = $statsMax / 10;

    for ($i = 1; $i < 10; $i++ ) {
        $statsValue = floor($statsMax - ($statsStep * $i));
        $statsJsData .= '{label:"' . $statsValue . '", value:' . $statsValue . '},' . PHP_EOL;
    }

    $statsJsData .= '];';
}

echo '
' . $statsJsData . '
';