<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!$core->loaded)
    die('Direct call detected.');

// Get the trackaback
if (!isset($path[2])) {
    $trackback = $core->getTrackback($fabwiki->config['homePageNamespace']);
} else {
    $trackback = $core->in($path[2], true);
}

// Check if a malformed uri has been passed
$lastPath = strtolower($path[count($path) - 1]);

if ($lastPath == 'www.google.it' || $lastPath == 'index.html' || $lastPath == 'index.htm') {
    header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $path[2] . '/');
    return;
}

// check if final slash has been passed
if ($charPos = strpos($_SERVER['REQUEST_URI'], '?')) {
    $char = substr($_SERVER['REQUEST_URI'], $charPos - 1, 1);

    if ($char !== '/') {
        $lastPath = substr($lastPath, 0, strpos($lastPath, '?'));
        $queryString = substr($_SERVER['REQUEST_URI'], $charPos);

        header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $lastPath . '/' . $queryString);
        return;
    }
} else {
    $lastChar = (substr($_SERVER['REQUEST_URI'], -1));

    if ($lastChar !== '/') {
        header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $path[2] . '/');
        return;
    }
}

echo '<style>' . $fabwiki->config['customCSS'] . '</style>';



if ((int)$core->getConfig('core', 'recaptchaEnabled') === 1) {
    /*
    $this->scripts .= '<script>
                        $( document ).ready(function() {
                        $.getScript("https://www.google.com/recaptcha/api.js", function(data, textStatus, jqxhr) {
                        console.log("*** Recapcha start ***");
                        console.log(data); //data returned
                        console.log(textStatus); //success
                        console.log(jqxhr.status); //200
                        console.log("Recapcha loaded!");
        });
        });
        </script>';

    $useRecaptacha = true;

    */
}

// Gets the page
$query = '
SELECT 
  ' . $db->prefix . 'wiki_pages.title,
  ' . $db->prefix . 'wiki_pages.title_alternative,
  ' . $db->prefix . 'wiki_pages.use_file,
  ' . $db->prefix . 'wiki_pages.trackback,
  ' . $db->prefix . 'wiki_pages.short_description,
  ' . $db->prefix . 'wiki_pages.metadata_description,
  ' . $db->prefix . 'wiki_pages.ID,
  ' . $db->prefix . 'wiki_pages.master_ID,
  ' . $db->prefix . 'wiki_pages.language,
  ( SELECT TAG.tag FROM ' . $db->prefix . 'wiki_pages_tags AS TAG WHERE TAG.page_ID = ' . $db->prefix . 'wiki_pages.ID ORDER BY ID ASC LIMIT 1) tag,
  ' . $db->prefix . 'wiki_pages.creation_date,
  ' . $db->prefix . 'wiki_pages.last_update,
  ' . $db->prefix . 'wiki_pages.visible_from_date,
  ' . $db->prefix . 'wiki_pages.visible_to_date,
  ' . $db->prefix . 'wiki_pages.content,
  ' . $db->prefix . 'wiki_pages.additional_data,
  ' . $db->prefix . 'wiki_pages.creation_user_ID,
  ' . $db->prefix . 'wiki_pages.latest_update_user_ID,
  ' . $db->prefix . 'wiki_pages.visible,
  ' . $db->prefix . 'wiki_pages.featured_video_ID,
                     VIDEO.user_ID featured_video_user_ID,
                     VIDEO.filename featured_video_filename,
                     VIDEO.description featured_video_description,
                     VIDEO.title featured_video_title,
  ' . $db->prefix . 'wiki_pages.no_index,
  ' . $db->prefix . 'wiki_pages.service_page,
  ' . $db->prefix . 'wiki_pages.full_page,
  ' . $db->prefix . 'wiki_pages.no_banner,
  ' . $db->prefix . 'wiki_pages.no_info,
  ' . $db->prefix . 'wiki_pages.no_linking_pages,
  ' . $db->prefix . 'wiki_pages.no_comment,
  ' . $db->prefix . 'wiki_pages.no_toc,
  ' . $db->prefix . 'wiki_pages.no_similar_pages,
  ' . $db->prefix . 'wiki_pages.no_search,
  ' . $db->prefix . 'wiki_pages.no_title,
  ' . $db->prefix . 'wiki_pages.no_feedback,
  ' . $db->prefix . 'wiki_pages.parser,
  ' . $db->prefix . 'wiki_pages.internal_redirect,
  IF (' . $db->prefix . 'wiki_pages.cache_expiration < NOW(), 1, 0 ) cache_expired,
  ' . $db->prefix . 'licenses_licenses.ID AS license_ID,
  ' . $db->prefix . 'licenses_licenses.name AS license_name,
  ' . $db->prefix . 'users.username,
  ' . $db->prefix . 'users.article_signature,
  ' . $db->prefix . 'users.short_biography,
  ' . $db->prefix . 'fabmedia.user_ID AS article_image_user_ID,
  ' . $db->prefix . 'fabmedia.title AS article_image_title,
  ' . $db->prefix . 'fabmedia.filename AS article_image_filename,
  ' . $db->prefix . 'fabmedia.extension AS article_image_extension,
  ' . $db->prefix . 'fabmedia_images.width AS article_image_width,
  ' . $db->prefix . 'fabmedia_images.height AS article_image_height
