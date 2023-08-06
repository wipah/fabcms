<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/02/2019
 * Time: 11:55
 */

if (!$user->logged)
    die("Direct call");

if (!$user->isAdmin)
    die("Not admin");

echo '<h1>Order list</h1>';

$query  = 'SELECT CARTS.ID, 
	    CARTS.latest_update,
       CARTS.status,
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
ORDER BY ID DESC';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')){
    echo '<pre>Query error: ' . $query . '</pre>';
}

$this->addJsFile('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', false, false);
$this->addCSSLink('https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css', false, 'all', true);
if (!$db->numRows) {
    echo 'No orders were completed.';
} else {
    echo '<h2>All orders/carts</h2>
    <div class="table-responsive">
        <table id="lastOrders" class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
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
                <td>' . ( (int) $row['status'] === 1 ? '<span style="color:green">Completed</span>' : '<span style="color:red">Not completed</span>' ) . '</td>
                <td>' . $row['username'] . ' (ID: ' . $row['user_ID'] . ')</td>
                <td>' . $row['email'] . '</td>
                <td>' . $core->getDateTime($row['latest_update']) . '</td>
                <td>' . $row['total_items'] . '</td>
                <td>' . $row['final_price'] . '</td>
              </tr>';
    }

    echo '</tbody>
      </table>
    </div>';
}

$theScript = '
$(document).ready(function() {
    $(\'#lastOrders\').DataTable( {
        "order": [[ 0, "desc" ]]
    } );
} );
';

$this->addScript($theScript);