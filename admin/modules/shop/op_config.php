<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 31/01/2017
 * Time: 11:27
 */

if (!$core->adminLoaded)
   return;

if ($_GET['command'] === 'save')
{
    $this->noTemplateParse = true;

    $lang = $core->in($_GET['lang']);

    $shopName                     =     $core->in($_POST['shopName']);
    $shopTitle                    =     $core->in($_POST['shopTitle']);
    $shopEnabled                  =     (int) $_POST['shopEnabled'];
    $shopUseVAT                   =     (int) $_POST['shopUseVAT'];
    $shopUseSandbox               =     (int) $_POST['shopUseSandbox'];
    $shopPaypalBusinessEmail      =     $core->in($_POST['shopPaypalBusinessEmail']);


    $query = 'DELETE FROM ' . $db->prefix . 'shop_config 
              WHERE lang = \'' . $core->in($_GET['lang']) . '\'';

    $db->setQuery($query);

    if (!$db->executeQuery('delete')) {
        echo 'Deleting old config error. ' . $query;
        return;
    }

    $query = 'INSERT INTO ' . $db->prefix . 'shop_config 
    (param, lang, value)
    VALUES 
    (\'shopName\', \'' . $lang . '\', \'' . $shopName . '\'),
    (\'shopTitle\', \'' . $lang . '\', \'' . $shopTitle . '\'),
    (\'shopEnabled\', \'' . $lang . '\', \'' . $shopEnabled . '\'),
    (\'useVAT\', \'' . $lang . '\', \'' . $shopUseVAT . '\'),
    (\'useSandbox\', \'' . $lang . '\', \'' . $shopUseSandbox . '\'),
    (\'businessEmail\', \'' . $lang . '\', \'' . $shopPaypalBusinessEmail . '\')
    
    ;';

    $db->setQuery($query);
    if (!$db->executeQuery('insert')) {
        echo 'Query error. ' . $query;
        return;
    }

    echo '<div class="alert alert-success clearfix">
            <strong>Success!</strong> Configuration was saved.
        </div>';

    return;
}

if (!isset($_GET['lang']))
{
    echo '<h2>Shop config</h2>
          <p>Please, select a language.</p>
          
          <ul>';

    foreach ($conf['langAllowed'] AS $singleLang) {
        echo '<li><a href="admin.php?module=shop&op=config&lang=' . $singleLang . '">' . $singleLang . '</a></li>';
    }

    echo '</ul>';

    return;
}

$lang = $core->in($_GET['lang'], true);

echo '<h2>Shop configuration</h2>';
$template->navBar[] = '<a href="admin.php?module=shop">Shop</a>';
$template->navBar[] = '<a href="admin.php?module=shop&op=config">Config</a>';

$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_config 
          WHERE lang = \'' . $lang . '\';';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

while ($row = mysqli_fetch_assoc($result)) {
    $shopConfig[$row['param']] = $row['value'];
}

$shopGeneral = ' <h3>General shop config</h3>
    <p>
        <div class="form-group row">
          <label class="col-md-3 control-label" for="useToc">Shop name</label>  
          <div class="col-md-9">
               <input id="shopName" class="form-control" type="text" value="' . $shopConfig['shopName'] . '" /> 
          </div>
        </div>   
        
        <div class="form-group row">
          <label class="col-md-3 control-label" for="useToc">Shop title</label>  
          <div class="col-md-9">
               <input id="shopTitle" class="form-control" type="text" value="' . $shopConfig['shopTitle'] . '" /> 
          </div>
        </div>   
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 control-label" for="shopEnabled">Shop enabled</label>  
          <div class="col-md-9">
               <select id="shopEnabled" name="shopEnabled" class="form-control">
                  <option ' . ( (int) $shopConfig['shopEnabled'] === 1 ? 'selected' : '') . ' value="1">Yes, shop is enabled</option>
                  <option ' . ( (int) $shopConfig['shopEnabled'] === 0 ? 'selected' : '') . ' value="0">No, shop is not enabled</option>
                </select> 
          </div>
        </div>   
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 control-label" for="shopUseVAT">Use VAT</label>  
          <div class="col-md-9">
               <select id="shopUseVAT" name="shopUseVAT" class="form-control">
                  <option ' . ( (int) $shopConfig['useVAT'] === 1 ? 'selected' : '') . ' value="1">Yes, use VAT</option>
                  <option ' . ( (int) $shopConfig['useVAT'] === 0 ? 'selected' : '') . ' value="0">No, VAT is not used</option>
                </select> 
          </div>
        </div>
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 control-label" for="shopUseSandbox">Use Sandbox</label>  
          <div class="col-md-9">
               <select id="shopUseSandbox" name="shopUseSandbox" class="form-control">
                  <option ' . ( (int) $shopConfig['useSandbox'] === 1 ? 'selected' : '') . ' value="1">Yes, use sandbox</option>
                  <option ' . ( (int) $shopConfig['useSandbox'] === 0 ? 'selected' : '') . ' value="0">No, use real transactions</option>
                </select> 
          </div>
        </div>   
           
    </p>';

$payPalConfig = '
    <p>        
        <div class="form-group row">
          <label class="col-md-3 control-label" for="useToc">Business email</label>  
          <div class="col-md-9">
               <input id="shopPaypalBusinessEmail" class="form-control" type="text" value="' . $shopConfig['businessEmail'] . '" /> 
          </div>
        </div>   
        
    </p>';


echo '
<div>
    <!-- Button -->
    <div class="form-group row">
      <label class="col-md-3 control-label" for="singlebutton">Operations</label>
      <div class="col-md-7" id="saveStatus"></div>
      <div class="col-md-2">
        <button onclick="saveConfig();" id="singlebutton" name="singlebutton" class="btn btn-primary float-right">Save config</button>
      </div>
    </div>
</div>

' .
    $template->getTabs('shopConfig',
                       ['General', 'Paypal', 'Banner'],
                       [$shopGeneral, $payPalConfig, ''],
                       []
                      )
    . '
<script type="text/javascript">  
function saveConfig() 
{
   
   shopName                 =   $("#shopName").val();
   shopTitle                =   $("#shopTitle").val();
   shopEnabled              =   $("#shopEnabled").val();
   shopUseVAT               =   $("#shopUseVAT").val();
   shopUseSandbox           =   $("#shopUseSandbox").val();
   shopPaypalBusinessEmail  =   $("#shopPaypalBusinessEmail").val();
   
   $.post( "admin.php?module=shop&op=config&command=save&lang=' . $lang . '", { 
                                                           shopName                 : shopName, 
                                                           shopTitle                : shopTitle, 
                                                           shopEnabled              : shopEnabled, 
                                                           shopUseVAT               : shopUseVAT, 
                                                           shopUseSandbox           : shopUseSandbox, 
                                                           shopPaypalBusinessEmail  : shopPaypalBusinessEmail, 
                                                           })
    .done(function( data ) {
        $( "#saveStatus").html(data);
    });        
}
</script>';