FROM
  ' . $db->prefix . 'wiki_pages
  LEFT JOIN ' . $db->prefix . 'licenses_licenses ON (' . $db->prefix . 'wiki_pages.license_ID = ' . $db->prefix . 'licenses_licenses.ID)
  LEFT JOIN ' . $db->prefix . 'wiki_masters ON (' . $db->prefix . 'wiki_pages.master_ID = ' . $db->prefix . 'wiki_masters.ID)
  LEFT JOIN ' . $db->prefix . 'users ON (' . $db->prefix . 'wiki_pages.creation_user_ID = ' . $db->prefix . 'users.ID)
  LEFT JOIN ' . $db->prefix . 'fabmedia ON (' . $db->prefix . 'wiki_pages.image_ID = ' . $db->prefix . 'fabmedia.ID)
  LEFT JOIN ' . $db->prefix . 'fabmedia_masters ON (' . $db->prefix . 'fabmedia.master_ID = ' . $db->prefix . 'fabmedia_masters.ID)
  LEFT JOIN ' . $db->prefix . 'fabmedia_images ON (' . $db->prefix . 'wiki_pages.image_ID = ' . $db->prefix . 'fabmedia_images.file_ID)
  LEFT JOIN ' . $db->prefix . 'fabmedia VIDEO ON (' . $db->prefix . 'wiki_pages.featured_video_ID = VIDEO.ID)
  
  WHERE ' . $db->prefix . 'wiki_pages.trackback = \'' . $trackback . '\' AND language = \'' . $core->shortCodeLang . '\'
  LIMIT 1;';

if (!$result = $db->query($query)) {

    $relog->write(['type' => '4',
        'module' => 'WIKI',
        'operation' => 'wiki_page_show_query_error',
        'details' => 'Cannot select the page. Query error. ' . $query,
    ]);

    echo 'Query error! <pre>' . $query . '</pre>';
    return;
}

// Fire a 404 error
if (!$db->affected_rows) {

    $relog->write(['type' => '2',
        'module' => 'WIKI',
        'operation' => 'wiki_page_not_found',
        'details' => 'Page not found. ' . $query,
    ]);

    header("HTTP/1.0 404 Not Found");
    $this->addTitleTag($trackback, '. ' . $language->get('wiki', 'showPagePageNotFound'));
    echo '<!--FabCMS-hook:wiki-showPage404NotFound-->';

    $stats->write(['IDX' => $page_ID, 'module' => 'wiki', 'submodule' => 'pageView']);

    return;
}

$row = mysqli_fetch_assoc($result);

$fabwiki->publishedID[] = $row['ID'];
$fabwiki->creationDate = $row['creation_date'];
$fabwiki->updateDate = $row['last_update'];
$fabwiki->authorUsername = $row['username'];
$fabwiki->authorSignature = $row['article_signature'];
$fabwiki->metaDataDescription = $row['metadata_description'];
$fabwiki->title = $row['title'] . (!empty($row['title_alternative']) ? ' - ' . $row['title_alternative'] : '');
$fabwiki->trackback = $row['trackback'];
$fabwiki->cacheExpired = (int)$row['cache_expired'];

/* Check if a $_GET directive of "renderType" has been passed */
if (isset($_GET['renderType'])) {
    $fabwiki->parserNoLink = true;
    $fabwiki->renderType = (int)$_GET['renderType'];
}

/* Open graph tags */
$this->addMeta('og:url', $URI->getBaseUri() . $this->routed . '/' . $trackback . '/');
$this->addMeta('og:locale', $row['language'] . '-' . strtoupper($row['language']));
$this->addMeta('og:type', 'article');
$this->addMeta('og:description', $fabwiki->metaDataDescription);
$this->addMeta('og:article:published_time', $row['creation_date']);
$this->addMeta('og:article:modified_time', $row['last_update']);
$this->addMeta('og:article:tag', $row['tag']);

if (!empty($row['featured_video_ID'])) {
    $this->addMeta('og:video', $row['featured_video_ID']);

    $contentFeaturedVideo = '<div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="video-card">
                    <div class="row" id="videoRow">
                       
                        <div class="col-12 col-lg-8 video-section" id="videoColumn">
                            <video controls id="featuredVideo">
                                <source src="' . $URI->getBaseUri() . 'fabmedia/' . $row['featured_video_user_ID'] . '/' . $row['featured_video_filename'] . '" type="video/mp4">
                                Il tuo browser non supporta il tag video.
                            </video>
                        </div>
                        <div class="col-12 col-lg-4 info-section" id="infoColumn">
                            <h3>' . $row['featured_video_title'] . '</h3>
                            <p>' . $row['featured_video_description'] . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>';

}

