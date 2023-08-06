<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 04/10/2017
 * Time: 15:10
 */

if (!$core->loaded)
    die("Direct call");

if ($path[3] === 'instant-checkout'){

    $cartID = $fabShop->userHasCart($user->ID);

    if ( (int) $fabShop->config['instantCheckout'] === 1) {
        $fabShop->updateDbCartStatus( ['ID' => $cartID, 'status' => 1 ]);
        echo '<a class="alert alert-success">
                <strong>Acquisto completato!</strong>Hai completato l\'acquisto. <a href="' . $URI->getBaseUri() . $this->routed .'/orders/' . $cartID .'/">Visualizza l\'ordine</a>.
              </div>';
        return;
    } else {
        echo 'Non puoi finalizzare l\'ordine.';
    }
}

$template->navBarAddItem('Shop',$URI->getBaseUri() . $this->routed . '/' );
$template->navBarAddItem($articleData['Carrello']);

$template->sidebar .= $template->simpleBlock('On cart', '<div id="quickCart"></div>');
$theScript = '
$.post( "' . $URI->getBaseUri() . $this->routed . '/quick-cart/", {})
  .done(function( data ) {
    $("#quickCart").html(data) ;
  });
';
$template->sidebar .= ' <!--FabCMS-hook:shopCommonFirstSidebar-->';

if (!$user->logged) {
    $template->sidebar .= $template->simpleBlock('Registrati', '<strong><a href="' . $URI->getBaseUri() . 'user/register/">Registrati adesso</a></strong> per effettuare gli ordini');
} else {
    $template->sidebar .= $template->simpleBlock('Ultimi ordini', '<strong><a href="' . $URI->getBaseUri() . $this->routed . '/orders/">Visualizza i tuoi ordini</a></strong> e procedi al download.');
}

echo '<h1>Carrello</h1>

<div id="cartBody">

</div>
';

$theScript = '
$(function(){
   showCart();
});

function changeQTY(line_ID, qty = null) {
    if (qty === null){
        var qty = $("#qty-" + line_ID).val();
    }
    
    $.post( "' . $URI->getBaseUri() . $this->routed . '/ajax-update-qty/", { line_ID: line_ID, qty: qty })
      .done(function( data ) {
        showCart();
      });
}

function showCart() {
    $.post( "' . $URI->getBaseUri() . $this->routed . '/ajax-view-cart/", { foo: "bar" })
      .done(function( data ) {
        $("#cartBody").html(data);
      });
}
';

$this->addScript($theScript);