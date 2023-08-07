<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 08/03/2018
 * Time: 12:45
 */

$keyword = $core->in($_POST['fabShopSearch'], true);

$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_items
          WHERE title LIKE \'%' . $keyword . '%\'
          AND enabled = 1';



echo '<div class="row">
        <div class="col-md-8">
             <h1>Shop - Risultato ricerca</h1>      
        </div>
        
        <div class="col-sm-4">
            <div class="fabCMS-Shop-QuickCart" id="quickCart"></div>
        </div>
    </div>';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;

    return;
}

if (!$db->affected_rows) {
    echo 'No rows';

    return;
}
$i = 0;
$rowOpen = true;

while ($row = mysqli_fetch_assoc($result)) {
    if ($i === 0) {
        echo '<div class="row">';
        $rowOpen = true;
    }
    $i++;
    echo '
            <div class="col-md-3 fabcms-shop-defaultArticleBlock">
                
                <a href="' . $URI->getBaseUri() . $this->routed . '/item/' . $row['ID'] . '-' . $row['trackback'] . '/"> 
                    <img class="img-fluid img-thumbnail" style="max-width: 100%;" src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $row['product_image'] . '" alt="' . $row['title'] . '" >
                </a>
                
                <div class="fabcms-shop-defaultArticleBlockArticleDescription">
                    <a href="' . $URI->getBaseUri() . $this->routed . '/item/' . $row['ID'] . '-' . $row['trackback'] . '/">' . $row['title'] . '</a>
                </div>
            </div>';

    if ($i === 4) {
        $i = 0;
        echo '</div> <!-- closing row-->';
        $rowOpen = false;
    }
}

if ($rowOpen)
    echo '</div>';