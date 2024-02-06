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

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows) {
    echo 'No keywords';
    return;
}

while ($row = mysqli_fetch_assoc($result)) {

    $data = json_decode($row['results'], true);

    $div = '';
    foreach ($data as $single => $value){
        $div .= '<strong>' . $single . '</strong> - ' . $value . ' <br/>';
    }
    echo '
       <div class="row">
            <div class="col-md-9" style="font-size: x-large">' . $row['keyword'] . '</div>
            <div class="col-md-3" style="font-size: xx-large; border-bottom: 1px solid gray; background-color: #444; padding: 8px !important;  color:white">
                <strong>' . $row['score'] . '</strong>
           </div>
       </div>
        
       <div style="margin-top: 24px; border-bottom: 1px solid gray; ">
       '. $div. '
       </div>';
}