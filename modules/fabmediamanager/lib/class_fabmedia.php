<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/09/2015
 * Time: 12:46
 */

namespace CrisaSoft\FabCMS;

class FabMedia
{

    public $customButtons = [];
    public $module;

    function renderCustomButton()
    {
        if (count($this->customButtons) == 0) {
            return;
        }

        $buttons = '';
        foreach ($this->customButtons as $button) {
            $buttons .= '
                <button type="button" class="btn btn-default float-right" aria-label="Send FabCode" onclick="' . $button[1] . '">
                    <span class="glyphicon glyphicon-' . $button[4] . '" aria-hidden="true"></span>' . $button[0] . '
                 </button>';

        }

        return $buttons;
    }

    function searchMedia($keyword)
    {
        global $core;
        global $db;
        global $user;
        global $URI;

        $keyword = $core->in($keyword, true);

        $query = '
          SELECT 
            FM.title,
            FM.tags,
            FM.ID,
            FM.user_ID,
            FM.type,
            FM.extension,
            FM.filename
          FROM ' . $db->prefix . 'fabmedia AS FM
          LEFT JOIN ' . $db->prefix .'fabmedia_masters AS FMASTER
            ON FM.master_ID = FMASTER.ID
          WHERE 
          FM.enabled = 1 AND
          (FMASTER.filename     LIKE \'%' . $keyword . '%\'
            OR  FM.title        LIKE   \'%' . $keyword . '%\'
            OR FM.tags LIKE \'%' . $keyword . '%\'
            OR FM.description LIKE \'%' . $keyword . '%\'
          )
          LIMIT 20;';

        if (!$result = $db->query($query)) {
            $data = ['status' => 500];

            return json_encode($data);
        }

        if (!$db->affected_rows) {
            // Status:404
            $data = ['status' => 404];

            return json_encode($data);
        } else {
            // Status 200
            $data = ['status' => 200];

            while ($row = mysqli_fetch_array($result)) {
                $element['media'][] = [
                    'ID'       => $row['ID'],
                    'type'     => $row['type'],
                    'user_ID'  => $row['user_ID'],
                    'filename' => $row['filename'],
                    'ext'      => $row['extension'],
                ];

            }
            $data = array_merge($data, $element);

            return json_encode($data);
        }
    }

    function getThumbnailPath($filename, $extension, $user_ID)
    {
        global $URI;
        $imagePath = $URI->getBaseUri(true) . 'fabmedia/' . $user_ID . '/' . $filename;
        $pos = strrpos($imagePath, '.' . $extension);

        return substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
    }

    function renameMedia($ID, $newName)
    {
        global $core;
        global $db;
        global $conf;
        global $user;
        global $relog;

        $ID = (int)$ID;

        $query = 'SELECT MEDIA.filename,
                         MEDIA.ID,
                         MEDIA.extension,
                         MEDIA.user_ID
                  FROM ' . $db->prefix . 'fabmedia AS MEDIA
                  LEFT JOIN ' . $db->prefix . 'fabmedia_masters AS MASTER
                    ON MASTER.ID = MEDIA.master_ID 
                  WHERE MEDIA.ID = \'' . $ID . '\'  
                  AND MEDIA.user_ID = ' . $user->ID . ' 
                  LIMIT 1';

        if (!$result = $db->query($query)) {
            echo 'Error. Query error while selecting. ' . $query;

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_rename_query_error',
                           'details'   => 'Cannot excec the query. ' . $query,
            ]);

