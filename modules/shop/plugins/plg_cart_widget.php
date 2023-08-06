<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/12/2017
 * Time: 10:17
 */

function plugin_cart_widget($dataArray)
{
    global $db;
    global $core;
    global $user;
    global $URI;
    global $conf;
    global $module;
    global $fabShop;
    global $language;

    $language->loadLang('shop');
    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }

    if (!is_object($fabShop)) {
        require_once $conf['path']['baseDir'] . 'modules/shop/lib/class_shop.php';
        $fabShop = new \CrisaSoft\FabCMS\shop();
    }

    if ($user->logged) {
        $cart_ID = $fabShop->userHasCart($user->ID);
    } else {
        $cart_ID = $fabShop->anonymousHasCart();
    }

    if ($cart_ID < 0)
        return '<a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/">' . $language->get('shop', 'pluginCartWidgetNoItems') . '</a>';

    $values = $fabShop->getCartValues($cart_ID);

    if ($values < 0)
        return '<a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/">' . $language->get('shop', 'pluginCartWidgetNoItems') . '</a>';

    (int) $values['items'] === 1 ? $measure = 'articolo' : $measure = 'articoli';


    return '<ins class="fa fa-shopping-cart shoppingCartIcon" aria-label="true"></ins>&nbsp;
            <a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/cart/">' . $values['items'] . ' ' . $measure . '</a>&nbsp;<br/><br/>
            <ins class="fa fa-euro shoppingCartIcon" aria-label="true"></ins>&nbsp;
            <span class="shoppingCartLink">' .  ( $values['taxable']) . '</span>
            ';
}