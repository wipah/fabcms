<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 04/10/2017
 * Time: 17:35
 */

if (!$core->loaded)
    die ("no way");

$this->noTemplateParse = true;

if (!$user->logged)
    $anonymousHash = $core->in($_COOKIE['cart_secure_hash'], true);

if (!isset($_POST['qty'])){
    echo 'No quantity passed';
    return;
}
$qty = (int) $_POST['qty'];

if (!isset($_POST['line_ID'])){
    echo 'No line_ID passed';
    return;
}
$line_ID = (int) $_POST['line_ID'];

// Is quantity > 0
if ($qty > 0)
{
    // Qty is > 0, so we have to update the line
    echo 'Updating with ' . $qty;

    $query = '
    UPDATE ' . $db->prefix . 'shop_cart_items AS I
    LEFT JOIN ' . $db->prefix . 'shop_carts AS C
        ON I.cart_ID = C.ID
    SET     I.item_qty     = ' . $qty       . '
    WHERE   I.ID      = ' . $line_ID   . '
        AND C.status = 0
        AND ' . ($user->logged === true
            ? 'C.user_ID =      ' . $user->ID
            : 'C.anonymous_hash = \'' . $anonymousHash . '\''
        ) . ';';

    $db->setQuery($query);

    if (!$db->executeQuery('update')){
        $relog->write(['type'      => '4',
                       'module'    => 'SHOP',
                       'operation' => 'shop_ajax_update_qty',
                       'details'   => 'Cannot update quantity. Query error. ' . $query,
                      ]);
    } else {
        echo 'OK';
    }
} else {
    // Qty is < 0, so we have to remove the line

    echo 'Deleting with ' . $qty;

    $query = '
    DELETE I FROM ' . $db->prefix . 'shop_cart_items AS I
    LEFT JOIN ' . $db->prefix . 'shop_carts AS C
        ON I.cart_ID = C.ID
    WHERE   I.ID      = ' . $line_ID   . '
    AND C.status = 0
    AND ' . ($user->logged === true
            ? 'C.user_ID =      ' . $user->ID
            : 'C.anonymous_hash = \'' . $anonymousHash . '\''
        ) . ';';
    $db->setQuery($query);

    echo $query;
    if (!$db->executeQuery('delete')){
        $relog->write(['type'      => '4',
                       'module'    => 'SHOP',
                       'operation' => 'shop_ajax_update_qty',
                       'details'   => 'Cannot update quantity on delete. Query error. ' . $query,
                      ]);
    } else {
        echo 'OK';
    }

}
