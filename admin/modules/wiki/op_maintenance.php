<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 19/01/2017
 * Time: 11:56
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Maintenance');

echo '
<div class="row">
    <div class="col">
        <h2>Rebuild pages</h2>
        <button onclick="rebuildPages();">Rebuild</button>
        <div id="rebuildStatus"></div>
    </div>
    
    <div class="col">
        <h2>Rebuild trackbacks</h2>
        <button onclick="rebuildTrackbacks();">Rebuild</button>
        <div id="rebuildTrackbacksStatus"></div>
    </div>
    
    <div class="col">
        <h2>Associate images</h2>
        <button onclick="associateImages();">Associate</button>
        <div id="associateImagesStatus"></div>
    </div>
    
    <div class="col">
        <h2>Rebuild stats</h2>
        <button onclick="rebuildStats();">Rebuild</button>
        <div id="rebuildStatsStatus"></div>
    </div>
    
    <div class="col">
        <h2>Rebuild first tag</h2>
        <button onclick="rebuildFirstTag();">Rebuild</button>
        <div id="rebuildFirstTagStatus"></div>
    <div class="col">
        <h2>Rebuild SEO</h2>
        <button onclick="rebuildSeo();">Rebuild</button>
        <div id="rebuildSeoStatus"></div>
    </div>
</div>
<script type="text/javascript">

function rebuildStats()
{

    $("#rebuildStatsStatus").html("Rebuilding stats.");

    $.post( "admin.php?module=wiki&op=rebuildStats", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#rebuildStatsStatus").html(data);
      });  
}

function rebuildFirstTag()
{

    $("#rebuildFirstTagStatus").html("Rebuilding first tag.");

    $.post( "admin.php?module=wiki&op=rebuildFirstTag", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#rebuildFirstTagStatus").html(data);
      });  
}
function rebuildSeo()
{

    $("#rebuildSeoStatus").html("Rebuilding SEO.");

    $.post( "admin.php?module=wiki&op=rebuildSeo", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#rebuildSeoStatus").html(data);
      });  
}


function rebuildPages()
{

    $("#rebuildStatus").html("Rebuilding pages.");

    $.post( "admin.php?module=wiki&op=rebuildPages", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#rebuildStatus").html(data);
      });  
}


function rebuildTrackbacks()
{
    $("#rebuildTrackbacksStatus").html("Rebuilding pages.");

    $.post( "admin.php?module=wiki&op=rebuildTrackbacks", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#rebuildTrackbacksStatus").html(data);
      });  
}

function associateImages()
{
    $("#associateImagesStatus").html("Associating images.");

    $.post( "admin.php?module=wiki&op=associateImages", { name: "John", time: "2pm" })
      
      .done(function( data ) {
        $("#associateImagesStatus").html(data);
      });  
}
</script>';