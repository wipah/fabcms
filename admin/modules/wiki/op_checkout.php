<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 10/02/2017
 * Time: 16:33
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if (isset($_GET['do'])){
    require_once ('op_checkout_build.php');
    return;
}

echo '
<h2>Checkout builder</h2>
<form method="post" action="admin.php?module=wiki&op=checkout&do" class="form-horizontal">
    <input type="hidden" value="dummy" name="dummy" id="dummy"/>
    
    <fieldset>
        
        <div class="form-group row">
          <label class="col-md-4 control-label" for="titlePage">Intestazione checkout</label>
          <div class="col-md-4">                     
            <input class="form-control" id="titlePage" name="titlePage" />
          </div>
        </div>
        
        <div class="form-group row">
          <label class="col-md-4 control-label" for="tags">Tags</label>  
          <div class="col-md-4">
          <input id="textinput" name="tags" type="tags" placeholder="placeholder" class="form-control input-md">
          <span class="help-block">help</span>  
          </div>
        </div>
    
        <div class="form-group row">
            <label class="col-md-4 control-label" for="singlebutton">Operazioni</label>
            <div class="col-md-4">
            <button type="submit" id="singlebutton" name="singlebutton" class="btn btn-primary">Esegui il checkout</button>
            </div>
        </div>

    
    </fieldset>
</form>';