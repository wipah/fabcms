<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/09/2015
 * Time: 20:56
 */

if (!$core->loaded)
    die();

if (!$user->logged)
    die("No direct access.");

$this->noTemplateParse = true;

$fabMedia->customButtons[]  =   explode('||', htmlentities($_POST['customButton']));
$fabMedia->module           =   $core->in($_POST['fabMediaModule'], true);

$filterKeyword              =   $core->in($_POST['fabMediaFilterKeyword']);

if (empty($_POST['ID'])) {
    echo 'No ID passed.';

    return;
}

$ID = (int) $_POST['ID'];

if (!empty($_POST['type'])) {
    $type = ' AND type = \'' . $core->in($_POST['type']) . '\'';
}

// Build the query
$query = '
SELECT F.*,
       F.ID AS media_ID, 
       M.ID AS master_ID,
       F.filename,
       F.extension,
       F.type,
       F.subtype,
       F.size,
       F.upload_date,
       V.ID AS video_ID,
       V.*
FROM ' . $db->prefix . 'fabmedia_masters AS M
LEFT JOIN ' . $db->prefix . 'fabmedia AS F
    ON M.ID = F.master_ID
LEFT JOIN ' . $db->prefix . 'fabmedia_videos AS V
    ON V.fabmedia_ID = F.ID
WHERE F.ID = \'' . $ID . '\' ' . $type . '
LIMIT 1;';



if (!$result = $db->query($query)) {
    echo 'Query error. The query is: ' . $query;
    return;
}

if (!$db->affected_rows) {
    echo 'No media found! Aborting.: ' . $query;
    return;
}

$row = mysqli_fetch_assoc($result);

// Build license
$query = 'SELECT * 
          FROM ' . $db->prefix . 'licenses_licenses 
          WHERE lang = \'' . $core->shortCodeLang . '\';';

if (!$resultLicenses = $db->query($query)) {
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No license found.' . $query;
    return;
}

$selectLicenses = '<select class="form-control-sm input-sm w-100" id="fabmediamanager_licenses">';
while ($rowLicenses = mysqli_fetch_assoc($resultLicenses)){
    $selectLicenses .= '<option ' . ( (int) $row['license_ID'] === (int) $rowLicenses['ID'] ? 'selected="selected"' : '') . ' value="' . $rowLicenses['ID'] . '">' . $rowLicenses['name'] . '</option>';
}
$selectLicenses .= '</select>';

