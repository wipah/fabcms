<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 04/10/2017
 * Time: 15:19
 */

if (!$core->loaded)
    die ("Direct access");

$this->noTemplateParse = true;

if ($user->logged) {
    $cartID = $fabShop->userHasCart($user->ID);

    if ($cartID < 1 || $cartID === false) {
        echo 'Your cart is empty. [L]';
        return;
    }
} else {
    // Check cookies
    if (isset($_COOKIE['cart_ID']) && isset($_COOKIE['cart_secure_hash'])) {
        $cartID = $fabShop->anonymousHasCart();
        $anonymousHash = $core->in($_COOKIE['cart_secure_hash'], true);

        if ($cartID < 1) {
            echo 'Your cart is empty.';
            return;
        }
    }
}

if (!isset($cartID) || empty($cartID)) {
    echo 'Your cart is empty.';
    return;
}
$fabShop->updateCartValues($cartID);

$query = 'SELECT I.ID AS line_ID,
                 I.item_ID,
                 I.item_qty,
                 I.public_price,
                 I.discount_1,
                 I.discount_2,
                 I.discount_3,
                 I.final_price,
                 I.global_discount,
                 I.net_price,
                 I.vat_amount,
                 I.is_promo,
                 I.global_discount,
                 ITEM.is_stackable,
                 ITEM.product_image,
                 ITEM.title,
                 ITEM.trackback,
                 ITEM.cod_art,
                 C.ID AS cart_ID,
                 C.has_global_discount,
                 (
                    SELECT GROUP_CONCAT(CONCAT(UCASE(CUSTOMIZATIONS.NAME), ": ", AI.VALUE) SEPARATOR \', \')
                    FROM ' .  $db->prefix .'shop_cart_items_additional_info AS AI
                    LEFT JOIN ' .  $db->prefix .'shop_items_customizations AS CUSTOMIZATIONS
                    ON CUSTOMIZATIONS.ID = AI.item_additional_ID
                    WHERE AI.cart_item_ID = line_ID
                 ) AS customizations
          FROM ' . $db->prefix . 'shop_cart_items AS I
          LEFT JOIN ' . $db->prefix  .'shop_carts AS C
            ON I.cart_ID = C.ID
          LEFT JOIN ' . $db->prefix . 'shop_items AS ITEM
             ON I.item_ID = ITEM.ID
          WHERE ITEM.enabled = 1 
            AND ' . ($user->logged === true ? 'C.user_ID = ' . $user->ID : 'C.anonymous_hash = \'' . $anonymousHash . '\'') . ' 
            AND C.ID = ' . $cartID;



if (!$result = $db->query($query)){
    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_ajax_view_cart',
                   'details'   => 'Query error while displaying cart. ' . $query,
    ]);
    echo 'Query error while displaying cart. ';
    return;
}

if (!$db->affected_rows){
    echo 'Non hai ancora articoli nel carrello. [CA-123]';
    return;
}

if (!$user->logged) {
    echo '<div class="alert alert-info">
            <strong>Registrazione!</strong>
                <a href="' . $URI->getBaseUri() . 'user/register/">Registrati subito</a> per acquistare tramite paypal®.
          </div>';
}




$totalPrice           = 0;
$totalTax             = 0;
$totalQTY             = 0;
$totalPriceDiscounted = 0;
$tableBody = '';

