<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 02/10/2017
 * Time: 17:44
 */

if (!$core->loaded)
    die("no core");

$this->noTemplateParse = true;

if (!isset($_POST['item_ID'])){
    echo '<!--error-->No item id was passed';
    return;
}


$itemID = (int) $_POST['item_ID'];
echo print_r($_POST);

$fabShop->addToCart($itemID);
$fabShop->updateCartValues($fabShop->cartID);

