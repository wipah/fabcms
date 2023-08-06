<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/11/2017
 * Time: 15:40
 */

if (!$core->loaded)
    die("Direct access");

$template->navBarAddItem('Shop', $URI->getBaseUri() . $this->routed);
$template->navBarAddItem('Visualizzazione ordine');

if (!$user->logged) {
    echo '<div class="alert alert-info">
            <strong>Login!</strong> Effettua il login per visualizzare gli ordini.
          </div>';
}

if (isset($path[3])) {
    if (!$user->logged) {
        echo '<div class="alert alert-warning">
                <strong>Errore!</strong> ID ordine non passato.
              </div>';
    }
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
$this->addScript($theScript);
$template->sidebar .= ' <!--FabCMS-hook:shopCommonFirstSidebar-->';

if (!$user->logged) {
    $template->sidebar .= $template->simpleBlock('Registrati', '<strong><a href="' . $URI->getBaseUri() . 'user/register/">Registrati adesso</a></strong> per effettuare gli ordini');
} else {
    $template->sidebar .= $template->simpleBlock('Ultimi ordini', '<strong><a href="' . $URI->getBaseUri() . $this->routed . '/orders/">Visualizza i tuoi ordini</a></strong> e procedi al download.');
}


$ID = (int)$path[3];

$query = 'SELECT
  I.*,
  ITEM.product_image,
  ITEM.title,
  ITEM.cod_art,
  GROUP_CONCAT(FILES.file SEPARATOR \'||\') AS file_list,                                (
                 SELECT GROUP_CONCAT(CONCAT(UCASE(CUSTOMIZATIONS.NAME), ": ", AI.VALUE) SEPARATOR \', \')
                 FROM ' .  $db->prefix .'shop_cart_items_additional_info AS AI
                 LEFT JOIN ' .  $db->prefix .'shop_items_customizations AS CUSTOMIZATIONS
                    ON CUSTOMIZATIONS.ID = AI.item_additional_ID
                 WHERE AI.cart_item_ID = ITEM.ID
                 ) AS customizations
  
FROM ' . $db->prefix . 'shop_cart_items AS I
  LEFT JOIN ' . $db->prefix . 'shop_carts AS C
    ON I.cart_ID = C.ID
  LEFT JOIN ' . $db->prefix . 'shop_items AS ITEM
    ON I.item_ID = ITEM.ID
  LEFT JOIN ' . $db->prefix . 'shop_item_files AS FILES
    ON FILES.item_ID = I.item_ID
WHERE C.ID = ' . $ID . '
AND C.status = 1
AND C.user_ID = ' . $user->ID . ';';


$query =
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
                 
                 (
                    SELECT GROUP_CONCAT(FILES.file SEPARATOR \'||\')  AS file_list
                    FROM ' .  $db->prefix .'shop_item_files AS FILES
                    WHERE FILES.item_ID = I.item_ID
                    AND FILES.enabled = 1
                 ) AS file_list,
       
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
             
          
          WHERE C.ID = ' . $ID . '
            AND C.status = 1
            AND C.user_ID = ' . $user->ID . ';';
$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {

    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_order_show_orders',
                   'details'   => 'Unable to show orders. Query error. ' . $query,
    ]);

    echo '<div class="alert alert-warning">
            <strong>Errore!</strong> Errore nella query.
           </div>';

    return;
}

if (!$db->numRows) {
    echo '<div class="alert alert-warning">
            <strong>Errore!</strong> Nessun articolo passato.
           </div>';
}

echo '  <table class="table table-bordered table-striped">
            <thead>
              <tr style="background-color: #5497a6; color: white;">
                <th>Immagine</th>
                <th>Articolo</th>
                <th>Quantità</th>
                <th>Totale (listino)</th>
                <th>Totale</th>
                <th>Download</th>
                <th>Operazioni</th>
              </tr>
            </thead>
            <tbody>';

$totalPrice      = 0;
$totalTax        = 0;
$totalQTY        = 0;
$totalDiscounted = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $operations = '';
    $downloads  = '';

    $totalPrice += $row['final_price'];
    $totalTax   += $row['vat_amount'];
    $totalQTY   += $row['item_qty'];

    if ($row['global_discount'] > 0) {

        $taxable = $row['public_price'];

        if ($row['discount_1'] > 0)
            $taxable = $taxable - ($taxable / 100) * $row['discount_1'];

        if ($row['discount_2'] > 0)
            $taxable = $taxable - ($taxable / 100) * $row['discount_2'];

        if ($row['discount_3'] > 0)
            $taxable = $taxable - ($taxable / 100) * $row['discount_2'];

        //$taxable = $taxable - ($taxable / 100 ) * $row['global_discount'];

        $lineDiscounted = $taxable;

        $totalDiscounted += $lineDiscounted;
    } else {
        $totalDiscounted += $row['final_price'];

    }

    // Check if a file exists
    if (!empty($row['file_list'])) {
        $fileArray = explode('||', $row['file_list']);

        foreach ($fileArray as $singleFile) {
            $hash = md5($singleFile . date('YmdG') . $conf['security']['siteKey']);

            $downloads .= '&bull; <a href="' . $URI->getBaseUri() . $this->routed . '/download/?file=' . $singleFile . '&hash=' . $hash . '">' . $singleFile . '</a><br/> <br/>';
        }
    }

    $operations .= '<a href="' . $URI->getBaseUri() . $this->routed . '/receipt/' . $row['line_ID'] .'/"> | Ricevuta| </a> <br/>';

    echo '
            <tr style="max-height: 120px; !important;">
                <td>
                    <img style="max-height: 120px"
                        src="' . $URI->getBaseUri(true) . 'modules/shop/images/items/' . $row['product_image'] . '" 
                        class="img-fluid" alt="' . str_replace('"', '\"', $row['title']) . '" />
                </td>
                <td>
                    ' . $row['title'] . '<br/>
                    <small><em>' . $row['cod_art'] . '</em></small><br/>
                    ' . $row['customizations'] . '
                </td>
                <td>
                   ' . $row['item_qty'] . ' 
                </td>
                                
                <td>
                ' . $row['final_price'] . '&euro;
                </td>
                
                <td>
                ' . $lineDiscounted . '&euro;
                </td>
                <td>' . $downloads . '</td>
                <td>' . $operations . '</td>

            </tr>';
}

echo '
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
             <td><strong>' . $totalDiscounted. '</strong></td>  
             <td></td>          
             <td></td>          
        </tr>
    </tbody>
</table>';

