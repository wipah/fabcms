<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/04/2017
 * Time: 16:20
 */

function plugin_latest_pages($dataArray)
{
    global $db;
    global $core;
    global $user;
    global $URI;
    global $conf;
    global $module;
    global $fabwiki;
    global $relog;

    if (!isset($fabwiki))
    {
        require_once $conf['path']['baseDir'] . 'modules/wiki/lib/class_wiki.php';

        $fabwiki = new wiki();
        $fabwiki->loadConfig();
    }


    if ($user->isAdmin)
    {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded)
        return $return;

    $where = ' P.language = \'' . $core->shortCodeLang . '\' AND P.visible = 1 AND P.service_page != 1 AND (P.internal_redirect IS NULL OR P.internal_redirect = \'\')';

    if (isset($dataArray['orderBy']))
    {
        $orderBy = ' ORDER BY ' . $core->in($dataArray['orderBy']);
    } else {
        $orderBy = ' ORDER BY P.ID DESC';
    }

    if (isset($dataArray['tag']))
        $where .= ' AND T.tag = \'' . $core->in($dataArray['tag']) . '\'';

    if (isset($dataArray['lang']))
        $where .= ' AND P.language = \'' . $core->in($dataArray['lang']) . '\'';

    if (isset($dataArray['category']))
        $where .= ' AND P.category_ID = \'' . (int)$dataArray['category'] . '\'';

    if (isset($dataArray['minLenght']))
        $where .= ' AND LENGTH(P.content) > ' . (int)$dataArray['minLength'] . '';

    if (isset($dataArray['minWords']))
        $where .= ' AND ST.words > ' . (int)$dataArray['minWords'] . '';

    if (isset($dataArray['limit'])) {
        $limit = ' LIMIT ' . (int)$dataArray['limit'] . ';';
    } else {
        $limit = ' LIMIT 6;';
    }

    if (!empty($fabwiki->publishedID)) {
        $excludedID = implode(', ', $fabwiki->publishedID);
        $where .= ' AND P.ID NOT IN (' . $excludedID . ') ';
    }

    $query = '
    SELECT P.ID,
           P.title, 
           P.trackback,
           P.image,
           P.metadata_description,
           P.short_description,
           T.tag
    FROM   ' . $db->prefix . 'wiki_pages AS P
    LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
        ON T.page_ID = P.ID
    LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics AS ST
         ON P.ID = ST.page_ID
    WHERE ' . $where . ' AND visible = 1
    GROUP BY P.ID
    ' . $orderBy . ' 
    ' . $limit;

    $db->setQuery($query);
    if (!$result = $db->executeQuery('select')) {

        $relog->write(['type'      => '4',
                       'module'    => 'WIKI',
                       'operation' => 'wiki_plugin_latest_pages',
                       'details'   => 'Cannot select latest pages. Query error. ' . $query,
                      ]);

        return 'Query error. ';
    }


    $numResult = $db->affected_rows;

    $output = PHP_EOL . PHP_EOL . '<!-- Start of latest page-->';

    !empty($dataArray['rowColor'])      ? $rowColor         = 'background-color: ' . $core->in($dataArray['rowColor'], true) . '; ' : $rowColor = '';
    !empty($dataArray['rowPadding'])    ? $rowPadding       = 'padding: ' . (int)$dataArray['rowPadding'] . 'px; ' : $rowPadding = '';
    !empty($dataArray['rowMarginTop'])  ? $rowMarginTop     = 'margin-top: ' . (int)$dataArray['rowMarginTop'] . 'px; ' : $rowMarginTop = '';
    !empty($dataArray['maxImageWidth']) ? $maxImageWidth    = (int)$dataArray['maxImageWidth']  : $maxImageWidth = '100%';
    !empty($dataArray['rowClass'])      ? $rowClass         = $core->in($dataArray['rowClass'], true) . 'px' : $rowClass = '';

    !empty($dataArray['rowColor'])   ? $rowColor    = $core->in($dataArray['rowColor'], true) : $rowColor = '#eee';
    !empty($dataArray['rowPadding']) ? $rowPadding = (int)$dataArray['rowPadding'] . 'px' : $rowPadding = '8px';

    if (isset($dataArray['gridSize'])) {
        $gridSize = (int) $dataArray['gridSize'];
    } else {
        $gridSize = 4;
    }




    $i = 0;
    $tot = 0;
    $colSize = 12 / $gridSize;
    switch ($dataArray['outputType']) {
        case 'thumbOnLeft':

            $output .= PHP_EOL . '<!-- Open latest page --> ' . PHP_EOL .' <div class="row rowThumbOnTop ' . $rowClass . '" 
                                       style="' . $rowColor . $rowPadding . '">';

            while ($row = mysqli_fetch_assoc($result)) {
                $fabwiki->publishedID[] = $row['ID'];
                $i++;
                $tot++;

                if ( (int) $dataArray['appendDescription'] === 1) {

                    // If additional text is not found the engine takes the text from metadata description
                    if (strlen($row['short_description']) < 10 ) {
                        $additionalText = $row['metadata_description'];
                    } else {
                        $additionalText = $row['short_description'];
                    }

                    $additionalText = '&nbsp;' . str_replace(['<p>','</p>'], ['', ''], $additionalText);

                }

                $output .= '<!-- Opening column --><div class="col-lg-' . $colSize . '">';
                $imagePath = $conf['path']['baseDir'] . $row['image'];

                if (file_exists($imagePath) && !is_dir($imagePath)) {

                    $imageInfo = (pathinfo($row['image']));
                    $extension = $imageInfo['extension'];

                    $pos = strrpos($row['image'], '.' . $extension);

                    $imagePath = substr_replace($row['image'], '_original_lq.' . $extension, $pos, strlen('.' . $extension));

                    $imgTag = '
                                    <img style="max-height: 300px; max-width:' . $maxImageWidth . ';" src="' . $URI->getBaseUri(true) . $imagePath . '" 
                                         class="img-fluid" 
                                         alt="' . optimizeTitle($row['title']) . ' /">
                                ';
                } else {
                    $imgTag = '<img style="max-height: 300px; max-width:' . $maxImageWidth . '" src="' . $URI->getBaseUri(true) . 'modules/wiki/res/noimage.png" class="img-fluid" alt="No image placeholder" />';
                }


                $output .= '
                <article class="fabCmsArticle">
                
                    <div class="FabCms-Wiki-item float-left">
                        <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                            <span class="FabCms-Wiki-notify-badge">' . $row['tag'] . '</span>
                            ' . $imgTag . '
                        </a>
                    </div>
                    
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <h3>' . optimizeTitle($row['title']) . '</h3>
                    </a>
                    
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <span class="FabCms-Wiki-articleSmallText" style="margin-left: 4px;">' . $additionalText . '</span>
                    </a>
                </article>
                </div>';

                if ($i === $gridSize && $tot !== $numResult) {
                    $i = 0;
                    $output .= '</div> <!-- end row-->
                        <div class="row" style="background-color:' . $rowColor . '; padding: ' . $rowPadding . ';"> <!-- Start new row -->';
                }
            }


            if ($numResult < $i) {
                for ($x = 0; $x < ($gridSize - $i); $x++) {
                    $output .= '<div class="col-lg-' . $colSize . '"><!--empty--></div>';
                }
            }

            $output .= '</div> <!-- Close latest page -->';

            break;
        case 'thumbOnRight':

            $output .= PHP_EOL . '<!-- Open latest page --> ' . PHP_EOL .' <div class="row rowThumbOnTop ' . $rowClass . '" 
                                       style="' . $rowColor . $rowPadding . '">';

            while ($row = mysqli_fetch_assoc($result)) {
                $fabwiki->publishedID[] = $row['ID'];
                $i++;
                $tot++;

                if ( (int) $dataArray['appendDescription'] === 1) {

                    // If additional text is not found the engine takes the text from metadata description
                    if (strlen($row['short_description']) < 10 ) {
                        $additionalText = $row['metadata_description'];
                    } else {
                        $additionalText = $row['short_description'];
                    }

                    $additionalText = '&nbsp;' . str_replace(['<p>','</p>'], ['', ''], $additionalText);

                }

                $output .= '<!-- Opening column --><div class="col-lg-' . $colSize . '">';
                $imagePath = $conf['path']['baseDir'] . $row['image'];

                if (file_exists($imagePath) && !is_dir($imagePath)) {

                    $imageInfo = (pathinfo($row['image']));
                    $extension = $imageInfo['extension'];

                    $pos = strrpos($row['image'], '.' . $extension);

                    $imagePath = substr_replace($row['image'], '_original_lq.' . $extension, $pos, strlen('.' . $extension));

                    $imgTag = '
                                    <img style="max-height: 300px; max-width:' . $maxImageWidth . ';" src="' . $URI->getBaseUri(true) . $imagePath . '" 
                                         class="img-fluid" 
                                         alt="' . optimizeTitle($row['title']) . ' /">
                                ';
                } else {
                    $imgTag = '<img style="max-height: 300px; max-width:' . $maxImageWidth . '" data-src="' . $URI->getBaseUri(true) . 'modules/wiki/res/noimage.png" class="lazyload img-fluid" alt="No image placeholder" />';
                }


                $output .= '
                <article class="fabCmsArticle">

                
                    <div class="FabCms-Wiki-item float-right">
                        <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                            <span class="FabCms-Wiki-notify-badge">' . $row['tag'] . '</span>
                            ' . $imgTag . '
                        </a>
                    </div>
                    
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <h3>' . optimizeTitle($row['title']) . '</h3>
                    </a>
                
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <span class="FabCms-Wiki-articleSmallText">' . $additionalText . '</span>
                    </a>
                </article>
                </div>';


                if ($i === $gridSize && $tot !== $numResult) {
                    $i = 0;
                    $output .= '</div> <!-- end row-->
                        <div class="row" style="background-color:' . $rowColor . '; padding: ' . $rowPadding . ';"> <!-- Start new row -->';
                }
            }

            if ($numResult < $i) {
                for ($x = 0; $x < ($gridSize - $i); $x++) {
                    $output .= '<div class="col-lg-' . $colSize . '"><!--empty--></div>';
                }
            }

            $output .= '</div> <!-- Close latest page -->';

            break;
        case 'thumbOnTop':

            $output .= PHP_EOL . '<!-- Open latest page --> ' . PHP_EOL .' <div class="row rowThumbOnTop ' . $rowClass . '" 
                                       style="' . $rowColor . $rowPadding . '">';

            while ($row = mysqli_fetch_assoc($result)) {
                $fabwiki->publishedID[] = $row['ID'];
                $i++;
                $tot++;

                if ( (int) $dataArray['appendDescription'] === 1) {

                    // If additional text is not found the engine takes the text from metadata description
                    if (strlen($row['short_description']) < 10 ) {
                        $additionalText = $row['metadata_description'];
                    } else {
                        $additionalText = $row['short_description'];
                    }

                    $additionalText = '&nbsp;' . str_replace(['<p>','</p>'], ['', ''], $additionalText);

                }

                $output .= '<!-- Opening column --><div class="col-lg-' . $colSize . '">';
                $imagePath = $conf['path']['baseDir'] . $row['image'];

                if (file_exists($imagePath) && !is_dir($imagePath)) {

                    $imageInfo = (pathinfo($row['image']));
                    $extension = $imageInfo['extension'];

                    $pos = strrpos($row['image'], '.' . $extension);

                    $imagePath = substr_replace($row['image'], '_original_lq.' . $extension, $pos, strlen('.' . $extension));

                    $imgTag = '
                                    <img style="max-height: 300px; max-width:' . $maxImageWidth . ';" src="' . $URI->getBaseUri(true) . $imagePath . '" 
                                         class="img-fluid" 
                                         alt="' . optimizeTitle($row['title']) . ' /">
                                ';
                } else {
                    $imgTag = '<img style="max-height: 300px; max-width:' . $maxImageWidth . '" data-src="' . $URI->getBaseUri(true) . 'modules/wiki/res/noimage.png" class="lazyload img-fluid" alt="No image placeholder" />';
                }



                $output .= '
                <article class="fabCmsArticle">
                
                    <div class="FabCms-Wiki-item">
                        <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                            <span class="FabCms-Wiki-notify-badge">' . $row['tag'] . '</span>
                            ' . $imgTag . '
                        </a>
                    </div>
                    
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <h3>' . optimizeTitle($row['title']) . '</h3>
                    </a>
                    
                    <a href="' . $URI->getBaseUri() . 'wiki/' . $row['trackback'] . '/">
                        <span class="FabCms-Wiki-articleSmallText">' . $additionalText . '</span>
                    </a>
                </article>
                </div>';


                if ($i === $gridSize && $tot !== $numResult) {
                    $i = 0;
                    $output .= '</div> <!-- end row-->
                        <div class="row" style="background-color:' . $rowColor . '; padding: ' . $rowPadding . ';"> <!-- Start new row -->';
                }
            }


            if ($numResult < $i) {
                for ($x = 0; $x < ($gridSize - $i); $x++) {
                    $output .= '<div class="col-lg-' . $colSize . '"><!--empty--></div>';
                }
            }

            $output .= '</div> <!-- Close latest page -->';
            break;
        case 'titleOnTop':

            while ($row = mysqli_fetch_assoc($result)) {

                $fabwiki->publishedID[] = $row['ID'];

                if ( (int) $dataArray['appendDescription'] === 1) {

                    // If additional text is not found the engine takes the text from metadata description
                    if (strlen($row['short_description']) < 10 ) {
                        $additionalText = $row['metadata_description'];
                    } else {
                        $additionalText = $row['short_description'];
                    }

                    $additionalText = '&nbsp;' . str_replace(['<p>','</p>'], ['', ''], $additionalText);

                }

                $output .= '<p class="subHead">
                            <h2 class="subHead">
                                <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $row['trackback'] . '/">' . optimizeTitle($row['title']) . '</a>
                            </h2>';


                $output = '</p>';

            }
            break;
        case 'styledList':
            while ($row = mysqli_fetch_assoc($result)) {
                $fabwiki->publishedID[] = $row['ID'];

                if ( (int) $dataArray['appendDescription'] === 1) {

                    // If additional text is not found the engine takes the text from metadata description
                    if (strlen($row['short_description']) < 10 ) {
                        $additionalText = $row['metadata_description'];
                    } else {
                        $additionalText = $row['short_description'];
                    }

                    $additionalText = '&nbsp;' . str_replace(['<p>','</p>'], ['', ''], $additionalText);

                }

                if ((int)$dataArray['stripNamespace'] === 1) {
                    $title = end(explode($fabwiki->config['nameSpaceSeparator'], optimizeTitle($row['title'])));
                } else {
                    $title = optimizeTitle($row['title']);
                }

                $output .= '<div class="' . $core->in($dataArray['customClass'], true) . '">
                                <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $row['trackback'] . '/">' . $title . '</a>' . $additionalText . '
                            </div>';
            }
            break;
        case 'simpleList':
        default:
            while ($row = mysqli_fetch_assoc($result)) {
                $fabwiki->publishedID[] = $row['ID'];
                $output .= '&bull; <a href="' . $URI->getBaseUri() . $core->router->getRewriteAlias('wiki') . '/' . $row['trackback'] . '/">' . optimizeTitle($row['title']) . '</a> <br/>';
            }
    }

    return $output;
}

function optimizeTitle ($title) {
    global $fabwiki;
    $separator = $fabwiki->config['nameSpaceSeparator'];

    $trackback = explode($separator, $title);
    $trackback = array_reverse($trackback);

    return $trackback[0];
}