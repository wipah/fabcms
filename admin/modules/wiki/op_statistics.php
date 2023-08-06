<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 29/01/2017
 * Time: 23:46
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Statistics', 'admin.php?module=wiki&op=statistics');

echo '
<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Optimizer</a>
    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-analytics" role="tab" aria-controls="nav-profile" aria-selected="false">Analytics</a>
  </div>
</nav>

<div class="tab-content" id="nav-tabContent">
  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
    <h2>Stats</h2>';

$query = '
SELECT WC.name,  COUNT(WP.ID) AS total, SUM(WPS.words) AS total_words
FROM ' . $db->prefix . 'wiki_pages AS WP
LEFT JOIN ' . $db->prefix . 'wiki_categories_details AS WC
	ON WC.ID = WP.category_ID
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS WPS
ON WPS.page_ID = WP.ID 
WHERE WP.visible = 1
AND WP.service_page != 1
AND WP.internal_redirect = \'\'
GROUP BY WC.ID
';
$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error: ' . $query;

    return;
}

if (!$db->numRows) {
    echo 'No data';
}

echo '
<h2>Categories distribution</h2>
<table id="tablesortableCategoriesDistribution" class="table table-bordered table-striped">
<thead>
    <tr>
        <th>Category</th>
        <th>Pages</th>
        <th>Words</th>
    </tr>
</thead>
<tbody>
';

$totalPages = 0;
$totalWords = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $totalPages += (int) $row['total'];
    $totalWords += (int) $row['total_words'];
    echo '<tr>
            <td>' . $row['name'] . '</td>
            <td>' . $row['total'] . '</td>
            <td>' . $row['total_words'] . '</td>
          </tr>';
}

echo '
    <tfoot>
        <tr>
            <td>&nbsp;</td>
            <td>' . $totalPages . '</td>
            <td>' . $totalWords . '</td>
        </tr>
    </tfoot>
    </tbody>
</table>';


$query = '
SELECT 
       COUNT(WP.ID) AS total, 
       SUM(WPS.words) AS total_words,
(
    SELECT WPT.tag
    FROM ' . $db->prefix . 'wiki_pages_tags WPT
    WHERE WPT.page_ID = WP.ID
    ORDER BY WPT.ID ASC
    LIMIT 1
) AS tag
FROM ' . $db->prefix . 'wiki_pages AS WP
LEFT JOIN ' . $db->prefix . 'wiki_categories_details AS WC
	ON WC.ID = WP.category_ID
LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS WPS
    ON WPS.page_ID = WP.ID 

WHERE   WP.visible = 1
AND     WP.service_page != 1
AND     WP.internal_redirect  = \'\'
GROUP BY tag
ORDER BY total DESC';

echo '
<h2>Tag distribution</h2>
<table id="tablesortableTagDistribution" class="table table-bordered table-striped">
<thead>
    <tr>
        <th>Tag</th>
        <th>Pages</th>
        <th>Words</th>
    </tr>
</thead>
<tbody>
';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error: ' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No data';
}


while ($row = mysqli_fetch_assoc($result)) {

    echo '<tr>
            <td>' . $row['tag'] . '</td>
            <td>' . $row['total'] . '</td>
            <td>' . $row['total_words'] . '</td>
          </tr>';
}

echo '
    </tbody>
</table>';

$query = '
SELECT P.ID,
       P.language, 
       P.title, 
       P.title_alternative, 
       P.trackback, 
       (CHAR_LENGTH(P.content)) AS CHARS
FROM ' . $db->prefix . 'wiki_pages AS P
ORDER BY CHARS DESC
LIMIT 100';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error: ' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No data';
    return;
}

echo '
<h2>Top pages by lenght</h2>
<table id="tablesortableLenght" class="table table-bordered table-striped">
<thead>
    <tr>
        <th>Language</th>
        <th>Title</th>
        <th>Lenght</th>
        <th>Operations</th>
    </tr>
</thead>
<tbody>
';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>
            <td>' . $row['language'] . '</td>
            <td>' . $row['title'] . (!empty($row['title_alternative']) ? '<em> - ' . $row['title_alternative'] . '</em>' : '') . '</td>
            <td>' . $row['CHARS'] . ' chars</td>
            <td> <a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">Edit</a> - <a href="' . $URI->getBaseUri() . '/wiki/' . $row['trackback'] . '/">View</a></td>
          </tr>';
}