switch ($row['type']) {
    case 'image':
        // If the file is an image build all the button for the relative variants (size, and quality)
        $imagePath = $row['filename'];

        $extension = strtolower($row['extension']);
        $pos = strrpos($imagePath, '.' . $extension);

        if ($extension        == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif' || $extension == 'webp') {
            $imageFinalMQPath = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
            $imageFinalLQPath = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

            $pathOriginal     = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'];
            $pathMQ           = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath;
            $pathLQ           = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath;
        }

        switch ($fabMedia->module) {
            case 'wiki':
                $sendToEditor = '
                    <div class="row">
                        
                        <div class="col">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button onclick="deleteFile(\'' . $row['media_ID'] . '\')" type="button" class="btn btn-outline-warning">Delete</button>
                                    <button onclick="setAsPageImage(' . $row['media_ID'] . ',\'' . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');" type="button" class="btn btn-outline-primary">Page</button>
                                    <button  onclick="updateInfo()" type="button" class="btn btn-outline-primary">Update</button>
                                </div>    
                        </div>    
                    
                        <div class="col">
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="simpleImage" value="1" >
                            <label class="form-check-label" 
                                for="simpleImage">Simple image</label>
                          </div>        
                       </div> 
                       
                       <div class="col">  
                          <select id="imageQuality" class="form-select form-select-sm" aria-label=".form-select-sm example">
                            <option value="' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalLQPath .  '">Low quality (' . human_filesize(filesize($pathLQ)) . ')</option>
                            <option value="' . 'fabmedia/' . $row['user_ID'] . '/' . $imageFinalMQPath .  '">Medium quality (' . human_filesize(filesize($pathMQ)) . ')</option>
                            <option value="' . 'fabmedia/' . $row['user_ID'] . '/' . utf8_encode($row['filename']) . '" selected>High quality (' . human_filesize(filesize($pathOriginal)) . ')</option>
                          </select> 
                        </div>
                       
                       <div class="col">
                            <button type="button" onclick="sendFileToEditor(\'' . $row['type'] . '\', $(\'#imageQuality\').val(), \'' . $row['filename'] . '\', ' . $row['media_ID']  . ');" class="btn btn-primary">Insert image</button>
                       </div>

                    </div>
';
                break;
        }

        break;
    case 'audio':

        $sendToEditor = '
        <div class="row">
            <div class="col">
                <audio controls>
                    <source src="'  . $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . ' " type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div class="col">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button onclick="deleteFile(\'' . $row['media_ID'] . '\')" type="button" class="btn btn-outline-warning">Delete</button>
                    <button disabled onclick="setAsAudioGuide(' . $row['media_ID'] . ',\'' . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . '\', \'' . $row['filename'] . '\', \'' . $row['media_ID'] . '\');" type="button" class="btn btn-outline-primary">Page Audioguide</button>
                    <button onclick="updateInfo()" type="button" class="btn btn-outline-primary">Update</button>
                </div>    
            </div>    
            <div class="col">
                <button type="button" 
                        class="btn btn-primary"
                        aria-label="Send FabCode" 
                        onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . $row['filename'] . '\', null, ' . $row['media_ID'] . ', null);">Send to editor
                </button>
            </div>
        </div>';
        break;
    case 'video':
        $sendToEditor = '
        <div class="row">
            <div class="col">
                <video preload="auto" width="480" height="360" controls>
                    <source src="'  . $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $row['filename'] . ' " type="video/mp4">
                </video>
              
            </div>
            <div class="col">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button onclick="deleteFile(\'' . $row['media_ID'] . '\')" type="button" class="btn btn-outline-warning">Delete</button>
                    <button onclick="setFeaturedVideo(\'' . $row['media_ID'] . '\')" type="button" class="btn btn-outline-warning">Featured</button>
                    <button onclick="updateInfo()" type="button" class="btn btn-outline-primary">Update</button>
                </div>    
            </div>    
            <div class="col">
                <button type="button" 
                        class="btn btn-primary"
                        aria-label="Send FabCode" 
                        onclick="sendFileToEditor(\'' . $row['type'] . '\', \'' . $row['filename'] . '\', null, ' . $row['media_ID'] . ', null);">Send to editor
                </button>
            </div>
        </div>';
        break;
}

echo '  <form id="fabMediaInfoForm" method="post">

        <input  type="hidden" id="ID" value="' . $_POST['ID'] . '" />
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class=" nav-link active" aria-selected="true" data-bs-toggle="tab" role="tab" id="tabGeneral" href="#tabPanelGeneral">General</a>
            </li>
            <li class="nav-item">
            
                <a class=" nav-link"  aria-selected="false" data-bs-toggle="tab" role="tab" id="tabVideo" href="#tabPanelVideo">Video</a>
                </li>';

if ($fabMedia->module === 'wiki') {
    echo '  
                <li class="nav-item">
            
                <a class=" nav-link" aria-selected="false" data-bs-toggle="tab" role="tab" id="tabPages" href="#tabPanelPages">Pages</a>
                </li>
           
';
}


$selectLanguage = '<select class="form-control" id="FabMediaLanguage" name="FabMediaLanguage">';
foreach ($conf['langAllowed'] as $lang ) {
    $selectLanguage .= '<option ' . ($row['lang'] === $lang ? 'selected' : '') . ' value="' . $lang . '">' . $lang . '</option>';
}
$selectLanguage .= '</select>';

$filename = mb_convert_encoding($row['filename'], 'UTF-8');


