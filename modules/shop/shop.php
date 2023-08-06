<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/07/2017
 * Time: 11:44
 */

require_once __DIR__ . '/lib/class_shop.php';
$fabShop = new \CrisaSoft\FabCMS\shop();


switch ($path[2])
{
    case 'quick-cart':
        require_once 'op_ajax_cart_widget.php';
        break;
    case 'ajax-update-qty':
        require_once 'op_ajax_update_qty.php';
        break;
    case 'ajax-add-to-cart':
        require_once 'op_ajax_add_to_cart.php';
        break;
    case 'ajax-view-cart':
        require_once  'op_ajax_view_cart.php';
        break;
    case 'receipt':
        require_once 'op_receipt.php';
        break;
    case 'cart':
        require_once 'op_cart.php';
        break;
    case 'cart_finalize':
        require_once 'op_cart_finalize.php';
        break;
    case 'search':
        require_once 'op_search.php';
        break;
    case 'item':
        require_once 'op_show_item.php';
        break;
    case 'checkout':
        require_once 'op_checkout.php';
        break;
    case 'paypal-ipn':
        require_once 'op_paypal_ipn.php';
        break;
    case 'orders':
        require_once 'op_orders.php';
        break;
    case 'download':
        require_once 'op_download.php';
        break;
    case 'category':
        require_once 'op_categories.php';
        break;
    case 'directcheckout':
        require_once 'op_direct_checkout.php';
        return;
    case 'show-qr-image':
        require_once 'op_show_qr_image.php';
        break;
    case 'cp':
        require_once  'op_cp.php';
        break;
    default:
        require_once 'op_shop_default.php';
        break;
}