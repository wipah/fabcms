<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 29/01/2019
 * Time: 12:29
 */
if (!$core->adminBootCheck())
    die("Check not passed");

echo '<h1>Media manager</h1>
<div id="mediaList"></div>

<script>

updateMediaList();

function updateMediaList() {
    $.post( "admin.php?module=fabmediamanager&op=ajaxMediaList", { })
    .done(function( data ) {
        $("#mediaList").html(data);
    });
}
</script>
';