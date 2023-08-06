<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 06/09/2015
 * Time: 12:46
 * @copyright Fabrizio Crisafulli
 * @author Fabrizio Crisafulli
 *
 */

namespace CrisaSoft\FabCMS;

/**
 * Class FabMedia
 * @package CrisaSoft\FabCMS
 */
class FabMedia
{
    public $customButtons = [];
    public $module;

    /**
     * @param $keyword
     *
     * @return false|string
     */
    function searchMedia(string $keyword) :string
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
            FM.filename,
            FM.trackback
        FROM ' . $db->prefix . 'fabmedia AS FM
        LEFT JOIN ' . $db->prefix .'fabmedia_masters AS FMASTER
            ON FM.master_ID = FMASTER.ID
        WHERE 
            FM.enabled = 1
            AND LENGTH(FM.filename) > 0
            AND LENGTH(FM.extension) > 0
            AND
            (
                FM.filename       LIKE \'%' . $keyword . '%\'
                OR  FM.title      LIKE \'%' . $keyword . '%\'
                OR FM.tags        LIKE \'%' . $keyword . '%\'
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
                    'trackback' => $row['trackback'],
                    'ext'      => $row['extension'],
                ];

            }
            $data = array_merge($data, $element);

            return json_encode($data);
        }
    }

    /**
     * Get full path of the thumbnail
     * @param string $filename
     * @param string $extension
     * @param int    $user_ID
     *
     * @return string
     */
    function getThumbnailPath(string $filename, string $extension, int $user_ID) :string
    {
        global $URI;
        $imagePath = $URI->getBaseUri(true) . 'fabmedia/' . $user_ID . '/' . $filename;
        $pos = strrpos($imagePath, '.' . $extension);

        return substr_replace($imagePath, '_thumb.' . $extension, $pos, strlen('.' . $extension));
    }
}