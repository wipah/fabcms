<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/02/2019
 * Time: 15:18
 */

if (!$user->logged)
    die ("Not logged");

if (!$user->isAdmin)
    die ("not admin");

if (!isset($path[5])){
    echo 'Missing order ID';
    return;
}

$cart_ID = (int) $path[5];


$template->navBarAddItem('Shop', $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem('CP', $URI->getBaseUri() . $this->routed . '/cp/');
$template->navBarAddItem('Orders', $URI->getBaseUri() . $this->routed . '/cp/orders/');
$template->navBarAddItem('Order view (ID: ' . $cart_ID . ')');

$query = 'SELECT 
            CART_ITEMS.*,
            ITEMS.title,
            ITEMS.cod_art
          FROM ' . $db->prefix . 'shop_cart_items AS CART_ITEMS
          LEFT JOIN ' . $db->prefix . 'shop_items AS ITEMS
            ON ITEMS.ID = CART_ITEMS.item_ID
          WHERE cart_ID = ' . $cart_ID;

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo 'Query error';
}

if (!$db->numRows){
    echo 'No items';
}

echo '<table class="table">
    <thead>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Public price</th>
        <th>Discount 1</th>
        <th>Discount 2</th>
        <th>Discount 3</th>
        <th>Final price</th>
      </tr>
    </thead>
    <tbody>';

$final_price = 0;
$qty         = 0;
while ($row = mysqli_fetch_assoc($result)) {

    $qty += $row['item_qty'];
    $final_price += $row['final_price'];

    echo '<tr>
            <td>' . $row['title'] . ' (<small>' . $row['cod_art']  . '</small>)</td>
            <td>' . $row['item_qty'] . '</td>
            <td>' . $row['public_price'] . '</td>
            <td>' . $row['discount_1'] . '</td>
            <td>' . $row['discount_2'] . '</td>
            <td>' . $row['discount_3'] . '</td>
            <td>' . $row['final_price'] . '</td>
          </tr>';
}
echo '<tr style="background-color:#EAEAEA">
<td></td>
<td>' . $qty . '</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td>' . $final_price . '</td>
</tr>';

echo '</tbody></table>';