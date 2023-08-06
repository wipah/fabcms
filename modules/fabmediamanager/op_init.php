<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/09/2015
 * Time: 08:18
 */

if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

$fabMediaModule     =   $core->in($path[3], true);
$fabMedia->module   =   $fabMediaModule;

$selectLanguage = '<select id="fabMediaLanguage"><option value="**">Any</option>';
foreach ( $conf['langAllowed'] as $singleLanguage ) {
    $selectLanguage .= '<option value="' . $singleLanguage . '">' . $singleLanguage . '</option>';
}
$selectLanguage .= '</select>';

$fabMediaContent = '
<div class="FabCMS-adminDefaultPanel">
    Keywords: <input id="fabMediaSearchKeywords" onkeypress="fabMediaRenderView();"/>
	Order by: <select id="fabMediaOrderBy">
			    <option value="0">None</option>
				<option value="1">Name ASC</option>
				<option value="2">Name DESC</option>
				<option value="3">Date ASC</option>
				<option value="4">Date DESC</option>
			  </select>
    Modified: <select id="fabMediaModified">
				<option value="0">Any</option>
				<option value="1">Modified</option>
				<option value="2">Unmodified</option>
			  </select>
	Language: ' .  $selectLanguage  . '		  
    Limit:   <select id="fabMediaLimit">
                <option value="0">No limit</option>
                <option value="1">20</option>
                <option value="2">40</option>
                <option value="3">100</option>
              </select>
			 
			 <button onclick="fabMediaRenderView();">Render view</button>
</div>
<div style="margin-top:12px; max-height: 450px; overflow: auto; background-color:#f5f8ff; padding: 12px; border: 1px dashed gray; padding: 4px;" id="view">

</div>
<div style="margin-top:12px; background-color:#f5f8ff; padding: 12px; border: 1px dashed gray; padding: 4px;" id="fabMediaInfo"></div>';

$uploadManagerContent = '<div id="html5_uploader">Your browser doesn\'t support native upload.</div>';

$externalVideoContent =  '
                        <div class="form-group">
                            <label class="control-label" for="video_youtube_title">Title</label>
                            <input type="text" class="form-control-sm input-sm" id="video_youtube_title">

                            <label class="control-label" for="video_youtube_ID">Code</label>
                            <input type="text" class="form-control-sm input-sm" id="video_youtube_ID">
                            
                            <button class="form-control-sm" id="youtubeManagerButton" onclick="videoAddYouTube();">Add</button>
                        </div>
                        <div id="videoList">Please wait...</div>
        
        
        
        <div id="youtube_status"></div>';
echo $template->getTabs('fabMediaManager', ['FabMedia', 'Upload manager', 'External video manager'], [$fabMediaContent, $uploadManagerContent, $externalVideoContent] , []);

echo '
<script type="text/javascript">
fabMediaInit();

$(function()
{
    fabMediaListVideo();
});

function fabMediaListVideo() {
  $.post( "' . $URI->getBaseUri() . $this->routed . '/list-video/", { type: "youtube" })
  .done(function( data )
  {
    $("#videoList").html(data);
  });
}

function videoAddYouTube() {
  youtubeTitle = $("#video_youtube_title").val();
  youtubeID    = $("#video_youtube_ID").val();
  
  if (youtubeID.length === 0){
    alert ("Empty video passed");
    return;
  }
  
  $.post( "' . $URI->getBaseUri() . $this->routed . '/add-video/", { type: "youtube", 
                                                                     youtubeTitle: youtubeTitle,
                                                                     youtubeID: youtubeID })
  .done(function( data ) {
    fabMediaListVideo();
  });
}

function fabMediaRenderView() {

    $("#view").html("Media is being updated. Plese wait.");
    
	fabMediaSearchKeywords  = $(\'#fabMediaSearchKeywords\').val();
	fabMediaOrderBy         = $(\'#fabMediaOrderBy\').val();
	fabMediaModified        = $(\'#fabMediaModified\').val();
	fabMediaLanguage        = $(\'#fabMediaLanguage\').val();
	fabMediaLimit           = $(\'#fabMediaLimit\').val();
    fabMediaModule          = \'' . $fabMedia->module . '\';
    
	$.post( "' . $URI->getBaseUri() . 'fabmediamanager/view/", { customButtons          : \'' . $_POST['customButton'] . '\', 
	                                                             fabMediaSearchKeywords : fabMediaSearchKeywords, 
	                                                             fabMediaLanguage       : fabMediaLanguage,
	                                                             fabMediaOrderBy        : fabMediaOrderBy,
	                                                             fabMediaLimit          : fabMediaLimit, 
	                                                             fabMediaModified       : fabMediaModified, 
	                                                             fabMediaModule         : fabMediaModule   
	                                                            })
		.done(function( data ) {
		    $("#view").html( data );
	});
}
</script>';