echo '
        
         </ul>
    
         <div class="tab-content" id="nav-tabContent">
  
              <div id="tabPanelGeneral" class="tab-pane fade show active " role="tabpanel" aria-labelledby="tabPanelGeneral">
    
                <div class="fabTemplateModuleSectionHead">' .
    $filename .
    ' - <a onclick="renameFile(' . $row['media_ID'] . ', \'' . $filename . '\');">Rename</a>
                </div>

                
                <hr />
                
                <div class="row">
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="type">Type</label>
                            <input value="' . $row['type'] . '" type="text" class="form-control-sm input-sm w-100" id="fabmediamanager_type" disabled="disabled">
                        </div>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="subtype">Subtype</label>
                            <input value="' . $row['subtype'] . '" type="text" class="form-control-sm input-sm w-100" id="fabmediamanager_subtype" placeholder="Placeholder text">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label" for="FabMediaLanguage">Lang</label> <br/>
                            ' . $selectLanguage . '
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="fabmediamanager_license">License</label>
                                    ' . $selectLicenses . '
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="author">Author</label> <br/>
                                    <input value="' . $row['author'] . '" type="text" class="form-control-sm w-100" id="fabmediamanager_author" placeholder="Placeholder text">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-2">   
                        <div class="form-check">
                            <input class="form-check-input" ' . ( (int)$row['modified'] === 1 ? "checked" : "") . ' id="fabmediamanager_modified" type="checkbox" value="1">
                                <label class="form-check-label" for="fabmediamanager_modified">
                                Modified
                                </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" ' . ((int)$row['enabled'] === 1 ? 'checked' : "") . ' id="fabmediamanager_enabled" type="checkbox" value="1">
                                <label class="form-check-label" for="fabmediamanager_enabled">
                                Enabled
                                </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" ' . ((int)$row['indexable'] === 1 ? 'checked' : "") . ' id="fabmediamanager_indexable" type="checkbox" value="1">
                                <label class="form-check-label" for="fabmediamanager_indexable">
                                Indexable
                                </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" ' . ((int)$row['global_available'] === 1 ? 'checked' : "") . ' id="fabmediamanager_globalAvailable" type="checkbox" value="1">
                                <label class="form-check-label" for="fabmediamanager_globalAvailable">
                                Global
                                </label>
                        </div>
                    </div>   
                </div>
    
                <hr />
    
                <div class="row">
                
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="fabmediamanager_title">Title</label>
                            <input style="width: 95%" value="' . $row['title'] . '" type="text" class="form-control-sm" id="fabmediamanager_title" placeholder="Title">
                        </div>
                    </div>
                
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="tags">Tags</label>
                            <input style="width: 95%" value="' . $row['tags'] . '" type="text" class="form-control-sm" id="fabmediamanager_tags" placeholder="Tags used">
                        </div>
                    </div>
                  
                </div>
                    
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label" for="fabmediamanager_description">Description</label>
                            <textarea class="" rows="4" id="fabmediamanager_description">' . $row['description'] . '</textarea>
                        </div>
                    </div>
                </div>
              

              <div class="fabTemplateModuleSectionSubHead mt-4 mb-4">Database references</div>
                
              <div class="row">
                
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="master_ID">Master ID</label>
                            <input value="' . utf8_encode($row['master_ID']) . '" type="text" class="form-control-sm input-sm" id="master_ID" disabled="disabled">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                         <div class="form-group">
                            <label class="control-label" for="media_ID">Media ID</label>
                            <input value="' . utf8_encode($row['media_ID']) . '" type="text" class="form-control-sm input-sm" id="media_ID" disabled="disabled">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="uploadDate">Upload</label>
                            <input value="' . $row['upload_date'] . '" type="text" class="form-control-sm input-sm" id="uploadDate" disabled="disabled">
                        </div>
                    </div>
                    
                </div>
              </div>              
             
              <div id="tabPanelVideo" class="tab-pane fade" role="tabpanel" aria-labelledby="tabPanelVideo">
                <h2 class="mt-4 mb-4>">Video properties</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="filename">Video ID</label>
                            <input value="' . $row['video_ID'] . '" type="text" disabled class="form-control-sm input-sm" id="fabMediaVideoID">
                        </div>
                    </div>
                
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="filename">Length</label>
                            <input value="' . $row['length'] . '" type="time" class="form-control-sm input-sm" id="FabmediaLength">
                        </div>
                    </div>
                
                </div>
                
              </div>
              ';

