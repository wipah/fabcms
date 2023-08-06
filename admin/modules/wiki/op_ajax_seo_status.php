<?php
if (!$core->adminBootCheck())
    die("Check not passed");

if (!isset($core->adminLoaded)) {
    echo 'Direct call';

    return;
}
$this->noTemplateParse = true;
if (!isset($_POST['ID'])) {
    echo 'ID not passed';
    return;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$ID = (int) $_POST['ID'];

$query = 'SELECT *
          FROM ' . $db->prefix  . 'wiki_pages_seo
          WHERE page_ID = ' . $ID;

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->numRows) {
    echo 'No keywords';
    return;
}

while ($row = mysqli_fetch_assoc($result)) {


    $row['results'] = preg_replace('/\-\-(.*)?\r/im', '<div style="border-left: 4px solid #f52039;background-color: #fabbbb; padding: 8px;">$1</div>', $row['results']);
    $row['results'] = preg_replace('/\=\=(.*)?\r/im', '<div style="border-left: 4px solid #ec9414; background-color: #f5c781; padding: 8px;">$1</div>', $row['results']);
    $row['results'] = preg_replace('/\+\+(.*)?\r/im', '<div style="border-left: 4px solid #0AA; background-color: #6cff7e; padding: 8px;">$1</div>', $row['results']);

    echo '
       <div class="row">
        <div class="col-md-9" style="border-bottom: 1px solid gray; font-size: x-large">' . $row['keyword'] . '</div>
        <div class="col-md-3" style="border-bottom: 1px solid gray; background-color: #444; padding: 12px!important; color:white"><strong>Score: ' . $row['score'] . '</strong></div>
       </div>
        
       <div style="margin-top: 24px;">
       '. $row['results'] . '
       </div>';
}