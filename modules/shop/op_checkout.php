<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/10/2017
 * Time: 12:47
 */

if (!$core->loaded)
    die ("Not loaded directly");

echo '
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">

  <!-- Saved buttons use the "secure click" command -->
  <input type="hidden" name="cmd" value="_s-xclick">
  <input type="hidden" name="notify_url" value="' . $URI->getBaseUri() . $this->routed . '/paypal-ipn/">

  <input type="hidden" name="order_number" value="1">
  <input type="hidden" name="amount" value="105">

  <!-- Saved buttons display an appropriate button image. -->
  <input type="image" name="submit"
    src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif"
    alt="PayPal - The safer, easier way to pay online">
  
  <img alt="" width="1" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" >

</form>';