switch ($fabMedia->module) {
    case 'wiki':
        $query = '
        SELECT 
          P.title, 
          P.title_alternative, 
          P.trackback
        FROM ' . $db->prefix . 'wiki_pages_files AS F
            LEFT JOIN ' . $db->prefix . 'wiki_pages AS P
        ON P.ID = F.page_ID
            WHERE F.fabmedia_ID = ' . $ID . '
        AND P.visible = 1';

        
        if (!$resultWikiPages = $db->query($query)) {
            echo 'Query error while selecting pages from wiki!';
        } else {
            if (!$db->affected_rows) {
                $pagesFromWiki = $language->get('media', 'showImageWikiHasNoPage');
            } else {
                $pagesFromWiki = '';
                while ($rowWikiPage = mysqli_fetch_assoc($resultWikiPages)) {
                    $pagesFromWiki .= '&bull; <a href="' . $URI->getBaseUri() . 'wiki/' . $rowWikiPage['trackback'] . '/">' . $rowWikiPage['title'] . $rowWikiPage['title_alternative'] . '</a><br/>';
                }
            }
        }

        echo '
              
              <div id="tabPanelPages" class="tab-pane fade" role="tabpanel" aria-labelledby="tabPanelPages">
                <div class="row">               
                    <div class="col-md-12">
                       <h2>Pages</h2>
                      ' . $pagesFromWiki . '
                    </div>
                </div>
              </div>
              
              
              ';

        break;
}


// Permissions
switch ( $fabMedia->module) {
    case 'wiki':
        $enablePageImage = true;
        $enableUpdateFile = true;
        $enableDelete = true;
        break;
    default:
        $enablePageImage = false;
        $enableUpdateFile = true;
        $enableDelete = true;
}

echo '     
           <div class="fabTemplateModuleSectionSubHead mt-4 mb-4">Operations</div>
           <div class="row">
            ' . $sendToEditor . '
           </div> 
            <!-- Start buttons -->
                     ' . $fabMedia->renderCustomButton();
echo '    
          
            </div>
        </form>

<script>
function renameFile(ID, oldFile) {
    var newName = prompt ("Please, enter a new file", oldFile);

    if (newName != null) {
        $.post( "' . $URI->getBaseUri() . 'fabmediamanager/rename/", {  ID : ID, newName : newName })
        .done(function( data ) {
            if (data !== "ok"){
                alert (data);
            }
        });
    }
}

function deleteFile(ID) {
    if (!confirm("Are you sure you want to delete this file?"))
        return;

    $.post( "' . $URI->getBaseUri() . 'fabmediamanager/deletefile/", {  ID : ID })
    .done(function( data ) {
        $("#fabMediaUpdate").html(data);
    });
}

