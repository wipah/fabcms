<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 22/03/2017
 * Time: 11:01
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if (isset($_GET['create']) ){
    $this->noTemplateParse = true;
    $tag = $core->in($_POST['tag']);

    if (empty($tag)){
        echo 'Tag was empty';
        return;
    }

    $query = '
    SELECT P.`language`,P.trackback
    FROM ' . $db->prefix . 'wiki_pages AS P
    LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
    ON P.ID = T.page_ID
    WHERE T.tag = \'' . $tag .'\';';

    if (!$result = $db->query($query)){
        echo '<pre>' . $query . '</pre>';
        return;
    }

    $theBuffer = '';
    while ($row = mysqli_fetch_assoc($result)){
        $theBuffer .= 'pageUrl==' . urlencode($URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/') . ',';
    }

    $theBuffer = substr($theBuffer, 0, -1);

    echo $theBuffer;

    return;
}

echo '
<h2>Piwik tag builder</h2>
Tag:<input type="tag" id="tag"> <button onclick="buildTag();" type="button">Build</button> 
<pre id="piwikiResult"></pre>          
          
<script type="text/javascript">
function buildTag(){
    $.post( "admin.php?module=wiki&op=piwik_tag_creator&create", { tag: $("#tag").val() }) 
        .done(function( data ) {
         $("#piwikiResult").html(data);
    });    
}

</script>';