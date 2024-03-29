<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/02/2019
 * Time: 15:49
 */

if (!$user->isAdmin)
    die ("Direct call");

if (!$user->isAdmin)
    die("Only admins here");

$template->navBarAddItem('Shop', $URI->getBaseUri() . $this->routed . '/');
$template->navBarAddItem('CP', $URI->getBaseUri() . $this->routed . '/cp/');
$template->navBarAddItem('Homepage');

echo '<h1>Control panel default</h1>';

$query  = 'SELECT CARTS.ID, 
	    CARTS.latest_update,
       USERS.ID  user_ID,
       USERS.email,
       USERS.username,
       (
       	SELECT COUNT(ITEMS.ID)
       	FROM ' . $db->prefix . 'shop_cart_items ITEMS
       	WHERE ITEMS.cart_ID = CARTS.ID 
       	GROUP BY ITEMS.cart_ID
       ) total_items,
       (
       	SELECT SUM(ITEMS.final_price)
       	FROM ' . $db->prefix . 'shop_cart_items ITEMS
       	WHERE ITEMS.cart_ID = CARTS.ID 
       	GROUP BY ITEMS.cart_ID
       ) final_price
FROM ' . $db->prefix . 'shop_carts CARTS
LEFT JOIN ' . $db->prefix . 'users USERS
ON CARTS.user_ID = USERS.ID
WHERE CARTS.`status` = 1
ORDER BY ID DESC
LIMIT 10';



if (!$result = $db->query($query)){
    echo '<pre>Query error: ' . $query . '</pre>';
}

if (!$db->affected_rows) {
    echo 'No orders were completed.';
} else {
    echo '<h2>Latest orders</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User </th>
                    <th>Email</th>
                    <th>Last update</th>
                    <th>Total items</th>
                    <th>Total price</th>
                </tr>
            </thead>
            <tbody>
    ';

    while ($row = mysqli_fetch_assoc($result)){
        echo '<tr>
                <td><a href="' . $URI->getBaseUri() . $this->routed .'/cp/orders/view/' . $row['ID'] .'/">' . $row['ID'] . '</a></td>
                <td>' . $row['username'] . ' (ID: ' . $row['user_ID'] . ')</td>
                <td>' . $row['email'] . '</td>
                <td>' . $core->getDateTime($row['latest_update']) . '</td>
                <td>' . $row['total_items'] . '</td>
                <td>' . $row['final_price'] . '</td>
              </tr>';
    }

    echo '</tbody></table></div>';
}