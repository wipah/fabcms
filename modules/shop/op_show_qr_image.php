<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/04/2019
 * Time: 10:20
 */

if (!$user->logged)
    die ("You must be logged");

if (!isset($path[3]))
    die ("No ID passed");

$ID = (int) $path[3];
$this->noTemplateParse = true;
require_once ('lib/phpqrcode/qrlib.php');

$query = 'SELECT I.*, 
          C.ID AS cart_ID,
          C.latest_update,
          ITEM.cod_art, 
          ITEM.title
          FROM ' . $db->prefix . 'shop_cart_items I
          LEFT JOIN ' . $db->prefix . 'shop_carts C
            ON I.cart_ID = C.ID
          LEFT JOIN ' . $db->prefix . 'shop_items ITEM
            ON I.item_ID = ITEM.ID  
          WHERE I.ID = ' . $ID .' 
            AND C.user_ID = ' . $user->ID . '
          LIMIT 1';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){
    echo 'Query error.';
    return;
}

$row = mysqli_fetch_assoc($result);

QRcode::png('CID:' .  $row['cart_ID'] . '||ID:' . $ID . '||ICODART:' . $row['cod_art'] . '||IDESC:' . $row['title'] . '||QTY:' . $row['item_qty'] .'||SEC:' . $core->getDate($row['latest_update']) . '-' .substr(md5($ID . $conf['security']['siteKey']),0,4));