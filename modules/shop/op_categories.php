<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 29/11/2017
 * Time: 12:10
 */

if (!$core->loaded)
    die ("Direct call");

if (preg_match('#([0-9]{1,3})-([a-z0-9\D]{1,120})#i', $path[3], $matches)) {
    $category_ID = (int)$matches[1];
    $category_trackback = (string)$matches[2];
} else {
    echo 'Nessun articolo passato';
    return;
}


// check if category exists
$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_categories 
          WHERE ID = ' . $category_ID . '
            AND trackback = \'' . $category_trackback . '\'
            AND enabled = 1
            AND lang = \'' . $core->shortCodeLang . '\'
          LIMIT 1;';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {

    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_categories_search_query_error',
                   'details'   => 'Query error while searching for categories. ' . $query,
    ]);

    echo 'Query error. ';
    return;
}

if (!$db->affected_rows) {
    echo 'La categoria richiesta non &egrave; disponibile';
    return;
}

$row = mysqli_fetch_assoc($result);

$template->navBarAddItem('Shop',$URI->getBaseUri() . $this->routed .'/');
$template->navBarAddItem($row['title']);
$this->addTitleTag($row['title']);

echo '
<div class="row">
        <div class="col-md-6">
             <h1>' . $row['title'] . '</h1>       
        </div>
        
        <div class="col-sm-3">
            <div class="fabCMS-Shop-QuickCart" id="quickCart"></div>
        </div>
        
        <div class="col-sm-3">
        <form method="post" action="' . $URI->getBaseUri() . $this->routed . '/search/">
            <input type="text" name="fabShopSearch" id="fabShopSearch">
            <button class="btn btn-secondary" type="submit" value="Search">Cerca articoli</button>
        </form>
    </div>
</div>';


/*
 * ITEMS
 */

$query = 'SELECT I.* 
          FROM ' . $db->prefix . 'shop_items AS I 
          WHERE lang = \'' . $core->shortCodeLang . '\' 
            AND enabled = 1
            AND category_ID = ' . $category_ID . '
          ORDER BY ID DESC';
$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_categories_show_items',
                   'details'   => 'Unable to select items from categories. Query error. ' . $query,
    ]);

    echo 'Unable to view categories.';

    return;
}

if (!$db->affected_rows) {
    echo 'No items are present.';
} else {

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

}