/*
 * ************************************
 * * Check if exists a featured image *
 * ************************************
*/
if (isset($row['article_image_filename'])) {

    $imagePath = $row['article_image_filename'];
    $extension = $row['article_image_extension'];
    $pos = strrpos($imagePath, '.' . $extension);

    $imageFinalMQPath_destination = $URI->getBaseUri(true) . 'fabmedia/' . $row['article_image_user_ID'] . '/' . substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));

    if ($conf['uri']['useHTTPS']) {
        $this->addMeta('og:image', $imageFinalMQPath_destination);
        $this->addMeta('og:image:secure_url', $imageFinalMQPath_destination);
    } else {
        $this->addMeta('og:image', $imageFinalMQPath_destination);
    }

    $this->addMeta('og:image:type', 'image/' . $extension);
    $this->addMeta('og:image:alt', $row['article_image_title']);
    $this->addMeta('og:image:width', $row['article_image_width']);
    $this->addMeta('og:image:height', $row['article_image_height']);

}

/*
 * **********************************
 * * Check if the page is fullpage *
 * **********************************
*/
$fabwiki->fullPage = (int)$row['full_page'] === 1;

/*
 * **********************************
 * * Check if the page is indexable *
 * **********************************
*/
if ((int)$row['noindex'] === 1) {
    $this->addMetaData('robots', 'noindex');
}

/*
 * ***************************************
 * * Check if the page is a service page *
 * ***************************************
*/

if ((int)$row['service_page'] === 1) {
    $this->addMetaData('robots', 'noindex');

    echo '<h2>' . $conf['site']['name'] . '</h2>' . $language->get('wiki', 'showPageServicePage');
    return;
}

/*
 * *******************************************
 * * Check the start and the end of the date *
 * *******************************************
 */
if (!$user->isAdmin && !$fabwiki->isBetweenDate($row['visible_from_date'], $row['visible_to_date'])) {
    echo $language->get('wiki', 'showPageContentIsNotAvaliable');

    return;
}

if ((int)$row['visible'] !== 1 && !$user->isAdmin) {
    echo $language->get('wiki', 'showPageContentIsNotAvaliable');
    return;
}

$master_ID = $row['master_ID'];
$page_ID = $row['ID'];

// Check if page has an internal redirect
if (!empty($row['internal_redirect'])) {
    header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $core->getTrackback($row['internal_redirect']) . '/');
    return;
}

/*
 * Get tags associated
 */
$tags_array = $fabwiki->getTagsFromPageID($page_ID, 'case_insensitive');

/*
 * Get keywords associated
 *
 */

$keywords_array = $fabwiki->getKeywordsFromPageID($page_ID);

/*
 * ********************
 * * Navigation bar   *
 * ********************
 */


/*
* ********************
* * Similar contents *
* ********************
*/

