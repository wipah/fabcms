<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/09/2017
 * Time: 11:48
 */
if (!$core->loaded)
    die ("Not loaded directly");

$fabShop->updateCartStatus();

$template->sidebar .= $template->simpleBlock('On cart', '<div id="quickCart"></div>');
$theScript = '
$.post( "' . $URI->getBaseUri() . $this->routed . '/quick-cart/", {})
  .done(function( data ) {
    $("#quickCart").html(data) ;
  });
';

$this->addTitleTag( $core->getConfig('shop','shopTitle') );


echo '
<div class="row">
    <div class="col-sm-6">
        <h1>' . $fabShop->config['shopName'] . '</h1>
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

$template->navBarAddItem('Shop', $URI->getBaseUri() . $this->routed . '/');

echo $fabShop->config['homepage_' . $core->shortCodeLang];

$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_categories 
          WHERE enabled = 1
            AND lang    = \'' . $core->shortCodeLang . '\';';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error while selecting categories';

    return;
}

echo '<h2>Categorie</h2>';

$i = 0;
$rowOpen = true;

while ($row = mysqli_fetch_assoc($result)) {
    if ($i === 0) {
        echo '<div class="row">';
        $rowOpen = true;
    }

    $i++;
    echo '<div class="col-md-4" style="background-color: #bbdeff; padding:12px; border: 12px solid white;">
            <a href="' . $URI->getBaseUri() . $this->routed . '/category/' . $row['ID'] . '-' . $row['trackback'] . '/">
                <strong>' . $row['title'] . '</strong> <br /> ' . $row['description'] . '
            </a>
          </div>';

    if ($i === 3) {
        $i = 0;
        echo '</div> <!-- closing row-->';
        $rowOpen = false;
    }
}

if ($rowOpen)
    echo '</div>';

/*
 * ITEMS
 */
echo '<h2>Ultimi articoli</h2>';

$query = 'SELECT I.* 
          FROM ' . $db->prefix . 'shop_items AS I 
          WHERE lang    = \'' . $core->shortCodeLang . '\' 
            AND enabled = 1
          LIMIT 12';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error.';
    return;
}

echo '<div class="container fabCMS-shop-articleList">';

if (!$db->affected_rows) {
    echo 'Nessun articolo al momento presente.';
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
                    <a href="' . $URI->getBaseUri() . $this->routed . '/item/' . $row['ID'] . '-' . $row['trackback'] . '/">' . $row['short_description'] . '</a>
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
echo '</div>';

$theScript = '
$.post( "' . $URI->getBaseUri() . $this->routed . '/quick-cart/", {})
  .done(function( data ) {
    $("#quickCart").html(data) ;
  });
';

$this->addScript($theScript);