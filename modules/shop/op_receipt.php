<?php
if (!$user->logged)
    die ("None is logged");

if (!isset($path[3]))
    die ("no ID passed");

$this->noTemplateParse = true;

echo '
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>';

$line_ID = (int) $path[3];

$query = 'SELECT I.ID AS line_ID,
                 I.item_ID,
                 I.item_qty,
                 I.public_price,
                 I.final_price,
                 I.net_price,
                 I.vat_amount,
                 I.is_promo,
                 ITEM.is_stackable,
                 ITEM.product_image,
                 ITEM.title,
                 ITEM.trackback,
                 ITEM.cod_art,
                 C.ID AS cart_ID,
                 C.latest_update,
                                  (
                                  SELECT GROUP_CONCAT(CONCAT(UCASE(CUSTOMIZATIONS.NAME), ": ", AI.VALUE) SEPARATOR \', \')
                 FROM ' .  $db->prefix .'shop_cart_items_additional_info AS AI
                 LEFT JOIN ' .  $db->prefix .'shop_items_customizations AS CUSTOMIZATIONS
                    ON CUSTOMIZATIONS.ID = AI.item_additional_ID
                 WHERE AI.cart_item_ID = ' . $line_ID . '
                 ) AS customizations
          FROM ' . $db->prefix . 'shop_cart_items AS I
          LEFT JOIN ' . $db->prefix  .'shop_carts AS C
            ON I.cart_ID = C.ID
          LEFT JOIN ' . $db->prefix . 'shop_items AS ITEM
             ON I.item_ID = ITEM.ID
          WHERE I.ID = ' . $line_ID . ' AND C.user_ID = ' . $user->ID . ';';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    $relog->write(['type'      => '4',
        'module'    => 'SHOP',
        'operation' => 'shop_view_receipt',
        'details'   => 'Query error while displaying receipt. ' . $query,
    ]);

    echo 'Query error while displaying cart. ';
    return;
}

if (!$db->affected_rows){
    echo 'Impossibile trovare la ricevuta. [CA-321]';
    return;
}

echo $fabShop->config['customHeader'];

echo '  <table class="table table-bordered table-striped">
            <thead>
              <tr style="background-color: #5497a6; color: white;">
                <th>Image</th>
                <th>Article</th>
                <th>Codice</th>
                <th>QTY</th>

              </tr>
            </thead>
            <tbody>';

while ($row = mysqli_fetch_assoc($result)){
    $cartID = $row['cart_ID'];

    echo '
            <tr style="max-height: 120px; !important;">
                
                <td>
                    <img style="max-height: 120px"
                        src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $row['product_image'] . '" 
                        class="img-fluid" alt="' . str_replace('"', '\"', $row['title']) . '" />
                </td>
                
                <td>
                    ' . $row['title'] . '<br/>
                    <small><em>' . $row['cod_art'] . '</em></small>
                    <div class="fabCMS-shop-cart-customizations">'. $row['customizations'] .'</div>
                </td>
                
                <td>
                    <img src="' . $URI->getBaseUri() . $this->routed . '/show-qr-image/' . $line_ID . '/" alt="qrcode" /> <br/>' . $core->getDate($row['latest_update']) . '-' . substr(md5($line_ID . $conf['security']['siteKey']),0,4) . '</td>
                <td>' . $row['item_qty'] . '</td>
                
            </tr>';
}

echo '
    </tbody>
</table>';

echo $fabShop->config['customFooter'];
