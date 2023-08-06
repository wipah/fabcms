<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/11/2018
 * Time: 09:20
 */

if (!$core->loaded)
    die("Direct access");

$this->noTemplateParse = false;

if (!isset($_GET['q'])) {
    echo '<div class="alert alert-warning" role="alert">
            ' . $language->get('media', 'searchNoKeywordPassed', null) . '
          </div>';
}

$template->navBarAddItem('Mediamanager', $URI->getBaseUri() . 'media/');
$template->navBarAddItem($language->get('media', 'searchTitleSearch', null));

$q = $core->in($_GET['q'], true);
$q = str_replace('%','', $q);

echo '

<div>
  <div class="form-row">
    
    <div class="form-group col-md-3">
      <label for="searchKeywords">Keywords</label>
      <input type="text" class="form-control" id="searchKeywords" placeholder="Keywords" value="' . $q . '">
    </div>

    <div class="form-group col-md-3">
      <label for="searchOrderBy">Order</label>
      <select class="form-control" id="searchOrderBy">
        <option value="1">By date ASC</option>
        <option value="2">By date DESC</option>
        <option value="3">By name ASC</option>
        <option value="4">By name Desc</option>
      </select>
    </div>

    <div class="col-md-1">
        <label for="btnSearch">&nbsp;</label>
        <button onclick="searchImage();" id="btnSearch" type="submit" class="form-control btn btn-xs btn-primary">' . $language->get('media', 'searchFormSearch', null) . '</button>
    </div>  
  </div>
</div>

<div id="searchResult"></div>
';

$theScript = '

$(function() {
    searchImage();
});

function searchImage(){
    
    q = $("#searchKeywords").val();
    
    orderBy = $("#searchOrderBy").val();

    $("#searchResult").html("' . $language->get('media', 'searchPleaseWait', null) . '");
        
    $.post( "' . $URI->getBaseUri() . 'media/search_engine/", { q: q, 
                                                                          orderBy: orderBy })
      .done(function( data ) {
        $("#searchResult").html(data);
      });

}

';

$this->addScript($theScript);