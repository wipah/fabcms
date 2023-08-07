<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 01/08/2017
 * Time: 16:06
 */

if ( !$core->loaded ) {
    die("Direct load");
}


if ( preg_match('#([0-9]{1,3})-([a-z0-9\D]{1,120})#i', $path[3], $matches) ) {
    $article_ID         =   (int) $matches[1];
    $article_trackback  =   (string) $matches[2];
} else {
    echo 'No item passed';
    return;
}

$template->sidebar .= $template->simpleBlock('Ricerca', '<form method="post" action="' . $URI->getBaseUri() . $this->routed . '/search/">
                                                    <input type="text" name="fabShopSearch" id="fabShopSearch">
                                                    <input type="submit" value="Search">
                                                </form>');

$template->sidebar .= $template->simpleBlock('On cart', '<div id="quickCart"></div>');
$theScript = '
$.post( "' . $URI->getBaseUri() . $this->routed . '/quick-cart/", {})
  .done(function( data ) {
    $("#quickCart").html(data) ;
  });
';
$template->sidebar .= ' <!--FabCMS-hook:shopCommonFirstSidebar-->';

$this->addScript($theScript);

if (!$user->logged) {
    $template->sidebar .= $template->simpleBlock('Registrati', '<strong><a href="' . $URI->getBaseUri() . 'user/register/">Registrati adesso</a></strong> per effettuare gli ordini');
} else {
    $template->sidebar .= $template->simpleBlock('Ultimi ordini', '<strong><a href="' . $URI->getBaseUri() . $this->routed . '/orders/">Visualizza i tuoi ordini</a></strong> e procedi al download.');
}


$articleData = $fabShop->getItemDataByIDTrackback($article_ID, $article_trackback);
$filesData = $fabShop->getFilesByArticleID($article_ID, 0);

if ( (int) $fabShop->config['globalDiscount'] > 0)
{
    $discountedFinalPrice = $articleData['final_price'] - ($articleData['final_price'] / 100) * $fabShop->config['globalDiscount'];
    $discountedVatAmount  = $discountedFinalPrice - ($discountedFinalPrice / 100 ) * $articleData['vat_value'];
    $discountedNetValue   = $discountedFinalPrice - $discountedVatAmount;
}

if ($filesData === -1 || $filesData === -2 )
{
    $fileList = '';
} else {
    $fileList = '<h3>Download media</h3>';

    foreach ($filesData as $theFile => $theName)
    {
        $fileList .= '&bull; <a href="' . $URI->getBaseUri() . $this->routed . '/download/?file=' . $theFile . '">' . $theName. '</a><br/>';
    }
}

$template->navBarAddItem('Shop',$URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem($articleData['title']);

$this->addTitleTag($articleData['title']);

if ($articleData == -1 ){
    echo 'Errore durante la visualizzazione dell\'articolo.';
    return;
}

if ($articleData == -2){
    header("HTTP/1.0 404 Not Found");
    echo '<!--FabCMS-hook:shop-itemNotFound-->Articolo non trovato.';
    return;
}

// Check if the article has customizations
$query = 'SELECT C.* 
          FROM ' . $db->prefix  .'shop_items_customizations AS C 
          LEFT JOIN ' . $db->prefix . 'shop_items_customizations_link AS L
          ON L.customization_ID = C.ID
          WHERE L.item_ID = ' . $article_ID . ' 
            AND C.lang = \'' . $core->shortCodeLang . '\'
          ORDER BY L.order DESC';


if (!$resultCustomization = $db->query($query))
{
    echo 'Query error while selecting customizations.' . $query;
    return;
}

if (!$db->affected_rows)
{
    $log->write('info', 'shop', 'No customizations were found');
} else {
    $log->write('info', 'shop', 'Customizations found. Number ' . $db->affected_rows);

    $itemCustomization = '';
    $mandatoryIDS      = [];
    $postCustomPass    = '';

    while ($rowCustomization = mysqli_fetch_assoc($resultCustomization)){
        switch ( (int) $rowCustomization['type']){
            case 1: // textbox
                $postCustomPass .= 'custom_' . $rowCustomization['ID'] . ' : $("#custom_' . $rowCustomization['ID'] . '").val(), ';

                if ( (int) $rowCustomization['is_mandatory'] == 1 )
                    $mandatoryIDS[] = 'custom_' . $rowCustomization['ID'];

                $itemCustomization .= '<div class="row">
                                        <div class="col-md-4">' . $rowCustomization['name'] . '</div>
                                        <div class="col-md-8">
                                            <input type =   "text" 
                                                   name =   "custom_' . $rowCustomization['ID'] . '" 
                                                   type =   "text" 
                                                   id   =   "custom_' . $rowCustomization['ID'] . '" />
                                        </div>
                                      </div>';
                break;

            case 2: // Yes or no
                $postCustomPass .= 'custom_' . $rowCustomization['ID'] . ' : $("#custom_' . $rowCustomization['ID'] . '").is(\':checked\') === true ? 1 : 0, ';
                $itemCustomization .= '<div class="row">
                                        <div class="col-md-4">' . $rowCustomization['name'] . '</div>
                                        <div class="col-md-8">
                                            <input type     =   "checkbox"
                                                   value    =   "checked" 
                                                   name     =   "custom_' . $rowCustomization['ID'] . '" 
                                                   type     =   "text" 
                                                   id       =   "custom_' . $rowCustomization['ID'] . '" />
                                        </div>
                                      </div>';
                break;

            case 3:
                // Find any options
                $query = 'SELECT * 
                          FROM ' . $db->prefix . 'shop_items_customizations_options 
                          WHERE customization_ID = ' . $rowCustomization['ID'];

                
                if (!$resultCustomizationOptions = $db->query($query)){
                    echo 'Query error. ' . $query;
                    return;
                }

                if (!$db->affected_rows) {
                    echo 'No options found.';
                    return;
                }

                $customOptions = '';
                while ($rowOptions = mysqli_fetch_assoc($resultCustomizationOptions)){
                    $customOptions .= '<option id="' . $rowOptions['ID'] . '">' . $rowOptions['value'] . '</option>';
                }

                $postCustomPass .= 'custom_' . $rowCustomization['ID'] . ' : $("#custom_' . $rowCustomization['ID'] . '").val(), ';

                $itemCustomization .= '<div class="row">
                                        <div class="col-md-4">' . $rowCustomization['name'] . '</div>
                                        <div class="col-md-8">
                                            <select id="custom_' . $rowCustomization['ID'] . '" name="custom_' . $rowCustomization['ID'] . '">
                                            ' . $customOptions . '
                                            </select>
                                        </div>
                                      </div>';
        }
    }
}

// Tags
$tags = $articleData['tags'];
if (!empty($tags) && strlen($tags) > 1) {
    $tagsArray = explode(', ', $tags);

    $queryTags = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_items 
                  WHERE enabled = 1
                        AND ID != ' . $article_ID . '
                        AND lang = \'' . $core->shortCodeLang . '\'
                        AND (
                  ';
    foreach ($tagsArray as $singleTag) {
        $queryTags .= 'tags LIKE \'%' . $core->in($singleTag) . '%\' OR ';
    }

    $queryTags = substr($queryTags, 0, -3) . ' ) LIMIT 4';
    
    if (!$resultSimilar = $db->query($queryTags)) {
        echo 'Query error' . $queryTags;
    } else {
        if (!$db->affected_rows) {
            $similar = 'Nessun prodotto simile';
        } else {
            $similar = '';
            while ($rowSimilar = mysqli_fetch_assoc($resultSimilar)) {

                $itemURI = $URI->getBaseUri() . $this->routed . '/item/' . $rowSimilar['ID'] . '-' . $rowSimilar['trackback'] . '/';

                $similar .= '
                    <div class="col-md-3">
                        <div class="fabShopItemPhoto">
                            <a href="' . $itemURI . '">
                                <img class="img-fluid img-thumbnail" src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $rowSimilar['product_image'] . '" alt="' . $rowSimilar['title'] . '" >
                        </div>
                        <a href="' . $itemURI . '">' . $rowSimilar['title'] . '</a>
                    </div>';
            }
        }
    }
}

echo '
<style type="text/css">
    .fabShopItemPhoto{
        padding: 24px;
        border: 2px solid #CCC;
    }
    
    .fabShopItemBody{
        margin-top: 12px;
        border-bottom: 2px solid #CCC;
    }
    
    .fabShopItemPricing{
        padding: 6px;
        font-size: 20px;
        border-bottom: 1px solid #5F5;
        border-top: 1px solid #5f5;
        background-color: #CFC;
        margin-bottom: 24px;
    }
    
    .fabShopRelatedProducts{
        margin-top: 24px;
    }
</style>
    <div class="row">
        <div class="col-md-6">
             <h1>' . $articleData['title'] . '</h1>       
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
    </div>
 

<div class="row mt-4">
    
    <div class="col-md-4">
        <div class="fabShopItemPhoto">
            <img class="img-fluid img-thumbnail" src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $articleData['product_image'] . '" alt="' . $articleData['title'] . '" >
        </div>
        
        <div class="fabShopItemShortDescription">
            ' . $articleData['short_description'] . '
        </div>
    </div>
    
    <div class="col-md-8">
        
        <div class="fabShopItemPricing">
            ' . ($articleData['taxable'] !== $articleData['public_price']
        ? '<del>' . $articleData['public_price'] . ' &euro;</del> - ' . $articleData['final_price']
        : $articleData['final_price']) . '&euro;
            ' . ( (int) $fabShop->config['useVAT'] === 1 ? '<br/> <small>Include ' . $articleData['vat_amount'] . ' &euro; di tasse</small>' : '') . '
        </div>
        ' . ( (int) $fabShop->config['globalDiscount'] > 0 ? 'Prezzo al rivenditore: ' . $discountedFinalPrice  : '' ). '        

        ' . $itemCustomization. '
        <button id="buttonAddToCart" class="button btn-info btn-lg fabCMS-button-addToCart" onclick="addToCart(' . $articleData['ID'] . ');">Compra adesso!</button>
        <div id="cartBuyNowStatus"></div>

        <div class="fabShopItemBody">
            ' . $articleData['description'] . '
        </div>        
    </div>
    
</div>

<div class="fabShopFileDownload">
    ' . $fileList . '
</div>

<div class="fabShopRelatedProducts">
    <h3>Prodotti simili</h3>
' . $similar . '
</div>';

$theScript = /** @lang javascript */
    '
function addToCart(itemID) {
    $("#buttonAddToCart").prop("disabled", true);
    $("#buttonAddToCart").text("Inserimento in corso...");
    
    $.post( "' . $URI->getBaseUri() . $this->routed . '/ajax-add-to-cart/", { item_ID: itemID, ' . $postCustomPass . ' })
      .done(function( data ) {
          console.log(data);
          $.post( "' . $URI->getBaseUri() . $this->routed . '/quick-cart/", {})
            .done(function( data ) {
                
                $("#buttonAddToCart").removeProp("disabled");
                
                $("#buttonAddToCart").text("Compra adesso!");
                $("#quickCart").html(data) ;
                $("#cartBuyNowStatus").html("<span style=\'color:green\'>Articolo inserito nel carrello</span>") ;
          });
          
      });

}';

$this->addScript($theScript);