if (count($keywords_array) > 0 && (int)$row['no_similar_pages'] !== 1) {

    if ($conf['memcache']['enabled'] === true && $fabwiki->cacheExpired === 0)
        $similarPages = $memcache->get('wikiPageSimilarPages-' . $page_ID);

    if (empty($similarPages)) {
        $similarPages = '';

        $query = '
        SELECT DISTINCT(P.ID), 
               P.title,
               P.image,
               P.image_ID,
               P.trackback,
               COUNT(K.keyword) AS no_keywords,               
               group_concat(K.keyword SEPARATOR \', \') AS keyword
        FROM ' . $db->prefix . 'wiki_pages AS P
        LEFT JOIN ' . $db->prefix . 'wiki_pages_keywords AS K
        ON K.page_ID = P.ID
        WHERE P.visible = 1 
            AND P.service_page != 1
            AND P.ID != \'' . $page_ID . '\'
            AND P.language = \'' . $core->shortCodeLang . '\' 
            AND (';

        foreach ($keywords_array as $singleKeyword) {
            $query .= 'K.keyword = \'' . $core->in($singleKeyword) . '\' OR ';
        }
        $query = substr($query, 0, -3) . ')';

        $query .= 'GROUP BY P.ID ORDER BY no_keywords DESC LIMIT 6';

        if (!$resultSimilarPages = $db->query($query)) {

            $relog->write(['type' => '4',
                'module' => 'WIKI',
                'operation' => 'wiki_page_similar_contents_error',
                'details' => 'Cannot select the similar pages. Query error. ' . "\r\n" . $db->lastError . "\r\n" . $query,
            ]);

            $similarPages = 'Query error.';
        } else {

            if (!$db->affected_rows) {
                $similarPages .= '<p>
                                    ' . $language->get('wiki', 'showPageNoSimilarPage') . '
                                  </p>';
            } else {
                while ($rowSimilar = mysqli_fetch_assoc($resultSimilarPages)) {
                    $trackback = $URI->getBaseUri() . $this->routed . '/' . $rowSimilar['trackback'] . '/';
                    $similarPages .= '<a href="' . $trackback . '">' . $rowSimilar['title'] . '</a><br/>';
                }
            }
        }

        if ($conf['memcache']['enabled'] === true && $fabwiki->cacheExpired === 0)
            $memcache->set('wikiPageSimilarPages-' . $page_ID, $similarPages, 604800);
    }
}


// $template->navBarAddItem([(isset($fabwiki->config['wikiName']) ? $fabwiki->config['wikiName'] : 'Wiki')], $URI->getBaseUri() . $this->routed);
$template->navBarAddItem((isset($fabwiki->config['wikiName']) ? $fabwiki->config['wikiName'] : 'Wiki'), $URI->getBaseUri() . $this->routed . '/');

isset ($fabwiki->config['nameSpaceSeparator']) ? $nameSpaceSeparator = $fabwiki->config['nameSpaceSeparator'] : $nameSpaceSeparator = ':';

$navBarPieces = explode($nameSpaceSeparator, $row['title']);
$numNavBarPieces = count($navBarPieces);
/*
 * TAGS IN NAVIGATION BAR
 */
$numTags = count($tags_array);
for ($i = 0; $i < $numTags; $i++) {
    if (isset($fabwiki->menuLink[$i][$tags_array[$i]]))
        $template->navBarAddItem(($fabwiki->menuLink[$i][$tags_array[$i]]['name']), $fabwiki->menuLink[$i][$tags_array[$i]]['URI']);
}

$i = 1;
if ($numNavBarPieces > 1) {
    foreach ($navBarPieces as $singlePiece) {
        if ($i !== $numNavBarPieces) {
            $past .= $core->getTrackback($singlePiece) . ':main-page/';
            $template->navBarAddItem($singlePiece, $URI->getBaseUri() . $this->routed . '/' . $past);
        } else {
            $past .= $core->getTrackback($singlePiece) . '/';
            $template->navBarAddItem($singlePiece);
        }
        $i++;
    }
} else {
    $template->navBarAddItem($row['title'] . (!empty($row['title_alternative']) ? ' - ' . $row['title_alternative'] : ''));
}


$fabwiki->creationDate = $row['creation_date'];
$fabwiki->updateDate = $row['last_update'];

// If author attribution is set then post the OpenGraph tag to the header
if ((int)$fabwiki->config['authorAttribution'] > 1)
    $this->addMeta('og:author', $row['article_signature']);

if ($conf['multilang'] === true) {
    $internalPages = $fabwiki->getTranslatedPage($master_ID);

    if ($internalPages) {
        $internalPagesBody = '';

        foreach ($internalPages as $singlePage) {
            $tempPage = explode('|||', $singlePage);
            if (count($internalPages) > 1) {
                $this->head .= '<link rel="alternate" hreflang="' . $tempPage[0] . '" href="' . $URI->getBaseUri(true) . $tempPage[0] . '/' . $this->routed . '/' . $tempPage[1] . '/" />' . PHP_EOL;

                if ($tempPage[0] != $core->shortCodeLang)
                    $internalPagesBody .= '<a href="' . $URI->getBaseUri(true) . $tempPage[0] . '/' . $this->routed . '/' . $tempPage[1] . '/">' . $tempPage[2] . '</a><br/>';
            }

        }

        if (!empty($internalPagesBody))
            $template->sidebar .= $template->simpleBlock('In other language', $internalPagesBody);
    }

}


$this->addMetaData('description', $row['metadata_description']);

$core->jsVar['fabcms_isFullPage'] = 0;

if (!empty($row['title_alternative']))
    $title_alternative = ' - ' . $row['title_alternative'];


// Check if a custom rule matches
$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_custom_title_rules 
          WHERE 
                first_tag = \'' . $tags_array[0] . '\'';

$resultCustomTag = $db->query($query);

if ($db->affected_rows) {
    $rowCustomTitle = mysqli_fetch_assoc($resultCustomTag);
    $titleTag = $rowCustomTitle['rule'];

    $titleTag = str_replace('%s', html_entity_decode($row['title']), $titleTag);
    $titleTag = str_replace('%tag1', $tags_array[0], $titleTag);
    $titleTag = str_replace('%tag2', $tags_array[1], $titleTag);
    $titleTag = str_replace('%ta', $title_alternative, $titleTag);

    $titleTag = trim($titleTag);

    $this->addTitleTag($titleTag);

} else if (isset($fabwiki->config['pageTitleFormat'])) {
    $titleTag = $fabwiki->config['pageTitleFormat'];

    $titleTag = str_replace('%s', html_entity_decode($row['title']), $titleTag);
    $titleTag = str_replace('%tag1', $tags_array[0], $titleTag);
    $titleTag = str_replace('%tag2', $tags_array[1], $titleTag);
    $titleTag = str_replace('%ta', $title_alternative, $titleTag);

    $titleTag = trim($titleTag);

    $this->addTitleTag($titleTag);
} else {
    $this->addTitleTag(html_entity_decode($row['title'] . $title_alternative));
}

$this->addMeta('og:title', $titleTag);

$connector->callHandler('wiki_page_view', ['page_ID' => $page_ID, 'title' => $row['title'], 'tags' => $tags_array, 'keywords' => $keywords_array]);

if ($conf['memcache']['enabled'] === true && $fabwiki->cacheExpired === 0)
    $content = $memcache->get('wikiPage-' . $trackback);

if (empty($content)) {
    $debug->write('info', 'Content was not on memacache', 'wiki');

    if (!empty($row['use_file']) && file_exists(__DIR__ . '/pagefiles/' . $row['use_file'])) {
        $debug->write('info', 'Content was taken directly from the file', 'wiki');
        ob_start();
        require_once(__DIR__ . '/pagefiles/' . $row['use_file']);
        $content = ob_get_clean();
    } else {
        $debug->write('info', 'Content was taken from database', 'wiki');
        $content = $fabwiki->parseContent($row['content']);
    }

    if ($conf['memcache']['enabled'] === true) {
        $memcache->set('wikiPage-' . $trackback, $content, 604800);
    }
} else {
    $debug->write('info', 'Content was taken from memacache', 'wiki');
}

if (isset($_GET['printable'])) {
    $currentUrl = $URI->getBaseUri() . 'wiki/' . $trackback . '/';
    echo '<p> ' . sprintf($language->get('wiki', 'pageShowPrintableHeader'), date('m-d-Y'), $currentUrl, $currentUrl) . '</p>';
}

// Check if the page has no banner
if ((int)$row['no_banner'] != 1 && !isset($_GET['printable']) && !$user->isBot)
    $content = $fabwiki->parseBanner($content);

// Check if FabCMS should build a TOC
if ((int)$fabwiki->config['useToc'] === 1 && (int)$row['no_toc'] === 0)
    $content = $fabwiki->parseToc($content);

// Check if a featured_video_exists
if (strlen($row['featured_video_code']) > 1) {
    $pos = strpos($content, '<h2');
    $content = substr($content, 0, $pos) . '<style>
                                                    .videoWrapper {
                                                      position: relative;
                                                      padding-bottom: 56.25%; /* 16:9 */
                                                      height: 0;
                                                    }
                                                    .videoWrapper iframe {
                                                      position: absolute;
                                                      top: 0;
                                                      left: 0;
                                                      width: 100%;
                                                      height: 100%;
                                                    }
                                                  </style>
                                                  <div class="videoWrapper">
                                                    <iframe id="ytplayer" 
                                                            type="text/html"
                                                            width="560" 
                                                            height="349"
                                                            src="https://www.youtube.com/embed/' . $row['featured_video_code'] . '" 
                                                            frameborder="0">
                                                    </iframe>
                                                  </div>' . substr($content, $pos, strlen($content));


}

if ((int)$row['no_title'] !== 1)
    $template->pageTitle = $row['title'];

if ($user->isAdmin === true) {
    $content = '<div class="wikiEditArticle"><a href="' . $URI->getBaseUri(true) . '/admin/admin.php?module=wiki&op=editor&ID=' . $page_ID . '">Article edit</a></div>' . $content;
}

if (isset($_GET['printable'])) {

    $this->noTemplateParse = true;
    $this->addMetaData('robots', 'noindex');

    echo '
    <style type="text/css">
        .FabCMS-imageContainer{
            padding:12px;
            background-color: #EFEFEF;
            margin: 0 auto;
            text-align: -webkit-center;
        }
        
        .hopt-url {
         display: none;
        }
    </style>
    
    <!--FabCMS-hook:wikiBeforeArticle-tag-' . $tags_array[0] . '-->
    <!--FabCMS-hook:wikiBeforeArticle-->
    <article>
       
        <!--FabCMS-hook:wikiInsideArticleTop-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleTop-->
        
        ' . $content . '
        <p>
            <em>Copyright ' . date('Y') . ' - ' . $conf['organization'] . '</em>
        </p>
        <!--FabCMS-hook:wikiInsideArticleBottom-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleBottom-->
    </article>
    <!--FabCMS-hook:wikiSideBarAfterArticle-->
    <!--FabCMS-hook:wikiSideBarAfterArticle-tag-' . $tags_array[0] . '-->';

    return;
} else {

    if (isset($row['parser']) && strlen($row['parser']) > 1) {
        $parserFile = __DIR__ . '/parser/' . str_replace('.', '', $row['parser']) . '.php';
        require_once $parserFile;
    } else {

        echo '
                <style>
                    .FabCMS-imageContainer {
                        padding:12px;
                        background-color: #EFEFEF;
                        margin: 0 auto;
                        text-align: -webkit-center;
                    }
                    
                    .hopt-url {
                         display: none;
                    }
                </style>

<!--FabCMS-hook:wikiBeforeArticle-tag-' . $tags_array[0] . '-->
<!--FabCMS-hook:wikiBeforeArticle-->
' . $header . '
<div class="row">
    <div class="col-md-' . ($fabwiki->fullPage === true ? 12 : 9) . '">
    <article>
        <div class="row">';


        if ( $row['no_info'] != 1) {
            $datetime_str = $row['last_update'];
            $timestamp = strtotime($datetime_str);
            $date_formatted = date('m-d-Y', $timestamp);

            $authorImage = $URI->getBaseUri(true) . 'fabmedia/authors/' . $row['creation_user_ID'] . '.webp';

            echo '<div class="article-card">
                    <div class="article-content">
                        ' . ((int)$row['no_title'] === 1 ? '' : '<h1>' . $titleTag . '</h1>') . '
                        <div class="article-description">
                            <p>' . $row['short_description'] . ' </p>
                        </div>
                    </div>
                    <div class="article-meta">
                        <img src="' . $authorImage . '" alt="Immagine autore articolo">
                        <h3>' . $authorName . '</h3>
                        <p>Revisionato il: ' . $date_formatted . '</p>
                    </div>
                 </div>';
        }


        if ( (int) $row['no_feedback'] !== 1) {
            $feedback = '
<style>
:root {
    --star-color: #ffd055; /* Colore delle stelle */
    --star-size: 30px; /* Dimensione delle stelle */
    --feedback-font: Arial, sans-serif; /* Font del feedback */
    --feedback-primary-color: #007bff; /* Colore primario */
    --feedback-secondary-color: #6c757d; /* Colore secondario */
}

.fabcmsWikiFeedback {
    border: 1px solid #EEE;
    padding: 16px;
    background-color: #EFEFEF;
}

.star-rating {
    display: flex;
    cursor: pointer;
}

.star {
    color: var(--feedback-secondary-color);
    font-size: var(--star-size);
    margin-right: 5px; /* Distanza tra le stelle */
}

.star.selected {
    color: var(--star-color);
}
</style>

    <div class="fabcmsWikiFeedback mt-4">
    <h4>Feedback</h4>   
    <p>Il tuo aiuto è importante. Ti chiediamo un minuto per rispondere a questo breve sondaggio</p>
    
    <div class="row">
        <div class="col-md-4">Come valuteresti questo articolo?</div>
        <div class="col-md-8">
            <div class="star-rating">
                
                <span class="star" data-value="1">&#9733;</span>
                <span class="star" data-value="2">&#9733;</span>
                <span class="star" data-value="3">&#9733;</span>
                <span class="star" data-value="4">&#9733;</span>
                <span class="star" data-value="5">&#9733;</span>
                
            </div>
            <input type="hidden" id="feedbackScore" value="1">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">Vuoi suggerirci qualcosa?</div>
        <div class="col-md-8">
            <textarea   class="form-control" id="fabcms-feedBackComment"></textarea>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">Captcha</div>
        <div class="col-md-8">
            <img src="' . htmlspecialchars($captcha->createImageBase64()). '" alt="captcha">
            <input class="form-control" id="fabcms-feedBackCaptcha"></input>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-5">
        <div class="col-md-8">
            <button id="fabcmsWikiFeedbackButton" onclick="submitFeedback();">Invia il feedback</button>
        </div>
    </div>
    
    <div class="" style="display:none;">
        <input type="text" name="website" id="website" value="">
    </div>
</div>

<script>
document.addEventListener(\'DOMContentLoaded\', function() {
    const stars = document.querySelectorAll(\'.star-rating .star\');

    stars.forEach(star => {
        star.addEventListener(\'click\', function() {
            const value = this.getAttribute(\'data-value\');
            document.getElementById(\'feedbackScore\').value = value;

            stars.forEach(s => {
                s.classList.remove(\'selected\');
            });

            this.classList.add(\'selected\');
            let prevSibling = this.previousElementSibling;
            while (prevSibling) {
                prevSibling.classList.add(\'selected\');
                prevSibling = prevSibling.previousElementSibling;
            }
        });
    });
});

function submitFeedback() {
    
    const website = document.getElementById(\'website\').value;
    
    if (website !== \'\') 
        return; 

    page_ID   = 0 + ' . $page_ID  . ';
    score     = document.getElementById(\'feedbackScore\').value;
    comment   = document.getElementById(\'fabcms-feedBackComment\').value;
    captcha   = document.getElementById(\'fabcms-feedBackCaptcha\').value;

    if (captcha.lenth === 0) {
        alert("Captcha non valido");
        return;
    }
    $.post( "'. $URI->getBaseUri() . '/wiki/ajax-submit-feedback/", { page_ID: page_ID, score: score, comment: comment, captcha: captcha})
        .done(function( data ) {
        $("#fabcmsWikiFeedbackResponse").html("Il tuo feedback &egrave; stato registrato. Grazie per aver contribuito a rendere la pagina migliore.");
        $("#fabcmsWikiFeedbackButton").prop("disabled", true);
    });
}
</script>
</div>';
        }



        echo '
        
        <script> 
            function toggleAuthorBox() {
                 $("#wikiAuthorBox").toggle();
            }
        </script>
        <div class="wikiArticle">
        <!--FabCMS-hook:wikiInsideArticleTop-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleTop-->
        ' . $contentFeaturedVideo . '
        ' . $content . '
        <!--FabCMS-hook:wikiInsideArticleBottom-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleBottom-->
        ' . $feedback . '
        </div>
    </article>
    </div>';


        $keywords = $fabwiki->getSeoKeywords($page_ID);
        require_once ($conf['path']['baseDir'] . 'lib/seo/class_seo.php');
        $seo = new SeoScoreCalculator($content, $keywords, $row['metadata_description']);
        $resultSeo = $seo->calculateScore();

        $seoBlock = '<div class="content-alt"><h3>SEO</h3>';

        foreach (json_decode($resultSeo, true) as $keyword  => $scoreInfo) {
            if (!is_array($scoreInfo))
                continue;

            $seoBlock .= "<strong>Keyword: " . $keyword . "</strong><br/>";
            $seoBlock .= "Total Score: " . $scoreInfo['totalScore'] . "<br/>";
            $seoBlock .= "Potential score: " . $scoreInfo['potentialScore'] . "<br/>";
            $seoBlock .= "Details:<br/>" ;

            // Itera sull'array dei dettagli per mostrare i punteggi individuali e le penalizzazioni
            foreach ($scoreInfo['details'] as $metric => $value) {
                $seoBlock .= "  " . ucfirst($metric) . ": " . $value . "<br/>";
            }

            $seoBlock .= "<br/>"; // Aggiunge una riga vuota per separare i risultati delle diverse keyword
        }

        $seoBlock .= '</div>';

        if ($fabwiki->fullPage === false) {
            echo '
            <div id="wikiSidebar" class="col-md-3 wikiSidebar sidebar">
              <!--FabCMS-hook:wikiSideBarFirstSpot-->
              <!--FabCMS-hook:wikiSideBarFirstSpot-tag-' . $tags_array[0] . '-->
              <div class="sidebar-block">
                <h3>CERCA</h3>
                <p>
                    <form class="clearfix" action="' . $URI->getBaseUri() . 'search/simple/' . '" method="post">
                        <div class="col-sm-9">
                            <input name="search" class="form-control">
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-dark  float-right" type="submit">Ricerca</button>    
                        </div>
                    </form>
                </p>
              </div>
              
              <div class="sidebar-block">
                <h3>Pagine simili</h3>
                <p>
                    ' . $similarPages . '
                </p>
            </div>';

            if ($user->isAdmin) {
                echo '<div class="sidebar-block-alt">
                        ' . $seoBlock . '
                     </div>';
            }

          echo '
          <!--FabCMS-hook:wikiSideBarLastSpot-tag-' . $tags_array[0] . '-->
          <!--FabCMS-hook:wikiSideBarLastSpot-->
    </div>';
        }

        echo '
                </div>
' . ((int)$fabwiki->config['showPageLicense'] === 1
                ? '<div class="fabCms-Wiki-CopyrightNotice"><i class="far fa-copyright"></i> &nbsp;' .
                sprintf($language->get('wiki', 'showPageCopyrightNotice'),
                    $URI->getBaseUri() . 'licenses/show/' . $row['license_ID'] . '/',
                    $row['license_name'])
                . '</div>'
                : '') . '
<!--FabCMS-hook:wikiSideBarAfterArticle-->
<!--FabCMS-hook:wikiSideBarAfterArticle-tag-' . $tags_array[0] . '-->';

    }
}


if ( (int) $row['no_comment'] !== 1) {
    // Get all the comments for the page

    $query = '
SELECT 
      C.comment,
      C.author,
      C.date,
      U.username 
FROM ' . $db->prefix . 'wiki_comments AS C
LEFT JOIN ' . $db->prefix . 'users AS U
    ON C.author_ID = U.ID
WHERE C.page_ID = ' . $page_ID . '
    AND visible = 1;';

    if (!$result = $db->query($query)) {

        $relog->write(['type' => '4',
            'module' => 'WIKI',
            'operation' => 'wiki_page_show_select_comments_error',
            'details' => 'Cannot show comments. Query error. ' . $query,
        ]);

        $comments = 'Query error while selecting comments';

    } else {
        if (!$db->affected_rows) {
            $comments = 'No comments for this page';
        } else {
            while ($row = mysqli_fetch_assoc($result)) {

                if (isset($row['username'])) {
                    $author = $row['username'];
                } else {
                    if (!empty($row['author'])) {
                        $author = $row['author'];
                    } else {
                        $author = 'Anonymous';
                    }
                }

                $comment = str_ireplace(['<style', '<script', '<div', '<span', '<link'], ['', '', '', ''], $row['comment']);

                // Clean HTML
                $comment = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $comment);

                // Replace URLS
                $url = '@(http)?(s)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
                $comment = preg_replace($url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $comment);

                $comments .= '                 
                 <div class="media">
                    <div class="media-left">
                        <img alt="Unknown user avatar" 
                             data-src="' . $URI->getBaseUri(true) . '/modules/wiki/res/50x50_user_unknown.png" 
                             class="lazy media-object" 
                             style="width:60px">
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">' . $author . ' il ' . $row['date'] . '</h4>
                        <p>' . $comment . '</p>
                    </div>
                </div>
                <div style="border-top: 1px solid #CACACA"></div>';
            }
        }
    }

    echo '
          <div id="commentContainer" class="fabcms-Wiki-CommentContainer form-group clearfix mt-4">
                 <!--FabCMS-hook:wikiCommentsTop-->
                 <h4 class="fabCMS-Wiki-CommentDescription">Commenti</h4>
                 <div id="commentBox" class="fabcms-Wiki-CommentBox clearfix">
                    
                    <div class="row">
                        
                        <div class="col-lg">
                            <select class="form-control" onchange="changeComment();" id="authorSelect">
                                ' . ($user->logged ? '<option value="' . $user->ID . '">Pubblica come ' . $user->username . '</option>' : '') . '
                                <option value="-1">' . $language->get('wiki', 'pageShowCommentPublishWithName', null) . '</option>
                                <option value="-2">' . $language->get('wiki', 'pageShowCommentPublishAnonymously', null) . '</option>
                            </select>    
                        </div>
                        
                        <div class="col-lg hopt-url">
                            URL: <input type="text" name="url" id="url" />
                        </div>
                        
                        <div class="col-lg">
                            <input class="form-control" type="text" value="" placeholder="' . $language->get('wiki', 'pageShowCommentUserPlaceholder', null) . '" id="author" disabled>
                        </div>
                        
                    </div>
                    
   
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <textarea rows="5" placeholder="' . $language->get('wiki', 'pageShowCommentCommentPlaceholder', null) . '" class="form-control" id="commentTextarea"></textarea>    
                        </div>
                    </div>

 
                    <div class="row">';

    /*
    if ( $useRecaptacha === true) {

        echo '<div class="col-lg mt-3">
              ' . $core->reCaptchaGetCode() . '
              </div>';
    }
   */

    echo '
                        <div class="col-lg mt-3">
                            <button class="btn btn-dark float-right" id="commentButton" onclick="postComment();">Invia commento</button>
                        </div>
                    </div>
                    
                    <div id="postResult" class="mt-6"></div>                
                 </div>
                 
                ' .
        $comments . '</div>';


    $script = '
function changeComment()
{
    chose = $("#authorSelect").val();

    switch ( chose ) {
        case "-1":
            $("#author").removeAttr("disabled");             
            break;
        case "-2":
            $("#author").prop("disabled", true); 
            break;
        default:
            $("#author").prop("disabled", true); 
            break;
    }
}    
    
function postComment()
{
    var SH1 = "' . ($conf['security']['siteKey'] . date('Y-m-d-H')) . '";
    var securityHash = "' . md5($conf['security']['siteKey'] . $page_ID) . '";
    var page_ID = ' . $page_ID . ';
    
    var url = $("#url").val();
    var comment = $("#commentTextarea").val();
    var authorSelect = $("#authorSelect").val();
    
    var author = $("#author").val();
    
    if (comment.length < 10){
        alert ("Il testo del commento deve essere di almeno dieci caratteri.");
        return;
    }
    
    if (comment.length > 500) {
        alert ("Il testo del commento non deve superare i 500 caratteri");
        return;
    }
    
    $.post( "' . $URI->getBaseUri() . $this->routed . '/ajax_post_comment/", {
                                                                              SH1: SH1, 
                                                                              comment:comment, 
                                                                              securityHash: securityHash,
                                                                              page_ID: page_ID,
                                                                              url: url,
                                                                              authorSelect : authorSelect, 
                                                                              author : author })
        .done(function( data ) {
            if (data.startsWith("<!--ok-->")) {
                $("#commentButton").hide();
                $("#postResult").html(data);
            } else {
                $("#postResult").html(data);
            }
            
    });
}';
    $this->addScript($script);
}

if (count($fabwiki->images) > 0) {
    $schemaImages = '"image": [ ';

    foreach ($fabwiki->images as $singleImage) {
        $schemaImages .= '"' . $singleImage . '", ';
    }

    $schemaImages = substr($schemaImages, 0, -2);
    $schemaImages .= '],';

}

// Check for the logo
if (file_exists($conf['path']['baseDir'] . 'templates/logo.png')) {
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.png';
}

if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpg')) {
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.jpg';
}

if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpeg')) {
    $logoBrand = $URI->getBaseUri(true) . 'templates/logo.jpeg';
}

echo PHP_EOL . '<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type"           :   "Article",
  "author"          :   "' . $fabwiki->authorUsername . '",
  "mainEntityOfPage": {
    "@type"         :   "WebPage",
    "@id"           :   "' . $URI->getBaseUri() . $this->routed . '/' . $fabwiki->trackback . '/"
  },
  "headline"        :   "' . $fabwiki->title . '",
  ' . $schemaImages . '
  "publisher"       : {   "@type" : "organization",  
                        "name" :    "' . $conf['organization'] . '", 
                        "logo" :  {
                            "@type" : "ImageObject",
                            "url"   : "' . $logoBrand . '" 
                        }
                    },
  "datePublished"   :   "' . $fabwiki->creationDate . '",
  "dateModified"    :   "' . $fabwiki->updateDate . '",
  "description"     :   "' . str_replace('"', '', $fabwiki->metaDataDescription) . '"
}
</script>';
if ($fabwiki->cacheExpired === 1) {
    $query = 'UPDATE ' . $db->prefix . 'wiki_pages 
              SET cache_expiration = DATE_ADD( NOW(), INTERVAL 7 DAY)
              WHERE ID = ' . $page_ID . ' 
              LIMIT 1 ';

    $db->query($query);
}

$stats->write(['IDX' => $page_ID, 'module' => 'wiki', 'submodule' => 'pageView']);