echo '</tbody>
</table>';

echo '
<h2>Top pages by lenght</h2>
<table id="tablesortableLenght" class="table table-bordered table-striped">
<thead>
    <tr>
        <th>Language</th>
        <th>Title</th>
        <th>Lenght</th>
        <th>Operations</th>
    </tr>
</thead>
<tbody>
';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>
            <td>' . $row['language'] . '</td>
            <td>' . $row['title'] . (!empty($row['title_alternative']) ? '<em> - ' . $row['title_alternative'] . '</em>' : '') . '</td>
            <td>' . $row['CHARS'] . ' chars</td>
            <td> <a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">Edit</a> - <a href="' . $URI->getBaseUri() . '/wiki/' . $row['trackback'] . '/">View</a></td>
          </tr>';
}

echo '</tbody>
</table>';

$query = 'SELECT  count(P.ID) as total_pages,
		  MONTH(P.creation_date) AS month,
          YEAR(P.creation_date) AS year,
          SUM(LENGTH(P.content) - LENGTH(REPLACE(P.content, \' \', \'\'))+1) AS words,
	      SUM(CHARACTER_LENGTH(P.content)) as characters,
	      SUM(LENGTH(P.content)) as bytes
FROM ' . $db->prefix . 'wiki_pages AS P
GROUP BY year, month 
ORDER BY YEAR DESC, MONTH DESC;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No data';
}

echo '
<h2>Pages by month/year</h2>
<table id="tablesortable" class="table table-bordered table-striped">
<thead>
    <tr>
        <th>Year</th>
        <th>Month</th>
        <th>Pages</th>
        <th>Words</th>
        <th>Bytes</th>
    </tr>
</thead>
<tbody>
';

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>
            <td>' . $row['year'] . '</td>
            <td>' . $row['month'] . '</td>
            <td>' . $row['total_pages'] . '</td>
            <td>' . $row['words'] . '</td>
            <td>' . makeSize($row['bytes']) . '</td>
          </tr>';
}

echo '</tbody>
</table>';

echo '<h2>Linked page</h2>';

$query = '
SELECT COUNT(T.trackback_page_ID) AS total, 
       T.trackback_page_ID 
FROM ' . $db->prefix . 'wiki_outbound_trackback AS T 
WHERE NOT (SELECT P.trackback FROM ' . $db->prefix . 'wiki_pages AS P WHERE T.trackback_page_ID = P.trackback) 
GROUP BY T.trackback_page_ID ORDER BY total DESC';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    $unlinkedItems = 'Query error. ' . $query;
} else {
    if (!$db->numRows) {
        $unlinkedItems = 'No items!';
    } else {
        echo '
            <table id="tablesortableLinked" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Trackback</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . $row['trackback_page_ID'] . '</td>
                    <td>' . $row['total'] . '</td>
                    
                  </tr>';
        }
    }
}

echo '</tbody>
</table>';

echo '<h2>Unlinked page</h2>';

$query = '
SELECT COUNT(T.trackback_page_ID) AS total, 
       T.trackback_page_ID 
       FROM ' . $db->prefix . 'wiki_outbound_trackback AS T 
       WHERE NOT EXISTS (SELECT P.trackback FROM ' . $db->prefix . 'wiki_pages AS P WHERE T.trackback_page_ID = P.trackback) 
       GROUP BY T.trackback_page_ID ORDER BY total DESC
';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    $unlinkedItems = 'Query error. ' . $query;
} else {
    if (!$db->numRows) {
        $unlinkedItems = 'No items!';
    } else {
        echo '
            <table id="tablesortableUnlinked" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Trackback</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . $row['trackback_page_ID'] . '</td>
                    <td>' . $row['total'] . '</td>
                  </tr>';
        }
    }
}