            return false;
        }

        if (!$result = $db->affected_rows) {
            echo 'Error. No file found.';

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_rename_media_no_file',
                           'details'   => 'Rename. Unable to find any file to rename with the query. ' . $query,
            ]);

            return false;
        }
        $row = mysqli_fetch_assoc($result);

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_rename_request_file',
                       'details'   => 'Rename. Requesting file. ' . $query,
        ]);

        $filename = $row['filename'];

        $imagePath = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $filename;
        $imagePathOriginal = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $filename;
        $extension = $row['extension'];
        $pos = strrpos($imagePath, '.' . $extension);

        /* Those files are the original files */
        $imageOriginalPath = substr_replace($imagePath, '_original.' . $extension, $pos, strlen('.' . $extension));

        $imageFinalMQPath = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
        $imageFinalLQPath = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

        $imageThumbPath = substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
        $imageThumbMQPath = substr_replace($imagePath, '_original_mq.' . $extension, $pos, strlen('.' . $extension));
        $imageThumbLQPath = substr_replace($imagePath, '_original_lq.' . $extension, $pos, strlen('.' . $extension));

        /* Those files are the new files */
        $imagePath = $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $newName;
        $pos = strrpos($imagePath, '.' . $extension);
        $imageOriginalPath_destination = substr_replace($imagePath, '_original.' . $extension, $pos, strlen('.' . $extension));

        $imageFinalMQPath_destination = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
        $imageFinalLQPath_destination = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

        $imageThumbPath_destination = substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
        $imageThumbMQPath_destination = substr_replace($imagePath, '_original_mq.' . $extension, $pos, strlen('.' . $extension));
        $imageThumbLQPath_destination = substr_replace($imagePath, '_original_lq.' . $extension, $pos, strlen('.' . $extension));

        // Move all the files
        try {
            rename($imagePathOriginal, $conf['path']['baseDir'] . 'fabmedia/' . $row['user_ID'] . '/' . $newName);
            rename($imageOriginalPath, $imageOriginalPath_destination);
            rename($imageFinalMQPath, $imageFinalMQPath_destination);
            rename($imageFinalLQPath, $imageFinalLQPath_destination);
            rename($imageThumbPath, $imageThumbPath_destination);
            rename($imageThumbMQPath, $imageThumbMQPath_destination);
            rename($imageThumbLQPath, $imageThumbLQPath_destination);
        } catch (Exception $e) {
            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_rename_error',
                           'details'   => 'Cannot rename',
            ]);
            echo 'Error while renaming: ', $e->getMessage(), "\n";
        }

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_rename_step_info',
                       'details'   => $imageOriginalPath . '->' . $imageOriginalPath_destination,
        ]);

        // Update the row
        $query = 'UPDATE ' . $db->prefix . 'fabmedia 
                  SET filename = \'' . $newName . '\' 
                  WHERE ID = ' . $row['ID'] . ' 
                  LIMIT 1';

            if (!$db->query($query)) {
            echo 'Query error while updating.' . $query;

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_rename_query_error',
                           'details'   => 'Unable to update the database. ' . $query,
            ]);

            return false;
        }

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_rename_ok',
                       'details'   => 'Renamed file with ID . ' . $row['ID'] . ' to ' . $filename,
        ]);
        echo 'ok';

        return true;
    }

    public function upload()
    {
        global $user;
        global $conf;
        global $db;
        global $core;
        global $relog;

        /**
         * upload.php
         *
         * Copyright 2013, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */

        // Make sure file is not cached (as it happens for example on iOS devices)

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        @set_time_limit(6 * 60);

        // Check if directory fabmedia exists, otherwise attempt to create
        $theDir = $conf['path']['baseDir'] . 'fabmedia/';
        if (!is_dir($theDir)) {
            $relog->write(['type' => '1', 'module' => 'FABMEDIAMANAGER', 'operation' => 'famedia_upload_directory_not_exists', 'details' => 'Directory ' . $conf['path']['baseDir'] . 'fabmedia/' . ' not exists.']);

            if (!mkdir($theDir, 0777, true)) {
                $relog->write(['type' => '3', 'module' => 'FABMEDIAMANAGER', 'operation' => 'famedia_upload_directory_cannot_create', 'details' => 'Directory ' . $conf['path']['baseDir'] . 'fabmedia/' . ' cannot be created.']);

                return;
            } else {
                $relog->write(['type' => '1', 'module' => 'FABMEDIAMANAGER', 'operation' => 'famedia_upload_directory_cannot_create', 'details' => 'Directory ' . $conf['path']['baseDir'] . 'fabmedia/' . ' created.']);
            }
        }

        //$cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } else if (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $fileName = utf8_decode(strtolower(str_replace(' ', '-',$_FILES['file']['name'])));

        if (empty($fileName)) {
            $relog->write(['type'      => '4',
                'module'    => 'FABMEDIAMANAGER',
                'operation' => 'fabmedia_upload_error_no_filename',
                'details'   => 'Tried to upload an empty filename. Filename is ' . $fileName]);
            return;
        }
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $extension = $core->in($extension, true);

        // Security check
        if (in_array($extension, ['php', 'php3', 'html' . 'htm', 'exe', 'cgi', 'js', 'java', 'cgi'])) {

            $relog->write(['type'      => '4',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_upload_security_extension',
                           'details'   => 'Tried to upload an ' . $extension . ' file. User was ' . $user->ID]);

            return;
        }

        $filePath = $conf['path']['baseDir'] . "fabmedia/" . $user->ID . '/' . $fileName;

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_start_upload_info',
                       'details'   => sprintf('Start file upload. Filename: %s, extension: %s, path: %s ', $fileName, $extension, $filePath)]);

        // Check if target exist, if yes abort
        if (file_exists($filePath)) {

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_file_exists_error',
                           'details'   => sprintf('File %s already exists. Aborting', $filePath),
            ]);

            die('{"jsonrpc" : "2.0", "error" : {"code": 166, "message": "File exists."}, "id" : "id"}');
        }

        // Create target dir
        $targetDir = dirname($filePath);
        if (!is_dir($targetDir)) {
            if (!@mkdir($targetDir, 0777, true)) {

                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_create_directory_error',
                               'details'   => sprintf('Unable to create the directory %s ', $targetDir)]);

                die('{"jsonrpc" : "2.0", "error" : {"code": 166, "message": "Directory exists."}, "id" : "id"}');
            } else {

                $relog->write(['type'      => '1',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_directory_exists_info',
                               'details'   => sprintf('Directory already exists: %s ', $targetDir)]);
            }
        }

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $cleanupTargetDir = true;

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {

                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_remove_old_files_error',
                               'details'   => sprintf('Failed to remove old files in %s ', $targetDir)]);

                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }

        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {


            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_remove_open_part_error',
                           'details'   => 'Failed to open part']);

            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {

                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_move_uploaded_file_error',
                               'details'   => 'failed to move uploaded file.' .
                                   $_FILES["file"]["error"] . ' || ' .
                                   $_FILES["file"]["tmp_name"]]);

                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {

                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_open_input_stream_error',
                               'details'   => 'Failed to open input stream.',
                ]);

                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_open_input_stream_error_generic',
                               'details'   => 'Failed to open input stream (generic).',
                ]);
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }

        // Now it's time to process the file
        switch ($extension) {
            case 'jpeg':
            case 'jpg' :
            case 'png' :
            case 'gif' :
            case 'webp':
                $this->processImage($filePath, $extension);
                break;
            case 'zip' :
            case 'rar' :
            case '7z'  :
            case 'tar' :
            case 'gz'  :
            case 'tar.gz':
                $this->processGeneric('archive', $filePath, $extension);
                break;
            default:    // Process custom type
                $this->processGeneric('custom', $filePath, $extension);
                break;
        }

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_upload_ok',
                       'details'   => 'Upload finished',
        ]);

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }

    function getMediaTrackback($filename)
    {

    }

    function processImage($imagePath, $extension)
    {
        global $core;
        global $user;
        global $db;
        global $relog;

        // Security check #1 if user file is PHP or HTML or JS block it
        if (in_array($extension, ['php', 'js', 'html', 'xhtml', 'htm'])) {
            $relog->write(['type'      => '4',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_process_image_filetype_error',
                           'details'   => 'Extension not allowed: ' . $extension,
            ]);
            die ('This kind of file is not processed, sorry');
        }


        // Basic security check against MySQL-Injections
        $imagePath = str_replace("'", "", $imagePath);
        $extension = str_replace("'", "", $extension);

        // Get the exif data if the image is jpeg. This is done before the original image is renamed.
        if ($extension == 'jpeg' || $extension == 'jpg') {
            $size = getimagesize($imagePath, $info);

            if (isset($info['APP13'])) {
                $iptc               =   iptcparse($info['APP13']);
                $title              =   $iptc['2#005'][0];
                $tags               =   $iptc['2#025'][0];
                $copyright          =   $iptc['2#116'][0];
                $description        =   $iptc['2#120'][0];
                $descriptionWriter  =   $iptc['2#122'][0];
                $location           =   $iptc['2#092'][0];
                $source             =   $iptc['2#115'][0];
                $credits            =   $iptc['2#110'][0];
                $author             =   $iptc['2#110'][0];
            }
        }

        if (empty($title))
            $title = ucfirst(str_replace('-', ' ', basename($imagePath, '.' . $extension)));

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $source_image = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $source_image = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $source_image = imagecreatefromgif($imagePath);
                break;
            case 'webp':
                $source_image = imagecreatefromwebp($imagePath);
                break;
        }

        $width      =   imagesx($source_image);
        $height     =   imagesy($source_image);
        $fileSize   =   filesize($imagePath);

        /* create a new, "virtual" image, later we will create another image container for the original */
        $virtual_image = imagecreatetruecolor(180, 180);

        imagealphablending($virtual_image, false);
        imagesavealpha($virtual_image, true);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, 180, 180, $width, $height);

        $pos = strrpos($imagePath, '.' . $extension);

        if ($pos !== false) {
            // If the width of the image is more than 768 pixel, the image will be reduced, but the original one
            // will be preserved, and named img_original.ext
            if ($width > 768) {
                $ratio = $width / $height; // width/height
                if ($ratio > 1) {
                    $width_dest     =   768;
                    $height_dest    =   768 / $ratio;
                } else {
                    $width_dest     =   768 * $ratio;
                    $height_dest    =   768;
                }
                $final_image = imagecreatetruecolor($width_dest, $height_dest);

                imagealphablending($final_image, false);
                imagesavealpha($final_image, true);

                imagecopyresampled($final_image, $source_image, 0, 0, 0, 0, $width_dest, $height_dest, $width, $height);
            } else {
                $final_image = imagecreatetruecolor($width, $height);

                imagealphablending($final_image, false);
                imagesavealpha($final_image, true);

                imagecopyresampled($final_image, $source_image, 0, 0, 0, 0, $width, $height, $width, $height);
            }

            $imageOriginalPath  = substr_replace($imagePath, '_original.' . $extension, $pos, strlen('.' . $extension));
            $imageFinalMQPath   = substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));
            $imageFinalLQPath   = substr_replace($imagePath, '_lq.' . $extension, $pos, strlen('.' . $extension));

            $imageThumbPath     = substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
            $imageThumbMQPath   = substr_replace($imagePath, '_original_mq.' . $extension, $pos, strlen('.' . $extension));
            $imageThumbLQPath   = substr_replace($imagePath, '_original_lq.' . $extension, $pos, strlen('.' . $extension));
        }

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                // Rename original image
                rename($imagePath, $imageOriginalPath);

                // Save resized image with various quality
                imagejpeg($final_image, $imagePath, 85);
                imagejpeg($final_image, $imageFinalMQPath, 75);
                imagejpeg($final_image, $imageFinalLQPath, 40);

                // Save not resized image with various quality
                imagejpeg($virtual_image, $imageThumbPath, 60);
                imagejpeg($source_image, $imageThumbMQPath, 40);
                imagejpeg($source_image, $imageThumbLQPath, 25);
                break;
            case 'png':
                rename($imagePath, $imageOriginalPath);

                // Transparency
                imagealphablending($final_image, false);
                imagesavealpha($final_image, true);

                imagealphablending($virtual_image, false);
                imagesavealpha($virtual_image, true);

                imagealphablending($source_image, false);
                imagesavealpha($source_image, true);

                // Save not resized image with various quality
                imagepng($final_image, $imagePath, 8);
                imagepng($final_image, $imageFinalMQPath, 7);
                imagepng($final_image, $imageFinalLQPath, 4);

                // Save resized image with various quality
                imagepng($virtual_image, $imageThumbPath, 6);
                imagepng($source_image, $imageThumbMQPath, 4);
                imagepng($source_image, $imageThumbLQPath, 9);
                break;
            case 'webp':
                rename($imagePath, $imageOriginalPath);

                // Transparency
                imagealphablending($final_image, false);
                imagesavealpha($final_image, true);

                imagealphablending($virtual_image, false);
                imagesavealpha($virtual_image, true);

                imagealphablending($source_image, false);
                imagesavealpha($source_image, true);

                // Save not resized image with various quality
                imagewebp($final_image, $imagePath, 100);
                imagewebp($final_image, $imageFinalMQPath, 60);
                imagewebp($final_image, $imageFinalLQPath, 30);

                // Save resized image with various quality
                imagewebp($virtual_image, $imageThumbPath, 100);
                imagewebp($source_image, $imageThumbMQPath, 60);
                imagewebp($source_image, $imageThumbLQPath, 30);
                break;

            case 'gif':
                rename($imagePath, $imageOriginalPath);
                imagegif($final_image, $imagePath);
                imagegif($virtual_image, $imageThumbPath);
                break;
        }

        $query = '
        INSERT INTO ' . $db->prefix  . 'fabmedia_masters 
        (
            user_ID
        )
        VALUES
        (
            \'' . $user->ID . '\' 
        )';

        if (!$db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_image_db_error',
                           'details'   => 'Query error while inserting into DB ' . $query,
            ]);

            echo 'Query error. <br/> . <pre>' . $query . '</pre>';
            return false;
        }

        $master_ID = $db->lastInsertID;

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_insert_image_ok',
                       'details'   => 'Image pushed to DB ',
        ]);


        // Build the query
        $query = '
        INSERT into ' . $db->prefix . 'fabmedia
        (
            master_ID,       
            user_ID,
            filename,
            extension,
            type,
            subtype,
            upload_date,
            license_ID,
            lang,
            modified,
            modify_date,
            title,
            trackback,
            author,
            link,
            tags,
            description,
            indexable,
            global_available,
            enabled
        )
        VALUES
        (
            \'' . $master_ID . '\',         /* Master */
            \'' . $user->ID . '\',          /* user_ID */

            \'' . basename($imagePath) . '\',
            \'' . $extension . '\',
            \'image\',
            \'\',
            \'' . date('Y-m-d') . '\',
            NULL,                           /* License ID */
            NULL,                           /* Lang */
            NULL,                           /* modified */
            NULL,                           /* modify date */
            \'' . $core->in($title) . '\',              /* title */
            \'' . $core->getTrackback(basename($imagePath)) . '\',    /* trackback */
            \'' . $core->in($author) . '\',             /* author */
            \'\',               /* Link */
            \'' . $tags . '\',  /* Tags */
            \'' . $core->in($description) . '\', /* Description */
            0,      /* Indexable */
            1,      /* Global available */
            \'1\'   /* Enabled */
        )';

        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_image_db_error',
                           'details'   => 'Query error while inserting into DB ' . $query,
            ]);

            echo 'Query error. <br/> . <pre>' . $query . '</pre>';

            return;
        } else {
            $latestRow = $db->lastInsertID;

            $relog->write(['type'      => '1',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_image_db_ok',
                           'details'   => 'Query ok while inserting into DB ' . $query,
            ]);

            echo 'Upload OK';

            $query = '
            INSERT INTO ' . $db->prefix . 'fabmedia_images
            (
                file_ID,
                width,
                height,
                GPS
            )
            VALUES
            (
                \'' . $latestRow . '\',
                \'' . $core->in($width) . '\',
                \'' . $core->in($height) . '\',
                \'' . $core->in($location) . '\'
            );';

            if (!$db->query($query)) {

                $relog->write(['type'      => '3',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_insert_image_data_db_error',
                               'details'   => 'Query error while inserting image\'s additional data into DB ' . $query,
                ]);

            } else {
                $relog->write(['type'      => '1',
                               'module'    => 'FABMEDIAMANAGER',
                               'operation' => 'fabmedia_manager_insert_image_data_db_ok',
                               'details'   => 'Query ok while inserting image\'s additional data into DB ' . $query,
                ]);

            }
        }
    }

    function createMasterID()
    {
        global $db;
        global $relog;

        // Create the master
        $query = 'INSERT INTO ' . $db->prefix . 'fabmedia_masters 
                    (creation_date) 
                  VALUES (NOW());
                  ';

        if (!$db->query($query)){
            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_image_master_db_error',
                           'details'   => 'Unable to create the master ID for the image ' . $query,
            ]);
            return false;
        }

        return $db->lastInsertID;
    }

    /*
     * Process a generic file
     */
    function processGeneric($type, $filePath, $extension)
    {
        global $core;
        global $user;
        global $db;
        global $conf;
        global $relog;

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_process_image_info',
                       'details'   => sprintf('Processing generic of type %s with filepath %s and extension %s ', $type, $filePath, $extension),
        ]);

        $filePath = utf8_decode($filePath);
        $fileSize = filesize($filePath);

        $customData = $this->checkCustom($extension);

        if ($customData['error'] === true) {

            $relog->write(['type'      => '1',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_abort_process',
                           'details'   => sprintf('Aborting process of type %s with filepath %s and extension %s ', $type, $filePath, $extension),
            ]);

            unlink($filePath);

            return false;
        }

        //@todo: finalize the import plugin

        // Basic security check against MySQL-Injections        
        $filePath   =   str_replace("''", "", $filePath);
        $extension  =   str_replace("''", "", $extension);

        $query = '
        INSERT INTO ' . $db->prefix  . 'fabmedia_masters 
        (
            user_ID
        )
        VALUES
        (
            \'' . $user->ID . '\'
        )';

        if (!$db->query($query)){
            $relog->write(['type'      => '4',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_master_generic_db_error',
                           'details'   => 'Query error while inserting master generic into DB ' . $query,
            ]);

            echo 'Query error. <br/> . <pre>' . $query . '</pre>';
            return false;
        }
        $master_ID = $db->lastInsertID;

        $relog->write(['type'      => '1',
                       'module'    => 'FABMEDIAMANAGER',
                       'operation' => 'fabmedia_manager_insert_master_generic_ok',
                       'details'   => 'Master generic pushed to DB ',
        ]);

        // Build the query
        $query = '
        INSERT into ' . $db->prefix . 'fabmedia
        (
	         master_ID,
	         user_ID,
	         modified,
	         modify_date,
	         filename,
	         extension,
	         type,
	         subtype,     
	         title,
	         author,
	         link,
	         tags,
	         copyright,
	         description,
	         enabled
        )
        
        VALUES
        
        (
	        \'' . $master_ID . '\',
	        \'' . $user->ID . '\',
	        \'0\', /* Modified */
	        \'\', */Modify date */
	        \'' . basename($filePath) . '\', /* Filename */
	        \'' . $extension . '\', /* Extension */
	        \'' . $type . '\', /* Type */
	        \'\', /* Subtype */
	        \'\',
	        \'\',
	        \'\',
	        \'\',
	        \'\',
	        \'\',
	        \'1\'
        );';


        if (!$db->query($query)) {

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_generic_item_db_error',
                           'details'   => 'Query error while inserting into DB ' . $query,
            ]);

            echo 'Error.';

            return;
        } else {

            $relog->write(['type'      => '1',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_manager_insert_generic_item_db_info',
                           'details'   => 'Image has been inserted into DB ' . $query,
            ]);

            return 'OK';
        }
    }

    function checkCustom($extension)
    {
        global $db;
        global $core;
        global $relog;

        $extension = $core->in($extension, true);

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'fabmedia_custom_filetypes 
                  WHERE 
                    module = \'' . $this->module . '\'
                    AND extension = \'' . $extension . '\'';

        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '3',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_select_custom_file_query_error',
                           'details'   => 'Query error while checking for custom. ' . $query,
            ]);

            return ['error' => true];
        }

        if (!$db->affected_rows) {
            $relog->write(['type'      => '2',
                           'module'    => 'FABMEDIAMANAGER',
                           'operation' => 'fabmedia_select_custom_file_no_match_info',
                           'details'   => 'Cannot match the extension ' . $extension,
            ]);

            return ['error' => true];
        }

        $row = mysqli_fetch_array($result);

        return ['export' => $row['import_plugin']];
    }
}