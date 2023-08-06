<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/02/2019
 * Time: 09:22
 */


if (!$core->loaded)
    die ("Not loaded directly");

if (!$user->logged)
    die ("User not logged");

$cart_ID = $fabShop->userHasCart($user->ID);

if ($cart_ID < 1 || $fabShop->cartItems < 1) {
    echo '<div class="alert alert-info">' . $language->get('shop', 'directCheckoutNoCart', null) . '</div>';
    return;
}

$cartValues = ($fabShop->getCartValues($cart_ID));

if ( (int) $cartValues['taxable'] > 0) {
    echo '<div class="alert alert-info">' . $language->get('shop', 'directCheckoutNoFreeItems', null) . '</div>';
    return;
}

$fabShop->updateDbCartStatus(['ID'=>$cart_ID, 'status' => 1] );

$urlOrders = $URI->getBaseUri() . 'shop/orders/';
echo '<div class="alert alert-succes">' . sprintf($language->get('shop', 'directCheckoutSuccess', null), $urlOrders) . '</div>';
