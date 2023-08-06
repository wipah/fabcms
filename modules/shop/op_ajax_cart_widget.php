<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/11/2017
 * Time: 14:54
 */

if (!$core->loaded)
    die("no core");

$this->noTemplateParse = true;

if ( (int) $fabShop->cartItems === 0) {
    echo 'Nessun articolo inserito nel carrello.';
} else {
    echo $language->get('shop', 'quickCartItems') . ' ' . $fabShop->cartItems . '<br/>
          ' . $language->get('shop', 'quickCartTotal') . ' ' . $fabShop->cartPrice . '&euro; <br/>
          
          <a style="color:white; !important;" href="' . $URI->getBaseUri() . $this->routed . '/cart/" class="btn btn-success btn-sm">
            <span class="glyphicon glyphicon-shopping-cart"></span> Carrello 
          </a>';
}

echo '&nbsp;<a style="color:white; !important;" href="' . $URI->getBaseUri() . $this->routed . '/orders/" class="btn btn-success btn-sm">
        <span class="glyphicon glyphicon-list-alt"></span> I miei ordini 
      </a>';