<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 16/04/2016
 * Time: 23:34
 */

if (!$core->loaded)
    die();

$query = 'SELECT 
	F.TYPE, 
	F.filename, 
	F.ID, 
	M.user_ID 
FROM ' . $db->prefix . 'fabmedia_masters AS M
LEFT JOIN ' . $db->prefix . 'fabmedia AS F
    ON M.ID = F.master_ID
WHERE indexable = 1  
     AND LENGTH(F.trackback) > 0
     AND LENGTH(F.filename) > 0;';


if (!$result = $db->query($query)) {
    echo 'Error in FabMedia. ' . $query;
}

unset($row);

$this->result .= '              <url>
                                    <loc>' . $URI->getBaseUri() . $core->router->getRewriteAlias('fabmediamanager') . '/
                                    </loc>
                                 </url>' . PHP_EOL;


while ($row = mysqli_fetch_array($result)) {
    switch ($row['type']) {
        case 'image':
            $loc = $URI->getBaseUri() .  $core->router->getRewriteAlias('media') . '/showimage/' . $row['ID'] . '-' . $row['trackback'] . '/';
            $imgLoc = $URI->getBaseUri() . 'fabmedia/' . $row['user_ID'] . '/' . utf8_encode($row['filename']) ;

            $this->result .= "
                                <url>
                                    <loc>$loc</loc>
                                    <image:image>
                                        <image:title><![CDATA[{$row['title']}]]></image:title>
                                        <image:loc><![CDATA[$imgLoc]]></image:loc>
                                    </image:image>
                                </url>\n";
            break;
        default:

            if (strlen($row['trackback']) < 1) {
                continue 2;
            }

            $loc = $URI->getBaseUri() . 'media/showmedia/' . $row['ID'] . '-' . utf8_encode($row['trackback']) . '/';
            $this->result .= "<url>
                                <loc><![CDATA[$loc]]></loc>
                              </url>\n";
            break;
    }
}