echo '    </tbody>
        </table>
    </div>

    <div class="tab-pane fade" id="nav-analytics" role="tabpanel" aria-labelledby="nav-contact-tab">
        <div class="FabCMS-filterBox">
            <div class="row">
                <h2>Tag analytics</h2>
                
                <div class="col-md">
                
                    <div class="form-group">
                        <span class="FabCMS-filterValue">Tag</span>
                        <input type="text" class="form-control" id="analyticsTag">
                    </div>
                    
                </div>
                
                <div class="col-md">
                
                    <div class="form-group">
                        <span class="FabCMS-filterValue">
                            Internal tag
                        </span>
                        <input type="text" class="form-control" id="analyticsInternalTag">
                    
                    </div>
                </div>
                
                <div class="col-md">
                    <span class="FabCMS-filterValue">
                        Operations
                    </span>
                <button class="button form-control" onclick="tagAnalyticsSearch();">Search</button>
                </div>
            </div>
        </div>
        <div id="tagAnalyticsResult">
                    <canvas id="pageStat" width="800px" height="500px"></canvas>
                </div>
    </div>
</div>

<script type="text/javascript">
    function tagAnalyticsSearch() {
        tag         = $("#analyticsTag").val();
        internalTag = $("#analyticsInternalTag").val();
        
        $.post( "admin.php?module=wiki&op=tagAnalyticsSearch", {tag:  tag, internalTag: internalTag})
        .done(function( data ) {
            console.log("Called tagAnalyticsSearch");
            script = data.replace(/<script>(.*)<\/script>/, "$1"); // Remove tags
            $.globalEval(data); 
            initStats();
        }  
    );
}

function initStats(){
	
	var can = document.getElementById("pageStat");
	
	wid = can.width;
	hei = can.height;
	var context = can.getContext("2d");
	context.fillStyle = "#eeeeee";
	context.strokeStyle = "#999999";
	context.fillRect(0,0,wid,hei);
	
	context.font = "8pt Arial-narrow, sans-serif";
	context.fillStyle = "#999999";
	
	context.moveTo(CHART_PADDING,CHART_PADDING);
	context.lineTo(CHART_PADDING,hei-CHART_PADDING);
	context.lineTo(wid-CHART_PADDING,hei-CHART_PADDING);
	
	fillChart(context,chartYData);
	createBars(context,data);
	
}

function fillChart(context, stepsData){ 
	var steps = stepsData.length;
	var startY = CHART_PADDING;
	var endY = hei-CHART_PADDING;
	var chartHeight = endY-startY;
	var currentY;
	var rangeLength = range.max-range.min;
	for(var i=0; i<steps; i++){
		currentY = startY + (1-(stepsData[i].value/rangeLength)) *	chartHeight;
		context.moveTo(CHART_PADDING, currentY );
		context.lineTo(CHART_PADDING*1.3,currentY);
		context.fillText(stepsData[i].label, CHART_PADDING*1.5, currentY+6);
	}
	context.stroke();
}

function createBars(context,data){ 
	var elementWidth =(wid-CHART_PADDING*2)/ data.length;
	var startY = CHART_PADDING;
	var endY = hei-CHART_PADDING;
	var chartHeight = endY-startY;
	var rangeLength = range.max-range.min;
	var stepSize = chartHeight/rangeLength;
	context.textAlign = "center";
	for(i=0; i<data.length; i++){
		context.fillStyle = data[i].style;
		context.fillRect(CHART_PADDING + elementWidth*i ,hei-CHART_PADDING - data[i].value*stepSize, elementWidth, data[i].value*stepSize);
		context.fillStyle = "rgba(50, 50, 50, 0.9)";
		context.fillText(data[i].label, CHART_PADDING +elementWidth*(i+.5), hei-CHART_PADDING*1.5);
	}
		
}

function drawBorder(xPos, yPos, width, height, thickness = 2)
{
  ctx.fillStyle=\'#000\';
  ctx.fillRect(xPos - (thickness), yPos - (thickness), width + (thickness * 2), height + (thickness * 2));
}


$("#tablesortable, #tablesortableLenght, #tablesortableUnlinked, #tablesortableLinked, #tablesortableTagDistribution").DataTable({
         "order": [[ 0, "desc" ]]
       });
</script>';

function makeSize($size, $type = 1)
{
    if ($type == 1) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $interval = 1024;
    } else {
        $units = ['', 'K', 'M', 'G', 'T'];
        $interval = 1000;
    }

    $u = 0;
    while ((round($size / $interval) > 0) && ($u < 4)) {
        $size = $size / $interval;
        $u++;
    }

    return (number_format($size, 0) . " " . $units[$u]);

}