<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 15/09/2015
 * Time: 20:56
 */

if (!$core->loaded)
    die();

if (!$user->isAdmin)
    die("No direct access.");

$this->noTemplateParse = TRUE;
$filterKeyword = $core->in($_POST['fabMediaSearchKeywords']);

switch ($_POST['fabMediaModule']) {
    case 'forum-topic':
        $fabMedia->module = 'forum';
        break;
    case 'wiki':
    default:
        $fabMedia->module = 'wiki';
        break;
}

switch ( (int) $_POST['fabMediaLimit']){
    case 1:
        $limit = ' LIMIT 20';
        break;
    case 2:
        $limit = ' LIMIT 40';
        break;
    case 3:
        $limit = ' LIMIT 100';
        break;
    case 0:
    default:
        $limit = '';
        break;
}

switch ($_POST['fabMediaOrderBy']){
    case 1:
    default;
        $orderBy = 'ORDER BY title ASC';
        break;
    case 2:
        $orderBy = 'ORDER BY title DESC';
        break;
    case 3:
        $orderBy = 'ORDER BY upload_date ASC';
        break;
    case 4:
        $orderBy = 'ORDER BY upload_date DESC';
        break;
}

switch ( (int) $_POST['fabMediaModified']){
    case 1:
        $isModified = 'AND modified = 1 ';
        break;
    case 2:
        $isModified = 'AND modified = 0 ';
        break;
}

$lang = $core->in($_POST['fabMediaLanguage'], true);

if ($lang !== '**')
    $queryLang = ' AND lang = \'' . $lang . '\'';

// Build the query
$query = '
SELECT 
  MM.ID as master_ID,
  MM.user_ID,
  M.upload_date,
  M.filename,
  M.extension,
  M.type,
  M.subtype,
  M.size,
  M.ID AS media_ID,
  M.license_ID,
  M.lang,
  M.title,
  M.trackback,
  V.provider_ID
FROM ' . $db->prefix . 'fabmedia_masters AS MM
LEFT JOIN ' . $db->prefix . 'fabmedia AS M
    ON MM.ID = M.master_ID
LEFT JOIN ' . $db->prefix . 'fabmedia_videos AS V
    ON V.fabmedia_ID = M.ID
WHERE
    (
        M.ID = ' . ( (int)  $filterKeyword) . ' OR
        M.title LIKE \'%' . $filterKeyword . '%\' OR
        M.filename LIKE \'%' . $filterKeyword . '%\' OR
        M.tags LIKE \'%' . $filterKeyword . '%\' OR
        M.description LIKE \'%' . $filterKeyword . '%\'
    )
    ' . $queryLang . '
    ' . $isModified. '
    ' . $orderBy . '
    ' . $limit . ';';




if (!$result = $db->query($query)){
    echo '
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">Not found</h3>
            </div>
            <div class="panel-body">Query error. <pre>' . $query . '</pre></div>
        </div>';

    return;
}

if (!$db->affected_rows){
    echo '
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Not found</h3>
            </div>
            <div class="panel-body">No media was found.</div>
        </div>';

    return;
}

$numRows = $db->affected_rows;

$i = 0;
while ($row = mysqli_fetch_assoc($result)){

    if ($i === 0)
        echo '<div class="row">';

    $i++;

    $spanLanguage = '<span style="position: absolute; top: 0; right: 20px; background-color: #2E2E2E; color: white; padding: 4px">' . $row['lang'] . '</span>';

    switch ($row['type'])
    {
       case 'image':
           $pos = strrpos(utf8_encode($row['filename']), '.' . $row['extension']);
           $imageThumbPath = substr_replace(utf8_encode($row['filename']), '_thumb.' . $row['extension'], $pos, strlen('.' . $row['extension']));
           $thumbnailPath = $URI->getBaseUri(true) . 'fabmedia/' . $row['user_ID'] . '/' . $imageThumbPath;

           echo '<div class="col-md-2">
                     ' . $spanLanguage . '
                     <img id="fabMediaFile_' . $row['media_ID'] . '" 
                     class="img-thumbnail" 
                     onclick="fabMediaGetInfo(' . $row['media_ID'] . ', \'image\')" 
                     src="' . $thumbnailPath . '" /> <br/>
                     ' . utf8_encode($row['filename']) . '
                 </div>';
           break;
       case 'video':
           echo '<div class="col-md-2">
                     <img id="fabMediaFile_' . $row['media_ID'] . '" 
                          class="img-thumbnail" 
                          onclick="fabMediaGetInfo(' . $row['media_ID'] . ', \'video\')" 
                          src="https://img.youtube.com/vi/' . $row['provider_ID'] .'/2.jpg"/ > <br/>
                     ' . $row['title'] . '
                 </div>';
           break;
       default:
           echo '<div class="col-md-2">
                     <img id="fabMediaFile_' . $row['media_ID'] . '" 
                          class="img-thumbnail" 
                          onclick="fabMediaGetInfo(' . $row['media_ID'] . ', \''.  $row['type'] .'\')" 
                          src="' . $URI->getBaseUri(true)  . '/modules/fabmediamanager/icons/archive.png"/> <br/>
                     ' . $row['filename'] . '
                 </div>';
   }

    if ($i === 6){
        echo '</div>';
        $i = 0;
    }
}

$bootstrapLeft = ( 12 - ( $i * 2)) / 2;
if ($bootstrapLeft > 0) {
    for ($i = 0; $i < $bootstrapLeft; $i++){
        // I apologize for such waste of columns.
        // And for such waste of comments.
        echo '<div class="col-md-2"></div>';
    }
    echo '</div>';
}

echo '
<script type="text/javascript">

    
    function fabMediaGetInfo(ID, type) {
        
        tinymce.execCommand(\'mceRemoveEditor\', true, \'fabmediamanager_description\');
        
        $.post( "' . $URI->getBaseUri() . 'fabmediamanager/getinfo/", { ID: ID, 
                                                                        type: type,
                                                                        fabMediaModule: \'' . $fabMedia->module . '\',     
                                                                        customButton: \'' . $_POST['customButtons'] . '\' 
                                                                       })
            .done(function( data ) {              
                $("#fabMediaInfo").html( data );

                 
                tinymce.init({
                    selector: \'#fabmediamanager_description\'
                });
            });
        
    }
</script>';