function sendFileToEditor(type, path, filename, ID, provider_ID = null) {
    switch (type){
        case "image": /* [img src="theSource"|alt==Alt for the image||copyright==FabCMS||description==this is a simple description] */
            if ($("#simpleImage").is(":checked") ) {
                simpleImage = "||simpleImage==1";
            } else {
                simpleImage = "";
            }
            
            tinyMCE.execCommand("mceInsertContent", false,"[$img src=" + path + "|ID=="+ ID + "||description==" + type + "||alt==" + $(\'#fabmediamanager_title\').val() + "||class==img-fluid" + simpleImage + "$]");
            break;
        case "audio":
            tinyMCE.execCommand("mceInsertContent", false,"[$audio src=" + path + "|ID=="+ ID + "||description==" + type + "||alt==" + $(\'#fabmediamanager_title\').val() + "$]");            
            break;
        case "video": /* [youtube abcde] */
            tinyMCE.execCommand("mceInsertContent", false,"[$video src=" + path + "|ID=="+ ID + "||description==" + type + "||alt==" + $(\'#fabmediamanager_title\').val() + "$]");            
            break;
        case "archive":
            tinyMCE.execCommand("mceInsertContent", false,"[file " + ID + "]");            
            break;
        default:
            tinyMCE.execCommand("mceInsertContent", false,"<a data-ID=" + ID + "  href=" + path + ">" + filename + "</a>");
            break;
    }
}

function setFeaturedVideo(media_ID)
{
    $("#featured_video_ID").val(media_ID);
}

function updateInfo() {
        console.log("Updating info");
    
        fabmediamanager_master_ID = ' . $row['master_ID'] . ';
        fabmediamanager_subtype = $("#fabmediamanager_subtype").val();
            
        if ( $("#fabmediamanager_enabled").prop(\'checked\') ) {
            fabmediamanager_enabled = 1;
        } else {
            fabmediamanager_enabled = 0;
        }

        if ( $("#fabmediamanager_indexable").prop(\'checked\') ) {
            fabmediamanager_indexable = 1;
        } else {
            fabmediamanager_indexable = 0;
        }
       
        if ( $("#fabmediamanager_globalAvailable").prop(\'checked\') ) {
            fabmediamanager_globalAvailable = 1;
        } else {
            fabmediamanager_globalAvailable = 0;
        }

        image_ID = $("#ID").val();

        fabmediamanager_title       =   $("#fabmediamanager_title").val();
        fabmediamanager_type       =   $("#fabmediamanager_type").val();
        fabmediamanager_tags        =   $("#fabmediamanager_tags").val();
        fabmediamanager_description =   tinyMCE.get(\'fabmediamanager_description\').getContent();
        fabmediamanager_copyright   =   $("#fabmediamanager_licenses").val();
        fabmediamanager_author      =   $("#fabmediamanager_author").val();
        fabmediamanager_language    =   $("#FabMediaLanguage").val();
        fabmediamanager_indexable    =   fabmediamanager_indexable;
        fabmediamanager_enabled    =   fabmediamanager_enabled;
        fabmediamanager_globalAvailable    =   fabmediamanager_globalAvailable;
        fabmediamanager_length      =   $("#FabmediaLength").val();
        fabmediamanager_video_ID    =   $("#fabMediaVideoID").val();
        
        console.log("Calling page");
    
        $.post( "' . $URI->getBaseUri() . 'fabmediamanager/saveinfo/", {  ID : image_ID,
                                                                          fabmediamanager_language      :   fabmediamanager_language,
                                                                          fabmediamanager_master_ID     :   fabmediamanager_master_ID,
                                                                          fabmediamanager_type          :   fabmediamanager_type,
                                                                          fabmediamanager_subtype       :   fabmediamanager_subtype,
                                                                          fabmediamanager_indexable     :   fabmediamanager_indexable,
                                                                          fabmediamanager_enabled       :   fabmediamanager_enabled,
                                                                          fabmediamanager_globalAvailable       :   fabmediamanager_globalAvailable,
                                                                          fabmediamanager_title         :   fabmediamanager_title,
                                                                          fabmediamanager_tags          :   fabmediamanager_tags,
                                                                          fabmediamanager_description   :   fabmediamanager_description,
                                                                          fabmediamanager_copyright     :   fabmediamanager_copyright,
                                                                          fabmediamanager_author        :   fabmediamanager_author, 
                                                                          fabmediamanager_length        :   fabmediamanager_length, 
                                                                          fabmediamanager_video_ID      :   fabmediamanager_video_ID})
        .done(function( data ) {
            console.log("Done calling page");
            $("#fabMediaUpdate").html(data);
        });
}
</script>';

function human_filesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}