// Todo: update variables, they are refered to "discount"
while ($row = mysqli_fetch_assoc($result)){
    $cartID = $row['cart_ID'];

    if ( (int) $row['has_global_discount'] === 1 ){
        $globalDiscount = 1;

        $taxable = $row['public_price'];

        if ($row['discount_1'] > 0)
            $taxable = $taxable - ($taxable / 100 ) * $row['discount_1'];

        if ($row['discount_2'] > 0)
            $taxable = $taxable - ($taxable / 100 ) * $row['discount_2'];

        if ($row['discount_3'] > 0)
            $taxable = $taxable - ($taxable / 100 ) * $row['discount_3'];

        $finalPriceDiscounted = $row['item_qty'] *  $taxable;
        $totalPriceDiscounted += $finalPriceDiscounted;
    }

    $totalPrice += $row['final_price'];
    $totalTax   += $row['vat_amount'];
    $totalQTY   += $row['item_qty'];
    $tableBody .= '
            <tr style="max-height: 120px; !important;">
                <td>
                    <img style="max-height: 120px"
                        src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $row['product_image'] . '" 
                        class="img-fluid" alt="' . str_replace('"', '\"', $row['title']) . '" />
                </td>
                <td>
                    ' . $row['title'] . '<br/>
                    <small><em>' . $row['cod_art'] . '</em></small>' . $row['line_ID'] . '
                    <div class="fabCMS-shop-cart-customizations">'. $row['customizations'] .'</div>
                </td>
                <td>
                    ' . ( (int) $row['is_stackable'] === 1 ?
                                                            '<input value="' . $row['item_qty'] . '" type="text" id="qty-' . $row['line_ID'] . '" onchange="changeQTY(' . $row['line_ID'] . ');"/>'
                                                            :
                                                            $row['item_qty'] . ' <br/> <a onclick="changeQTY(' . $row['line_ID'] . ', 0);">Rimuovi</a>'
                        ) . '
                </td>
                
                <td>
                ' . $row['final_price'] . '&euro;
                </td>
                
                ' . ( (int) $row['has_global_discount'] === 1 ? '<td>' . $finalPriceDiscounted .'&euro;</td>' : ''  ) .'
            </tr>';
}

echo '  <table class="table table-bordered table-striped">
            
            <thead>
              <tr style="background-color: #5497a6; color: white;">
                <th></th>
                <th>Articolo</th>
                <th>Quantità</th>
                <th>Prezzo</th>
                ' . ( $globalDiscount === 1 ? '<th>Prezzo da listino</th>' : '') . '
              </tr>
            </thead>
            
            <tbody>' .
            $tableBody .
            ' 
        <tr style="background-color: #d6ffe1;">
            <td></td>
            
            <td>
                <strong>Totali</strong>
            </td>
            
            <td>
                <strong>' . $totalQTY . '</strong>
            </td>
            
            <td>
                <strong>' . $totalPrice . '&euro;</strong>
            </td>     
            
            ' . ( $globalDiscount === 1 ? '<td><strong>' . $totalPriceDiscounted .'</strong>&euro;</td>' : ''  ) .'       
        </tr>
    </tbody>
</table>';


if (!$user->logged) {
    echo '<div class="alert alert-info">
            <strong>Registrazione!</strong>
                <a href="' . $URI->getBaseUri() . 'user/register/">Registrati subito</a> per acquistare tramite paypal®.
          </div>';
} else {

    if ( floatval($totalPrice) == 0 ){
        echo '<div class="float-right alert alert-success">
            <a href="' . $URI->getBaseUri() . '/shop/directcheckout/">
            ' . $language->get('shop', 'ajaxViewCartBuyNowNoPrice', null) . '</div>
            </a>';
    } else {
        if ( (int) $fabShop->config['instantCheckout'] === 1 ){
            echo '<a href="' . $URI->getBaseUri() . $this->routed . '/cart/instant-checkout/">Finalizza ordine</a>';
        }

        echo '
            <div class="float-right">
                
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    
                    <!-- Saved buttons use the "secure click" command -->
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="notify_url" value="' . $URI->getBaseUri() . $this->routed . '/paypal-ipn/">
                    <input type="hidden" name="return" value="' . $URI->getBaseUri() . $this->routed . '/orders/">
                    <input type="hidden" name="currency_code" value="EUR">
                    <input type="hidden" name="business" value="' . $fabShop->config['businessEmail'] . '">
                    <input type="hidden" name="amount" value="' . $totalPrice . '">
                    <input type="hidden" name="custom" value="' . $cartID . '">
                      
                    <input type="hidden" name="item_name" value="Ordine dal sito">
                    <input type="hidden" name="item_number" value="' . $cartID . '">
                      
                    <!-- Saved buttons display an appropriate button image. -->
                    <input type="image" name="submit"
                        src="https://www.paypalobjects.com/it_IT/i/btn/btn_buynow_LG.gif"
                        alt="PayPal - The safer, easier way to pay online">
                      
                    <img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" >
                
                </form>
                
            </div>';
}
}