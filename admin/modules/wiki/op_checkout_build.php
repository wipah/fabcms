<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/02/2017
 * Time: 16:40
 */

if (!$core->adminBootCheck())
    die("Check not passed");

ini_set('max_execution_time', '2400');

$templateCheckout = '
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><!--title--></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
  <div class="container">

    <div class="starter-template">
      <!--body-->
    </div>

  </div><!-- /.container -->

    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>';


if (!isset($_POST['dummy'])){
    echo 'Reload detected';
    return;
}

if (isset($_POST['tags']) && strlen($_POST['tags']) > 1){
    if ($where === true)
        $filterTags = ' AND ';

    $where = true;
    $tagsArray = explode(', ', $_POST['tags']);

    foreach ($tagsArray as $singleTags){
        $filterTags .= 'T.tag = \'' . $core->in($singleTags, true) . '\' OR ';
    }

    $filterTags = substr($filterTags, 0, -3);
}

$query = '
SELECT P.ID,
       P.title,
       P.title_alternative,
       P.trackback,
       P.metadata_description,
       P.creation_date,
       P.last_update,
       P.keywords,
       P.keywords,
       P.internal_redirect,
       P.content,
       P.visible,
       GROUP_CONCAT(DISTINCT T.tag SEPARATOR \', \') AS tag 
FROM ' . $db->prefix . 'wiki_pages AS P
LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
ON P.ID = T.page_ID
LEFT JOIN fabcms_wiki_masters AS M
ON P.master_ID = M.ID
' . (isset($where) ? 'WHERE ' : '') . $filterTags .  '
GROUP BY M.ID
ORDER BY P.title ASC';

$db->setQuery($query);

if (!$buildResult = $db->executeQuery('select')){
    echo '<pre>' . $query . '</pre>';
    return;
}
$buildPages = [];

$body = '<div style="page-break-after: always;"></div>';

$parserUsed = '';



foreach ($fabwiki->parsers as $singleParser){
    $parserUsed .= $singleParser . ', ';
}
$parserUsed = substr($parserUsed, 0, -2);
while ($rowBuild = mysqli_fetch_array($buildResult)){

    $fabwiki->disabledParsers = ['box'];
    $buildPages[$rowBuild['trackback']] = $rowBuild['title'];

    $body .= '
    <a name="' .  $rowBuild['trackback']. '"></a>
        <h1 style="padding:4px; border-bottom: 1px solid black;"">' . $rowBuild['title'] . '</h1>
        Location: <a href="' . $URI->getBaseUri() . '/wiki/' . $rowBuild['trackback'] . '/">' . $URI->getBaseUri() . '/wiki/' . $rowBuild['trackback'] . '/</a>
        <div style="border: 1px dashed #555; background-color: #EAEAEA" class="row">
            
            <div class="col-md-6">
                <strong>Tags</strong>: ' . $rowBuild['tag'] . '<br/>
                <strong>Metadata description</strong>: ' . $rowBuild['metadata_description'] . '<br/>
                <strong>Parser used</strong>: ' . $parserUsed . '
            </div>
            <div class="col-md-6">
                <strong>Words</strong>: ' . str_word_count($rowBuild['content']) . ' <br/>
                <strong>Description chars</strong>: ' . (strlen($rowBuild['metadata_description'])) . '
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                ' . $fabwiki->parseContent($rowBuild['content'], ['underLinkNotExistingPages' => true]) . '      
            </div>
        </div>
        
        <hr/>';
}

$body .= '<div style="page-break-after: always;"></div>
<script>
$(document).find("img").each(function() {
     var $t = $(this);
  $t.attr({
      src: $t.attr(\'data-src\')
    })
    .removeAttr(\'data-src\');
});
</script>
';

foreach ($buildPages AS $trackback => $title){
   $header .= '<a href="#' . $trackback . '">' . $title . '</a><br/>';
}

$templateCheckout = str_replace('<!--body-->', '<p>' . $_POST['titlePage'] . '</p>
                                                               <p style="text-align: right "><em>Generato il ' . date('d-m-Y H:i:s') . '</em></p><p>' . $header . '</p>' . $body, $templateCheckout);
$templateCheckout = str_replace('<!--title-->', 'Checkout generato il ' . date('Y-m-d H:i:s'), $templateCheckout);

$templateCheckout .= '
<script>
$(document).find("img").each(function() {
     var $t = $(this);
  $t.attr({
      src: $t.attr(\'data-src\')
    })
    .removeAttr(\'data-src\');
});
      </script>
      <hr/>
      <strong>Fine checkout</strong>';

if (!is_dir(__DIR__ . '/checkouts/'))
    mkdir(__DIR__ . '/checkouts/');

file_put_contents(__DIR__ . '/checkouts/checkout.html', $templateCheckout);

echo 'Checkout created <a href="' . $URI->getBaseUri(true) . 'admin/modules/wiki/checkouts/checkout.html">here</a>';