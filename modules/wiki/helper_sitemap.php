<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 17/01/2017
 * Time: 11:17
 */

$query = '
SELECT * 
FROM ' . $db->prefix .'wiki_pages 
WHERE visible = 1
    AND (no_index != 1 OR no_index IS NULL)
    AND (no_search != 1 OR no_search IS NULL)
    AND (service_page != 1 OR service_page IS NULL)
    AND LENGTH(internal_redirect) < 1;';



if (!$result = $db->query($query)){
    $relog->write(['type'      => '4',
                   'module'    => 'WIKI',
                   'operation' => 'wiki_helper_sitemap_select_pages_query_error',
                   'details'   => 'Cannot select pages. Query error. ' . $query,
    ]);

    return;
}

if (!$db->affected_rows) {
    $log->write('info','Sitemap:wiki','No result: ' . $query);
    return;
}

while ($row = mysqli_fetch_assoc($result)){
    $return .= '<url>
    <loc>' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $row['trackback'] .'/</loc>';

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'wiki_pages_files 
              WHERE page_ID = ' . $row['ID'] . '
              AND type = \'image\'';

    

    if (!$resultImage = $db->query($query)){

        $relog->write(['type'      => '4',
                       'module'    => 'WIKI',
                       'operation' => 'wiki_helper_sitemap_image_query_error',
                       'details'   => 'Cannot select the images. Query error. ' . $query,
        ]);

    } else {
        if ($db->affected_rows){
            while ( $rowImage = mysqli_fetch_assoc($resultImage)){
                $return .= PHP_EOL . '<image:image>
                                <image:loc><![CDATA[' . $URI->getBaseUri(true) . $rowImage['filename'] . ']]></image:loc>
                                <image:caption><![CDATA[' . $rowImage['title'] . ']]></image:caption>
                            </image:image>';
            }
        }
    }

    $return .= '
</url>';
}

$this->result .= $return;