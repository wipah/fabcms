<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/11/2017
 * Time: 15:15
 */

if (!$core->loaded)
    die ("Not loaded directly");

if (isset($path[3])) {
    require_once 'op_orders_show.php';

    return;
}

$template->navBarAddItem( 'Shop', $URI->getBaseUri() . $this->routed);
$template->navBarAddItem('Elenco ordini');

$this->addTitleTag("I miei ordini");


echo '
<div class="row">
        <div class="col-md-6">
             <h1>I miei ordini</h1>       
        </div>
        
        <div class="col-sm-3">
            <div class="fabCMS-Shop-QuickCart" id="quickCart"></div>
        </div>
        
        <div class="col-sm-3">
        <form method="post" action="' . $URI->getBaseUri() . $this->routed . '/search/">
            <input type="text" name="fabShopSearch" id="fabShopSearch">
            <button class="btn btn-secondary" type="submit" value="Search">Cerca articoli</button>
        </form>
    </div>
</div>';
echo '<h2>Ordini</h2>';

if (!$user->logged) {
    echo '<div class="alert alert-info">
            <strong>Login!</strong> Effettua il login per visualizzare gli ordini.
          </div>';

    return;
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_carts 
          WHERE user_ID = ' . $user->ID . ' 
             AND status = 1;';



if (!$result = $db->query($query)) {

    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_order_view_error',
                   'details'   => 'Unable to select orders completed ' . $query,
    ]);

    echo 'Query error.' . $query;
    return;
}

if (!$db->affected_rows) {
    echo '<div class="alert alert-info">
            <strong>Informazione!</strong> Non risultano ancora ordini completati.
          </div>';

    return;
}

echo '  
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Ordine ID</th>
        <th>Data ordine</th>
        <th>Visualizza</th>
      </tr>
    </thead>
    <tbody>';

while ($row = mysqli_fetch_assoc($result)) {
    echo '
    <tr>
        <td>' . $row['ID'] . '</td>
        <td>' . $row['latest_update'] . '</td>
        
        <td>
            <a href="' . $URI->getBaseUri() . $this->routed . '/orders/' . $row['ID'] . '/">Visualizza</a>
        </td>
        
    </tr>';
}

echo '</tbody>
</table>';