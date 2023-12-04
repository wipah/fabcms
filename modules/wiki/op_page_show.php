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
$lastPath = strtolower($path[ count($path) - 1]);

if ($lastPath == 'www.google.it' || $lastPath == 'index.html' || $lastPath == 'index.htm') {
    header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $path[2] . '/' );
    return;
}

// check if final slash has been passed
if ($charPos = strpos($_SERVER['REQUEST_URI'], '?')){
    $char = substr($_SERVER['REQUEST_URI'], $charPos -1, 1);

    if ($char !== '/'){
        $lastPath = substr($lastPath, 0, strpos($lastPath,'?'));
        $queryString = substr($_SERVER['REQUEST_URI'], $charPos);

        header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $lastPath . '/' . $queryString);
        return;
    }
} else {
    $lastChar = (substr($_SERVER['REQUEST_URI'], -1));

    if ($lastChar !== '/'){
        header('Location:' . $URI->getBaseUri() . $this->routed . '/' . $path[2] . '/');
        return;
    }
}

echo '<style>' . $fabwiki->config['customCSS'] . '</style>';


if ( (int) $core->getConfig( 'core', 'recaptchaEnabled') === 1) {
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
  ' . $db->prefix . 'wiki_pages.trackback,
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

  WHERE ' . $db->prefix . 'wiki_pages.trackback = \'' . $trackback . '\' AND language = \'' . $core->shortCodeLang . '\'
  LIMIT 1;';

if (!$result = $db->query($query)) {

    $relog->write(['type'      => '4',
                   'module'    => 'WIKI',
                   'operation' => 'wiki_page_show_query_error',
                   'details'   => 'Cannot select the page. Query error. ' . $query,
    ]);

    echo 'Query error! <pre>' . $query . '</pre>';
    return;
}

// Fire a 404 error
if (!$db->affected_rows) {

    $relog->write(['type'      => '2',
        'module'    => 'WIKI',
        'operation' => 'wiki_page_not_found',
        'details'   => 'Page not found. ' . $query,
    ]);

    header("HTTP/1.0 404 Not Found");
    $this->addTitleTag($trackback, '. ' . $language->get('wiki', 'showPagePageNotFound'));
    echo '<!--FabCMS-hook:wiki-showPage404NotFound-->';

    $stats->write(['IDX' => $page_ID, 'module' => 'wiki', 'submodule' => 'pageView']);

    return;
}

$row = mysqli_fetch_assoc($result);

$fabwiki->publishedID[]         =   $row['ID'];
$fabwiki->creationDate          =   $row['creation_date'];
$fabwiki->updateDate            =   $row['last_update'];
$fabwiki->authorUsername        =   $row['username'];
$fabwiki->authorSignature       =   $row['article_signature'];
$fabwiki->metaDataDescription   =   $row['metadata_description'];
$fabwiki->title                 =   $row['title'] . ( !empty($row['title_alternative']) ? ' - ' . $row['title_alternative'] : '');
$fabwiki->trackback             =   $row['trackback'];
$fabwiki->cacheExpired         =   (int) $row['cache_expired'];

/* Check if a $_GET directive of "renderType" has been passed */
if (isset($_GET['renderType'])) {
    $fabwiki->parserNoLink = true;
    $fabwiki->renderType = (int) $_GET['renderType'];
}

/* Open graph tags */
$this->addMeta('og:url', $URI->getBaseUri() . $this->routed . '/' . $trackback . '/');
$this->addMeta('og:locale', $row['language'] . '-' . strtoupper($row['language']));
$this->addMeta('og:type', 'article');
$this->addMeta('og:description', $fabwiki->metaDataDescription);
$this->addMeta('og:article:published_time', $row['creation_date']);
$this->addMeta('og:article:modified_time', $row['last_update']);
$this->addMeta('og:article:tag', $row['tag']);

if (strlen($row['featured_video_url']) > 8 ) {
    $this->addMeta('og:video', $row['featured_video_url']);

    $contentFeaturedVideo = '
        <div id="featuredVideo" class="section d-flex justify-content-center embed-responsive embed-responsive-16by9">
            <video class="embed-responsive-item" controls autoplay loop muted>
                <source src="' . $row['featured_video_url'] . '" type="video/mp4">
                Il tuo browser non supporta la visualizzazione dei video.
            </video>
        </div>';
}

/*
 * ************************************
 * * Check if exists a featured image *
 * ************************************
*/
if (isset($row['article_image_filename'])) {

    $imagePath =  $row['article_image_filename'];
    $extension = $row['article_image_extension'];
    $pos = strrpos($imagePath, '.' . $extension);

    $imageFinalMQPath_destination = $URI->getBaseUri(true) . 'fabmedia/' . $row['article_image_user_ID'] . '/' .substr_replace($imagePath, '_mq.' . $extension, $pos, strlen('.' . $extension));

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

    $header = '
    <style>

    div.articleHeader {
        position:relative;
    }
    div.cover {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        background-size: cover !important; 
        height: 50vh; 
        background: url(' . $imageFinalMQPath_destination . ');
        
    }
    div..top-text,
    div.bottom-text {
            color: white;
    }

    </style>
    <div class="articleHeader">
        <div class="cover">
            <div class="top-text">' .  $titleTag . '</div>
                <!-- Immagine di copertina con titolo, autore e data -->
            <div class="bottom-text">Info in fondo</div>
        </div>
    </div>';
}

/*
 * **********************************
 * * Check if the page is indexable *
 * **********************************
*/
if ( (int) $row['noindex'] === 1) {
    $this->addMetaData('robots', 'noindex');
}

/*
 * ***************************************
 * * Check if the page is a service page *
 * ***************************************
*/

if ( (int) $row['service_page'] === 1) {
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

if ( (int) $row['visible'] !== 1 && !$user->isAdmin) {
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

// $template->navBarAddItem([(isset($fabwiki->config['wikiName']) ? $fabwiki->config['wikiName'] : 'Wiki')], $URI->getBaseUri() . $this->routed);
$template->navBarAddItem( (isset($fabwiki->config['wikiName']) ? $fabwiki->config['wikiName'] : 'Wiki'), $URI->getBaseUri() . $this->routed . '/');

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
            $template->navBarAddItem($singlePiece, $URI->getBaseUri() . $this->routed . '/' . $past );
        } else {
            $past .= $core->getTrackback($singlePiece) . '/';
            $template->navBarAddItem($singlePiece);
        }
        $i++;
    }
} else {
    $template->navBarAddItem( $row['title'] . (!empty($row['title_alternative']) ? ' - ' . $row['title_alternative'] : ''));
}

/*
 * **********************
 * * Lateral info block *
 * **********************
 */
if (  (int) $row['full_page'] !== 1) {


    $template->sidebar .= '<!--FabCMS-hook:wikiSideBarTop-->
                            <!--FabCMS-hook:wikiTag-sideBarTop-' . $core->getTrackback($tags_array[0]) . '-->';

    if ((int)$fabwiki->config['showSearchBox'] === 1 && (int)$row['no_search'] !== 1) {
        $template->sidebar .= $template->simpleBlock($language->get('wiki', 'showSearchBoxSearch'), '
        <form class="clearfix" action="' . $URI->getBaseUri() . 'search/simple/' . '" method="post">
            <div class="row">
                <div class="col-sm-9">
                    <input name="search" class="form-control">
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-outline-primary btn-outline-primary-search float-right" type="submit">Ricerca</button>    
                </div>
            </div>
            
        </form>
        ');
    }

    if ( (int) $row['no_info'] !== 1) {

        switch ( (int) $fabwiki->config['authorAttribution']) {

            case '1':
                $fabwiki->authorUsername = $language->get('wiki', 'pageShowInternalAuthor', null);
                break;
            case '2':
                $fabwiki->authorUsername = $row['article_signature'];
                $authorName = $language->get('wiki', 'showPageWrittenBy') . '<span itemprop="author">' . $row['article_signature'] . '</span><br/>';
                break;
            case '3':
                $fabwiki->authorUsername = $row['article_signature'];

                // Check if photo exists
                $hash = md5($row['creation_user_ID'] . $conf['security']['siteKey']);

                $filePath = $conf['path']['baseDir'] . 'cache/users/profile/' . $hash . '.jpeg';
                if (file_exists($filePath))
                    $authorImage = $URI->getBaseUri(true) . 'cache/users/profile/' . $hash . '.jpeg';


                $filePath = $conf['path']['baseDir'] . 'cache/users/profile/' . $hash . '.jpg';
                if (file_exists($filePath))
                    $authorImage = $URI->getBaseUri(true) . 'cache/users/profile/' . $hash . '.jpg';


                $filePath = $conf['path']['baseDir'] . 'cache/users/profile/' . $hash . '.png';
                if (file_exists($filePath))
                    $authorImage = $URI->getBaseUri(true) . 'cache/users/profile/' . $hash . '.png';

                if (!empty($authorImage)) {
                    $authorImage = '
                    <div class="float-left">
                        <a href="' . $URI->getBaseUri() . 'user/showuser/' . $row['creation_user_ID'] . '/">
                            <img class="lazy img-fluid" 
                                 alt="Author pic" 
                                 style="max-width: 135px; max-height: 135px;" 
                                 data-src="' . $authorImage . '" />
                        </a>
                    </div> ';


                }

                $authorName = $language->get('wiki', 'showPageWrittenBy') . '<span class="fabCMS-Wiki-PageShowAuthor" onclick="toggleAuthorBox();">' . $row['article_signature'] . '</span>';
                /* $authorName = $language->get('wiki', 'showPageWrittenBy') . '<a href="' . $URI->getBaseUri() . 'user/showuser/' . $row['creation_user_ID'] . '/">
 ' . $row['article_signature'] . '</a>'; */
                break;
        }


    }

    $fabwiki->creationDate =  $row['creation_date'];
    $fabwiki->updateDate   =  $row['last_update'];

    // If author attribution is set then post the OpenGraph tag to the header
    if ((int) $fabwiki->config['authorAttribution'] > 1)
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

} else { // Item is in full page
    $template->fullPage = true;
    $core->jsVar['fabcms_isFullPage'] = 1;
}


if (!empty($row['title_alternative']))
    $title_alternative = ' - ' . $row['title_alternative'];

// Check if we have to pass the first tag to Google Tag Manager (analytics)
if ((int)$fabwiki->config['googleTagsManager'] === 1) {
    $this->addScript('var dimensionValue = \'' . htmlentities($tags_array[0]) . '\';
                      ga(\'set\', \'dimension1\', dimensionValue);');
}

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
    $debug->write('info','Content was not on memacache', 'wiki');
    $content = $fabwiki->parseContent($row['content']);

    if ($conf['memcache']['enabled'] === true) {
        $memcache->set('wikiPage-' . $trackback, $content, 604800);
    }
} else {
    $debug->write('info','Content was taken from memacache', 'wiki');
}

if (isset($_GET['printable'])){
    $currentUrl = $URI->getBaseUri() . 'wiki/' . $trackback . '/';
    echo '<p> ' . sprintf($language->get('wiki','pageShowPrintableHeader'), date('m-d-Y'), $currentUrl, $currentUrl) . '</p>';
}

// Check if the page has no banner
if ( (int) $row['no_banner'] != 1 && !isset($_GET['printable']) && !$user->isBot)
    $content = $fabwiki->parseBanner($content);

// Check if FabCMS should build a TOC
if ((int)$fabwiki->config['useToc'] === 1 && (int) $row['no_toc'] === 0)
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

if ((int) $row['no_title'] !== 1 )
    $template->pageTitle = $row['title'];

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
       <h1>' . $titleTag . '</h1>
        <!--FabCMS-hook:wikiInsideArticleTop-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleTop-->
        
        ' . $content . '
        <p>
            <em>Copyright ' . date('Y')  . ' - ' . $conf['organization'] .'</em>
        </p>
        <!--FabCMS-hook:wikiInsideArticleBottom-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleBottom-->
    </article>
    <!--FabCMS-hook:wikiSideBarAfterArticle-->
    <!--FabCMS-hook:wikiSideBarAfterArticle-tag-' . $tags_array[0] . '-->';

    return;
} else {

if ( isset($row['parser']) && strlen($row['parser']) > 1){
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
    <div class="col-md-9">
    <article>
        <div class="row">
        
            <div class="col-12 col-sm-12 col-md-' . ( (int) $row['no_info'] === 1 ? '12' : '8') . '">
                '. ( (int) $row['no_title'] === 1 ? '' : '<h1>' . $titleTag . '</h1>' ) . '
            </div>';

        if ( (int) $row['no_info'] !== 1 ) {
            echo '        <div class="col-md-4 d-none d-lg-block fabCMS-Wiki-PageInfoBox">
                <i class="fas fa-user"></i> ' . $authorName . '.<br/>
                <i class="fas fa-calendar"></i> ' .
                $language->get('wiki', 'wikiShowPagePublishedOn') . ' ' . $core->getDate($fabwiki->creationDate) . ' 
                ' . (empty($fabwiki->updateDate) ? '' : $language->get('wiki', 'wikiShowPageLastUpdate')) .  ' ' . $core->getDate($fabwiki->updateDate) . '
                ' . ($user->isAdmin === true ?  '<br/><i class="fas fa-edit"></i><a href="' . $URI->getBaseUri(true) . 'admin/admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">Edit</a>' : '' ) . '
                <div id="wikiAuthorBox" class="fabCMS-Wiki-PageAuthorBox">
                    ' . $authorImage .'
                    <div class="fabCMS-Wiki-wikiAuthorBoxName">'.   $row['article_signature'] .'</div>
                    <div class="FabCMS-Wiki-pageAuthorBoxShortBio mt-2">' . $row['short_biography'] . '</div>
                </div>
            </div>';
        }

        echo '
            
        </div>
        
        <script > 
            function toggleAuthorBox() {
                 $("#wikiAuthorBox").toggle();
            }
        </script>
        <!--FabCMS-hook:wikiInsideArticleTop-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleTop-->
        ' . $contentFeaturedVideo . '
        ' . $content . '
        <!--FabCMS-hook:wikiInsideArticleBottom-tag-' . $tags_array[0] . '-->
        <!--FabCMS-hook:wikiInsideArticleBottom-->
    </article>
    </div>
    <div id="wikiSidebar" class="col-md-3 wikiSidebar" style="background-color: var(--fabCMS-tertiary);">
    
          <!--FabCMS-hook:wikiSideBarFirstSpot-->
          <!--FabCMS-hook:wikiSideBarFirstSpot-tag-' . $tags_array[0] . '-->
          <form class="clearfix" action="' . $URI->getBaseUri() . 'search/simple/' . '" method="post">
            <div class="row">
                <div class="col-md" style="background-color: var(--fabCMS-primary); color: white; padding: 12px; font-size: x-large">
                    RICERCA
                </div>
            </div>
            <div class="row" style="padding: 12px; background-color: var(--fabCMS-secondary)">
                <div class="col-sm-9">
                    <input name="search" class="form-control">
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-dark  float-right" type="submit">Ricerca</button>    
                </div>
            </div>
            
            
            <!--FabCMS-hook:wikiSideBarLastSpot-tag-' . $tags_array[0] . '-->
            <!--FabCMS-hook:wikiSideBarLastSpot-->
          </form>
    </div>
</div>
' . ( (int) $fabwiki->config['showPageLicense'] === 1
            ? '<div class="fabCms-Wiki-CopyrightNotice"><i class="far fa-copyright"></i> &nbsp;' .
            sprintf($language->get('wiki','showPageCopyrightNotice' ),
                $URI->getBaseUri() . 'licenses/show/' . $row['license_ID'] . '/',
                $row['license_name'] )
            . '</div>'
            : '' ) . '
<!--FabCMS-hook:wikiSideBarAfterArticle-->
<!--FabCMS-hook:wikiSideBarAfterArticle-tag-' . $tags_array[0] . '-->';

}


}
/*
* ********************
* * Similar contents *
* ********************
*/

if (count($keywords_array) > 0 && (int)$row['no_similar_pages'] !== 1) {

    if ($conf['memcache']['enabled']  === true && $fabwiki->cacheExpired === 0)
        $similarPages = $memcache->get('wikiPageSimilarPages-' . $page_ID);

    if (empty($similarPages)) {
        $similarPages = '<section class="fabCMS-Wiki-SimilarPage mt-4">
                        <h2 class="fabCMS-Wiki-SimilarPageDescription">' . $language->get('wiki', 'showPageSimilarPages') . '</h2>' . PHP_EOL;

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

        foreach ($keywords_array AS $singleKeyword) {
            $query .= 'K.keyword = \'' . $core->in($singleKeyword) . '\' OR ';
        }
        $query = substr($query, 0, -3) . ')';

        $query .= 'GROUP BY P.ID ORDER BY no_keywords DESC LIMIT 6';

        if (!$resultSimilarPages = $db->query($query)) {

            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_page_similar_contents_error',
                'details'   => 'Cannot select the similar pages. Query error. ' . "\r\n" . $db->lastError . "\r\n" .$query,
            ]);

            $similarPages = 'Query error.';
        } else {

            if (!$db->affected_rows) {
                $similarPages .= '<div class="row">
                                <div class="col-lg-12"> ' . $language->get('wiki', 'showPageNoSimilarPage') . '</div>';
            } else {

                $i = 0;
                $gridSize = 4;
                $colSize = 12 / $gridSize;

                $similarPages .= '<div class="row"> <!-- Start of similar pages-->' . PHP_EOL;

                while ($row = mysqli_fetch_assoc($resultSimilarPages)) {
                    $i++;

                    $similarPages .= PHP_EOL . '<div class="col-lg-' . $colSize . '">' . PHP_EOL;
                    $imagePath = $conf['path']['baseDir'] . $row['image'];

                    $trackback = $URI->getBaseUri() . $this->routed . '/' . $row['trackback'] . '/';

                    $similarPages .= '<a href="' . $trackback . '">';
                    if (file_exists($imagePath) && !is_dir($imagePath)) {

                        $imageInfo = (pathinfo($row['image']));
                        $extension = $imageInfo['extension'];

                        $pos = strrpos($row['image'], '.' . $extension);

                        $imagePath = substr_replace($row['image'], '_thumb.' . $extension, $pos, strlen('.' . $extension));

                        $similarPages .= PHP_EOL . '
                                    <img style="max-width:150px;" 
                                         data-src="' . $URI->getBaseUri(true) . $imagePath . '" 
                                         class="lazy img-fluid" 
                                         alt="' . $row['title'] . '" />
                                ';
                    } else {
                        $similarPages .= '<img 
                                        style="max-width:150px;" 
                                        data-src="' . $URI->getBaseUri(true) . 'modules/wiki/res/noimage.png" class="lazy img-fluid" alt="No image placeholder" />';
                    }

                    $similarPages .= '</a>
                                  <br/>
                                  <a href="' . $trackback . '">' . $row['title'] . '</a>
                                  </div>' . PHP_EOL;

                    if ($i === $gridSize) {
                        $i = 0;
                        $similarPages .= PHP_EOL . '</div> <!-- end row-->
                        <div class="row"> <!-- Start new row -->';
                    }
                }


                for ($x = 0; $x < ($gridSize - $i); $x++) {
                    $similarPages .= '<div class="col-lg-' . $colSize . '"><!--empty--></div>';
                }

            }
        }

        $similarPages .= '</div></section> <!-- End similar pages-->';

        if ($conf['memcache']['enabled']  === true && $fabwiki->cacheExpired === 0)
            $memcache->set('wikiPageSimilarPages-' . $page_ID, $similarPages, 604800);
    }

    echo $similarPages;
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

        $relog->write(['type'      => '4',
                       'module'    => 'WIKI',
                       'operation' => 'wiki_page_show_select_comments_error',
                       'details'   => 'Cannot show comments. Query error. ' . $query,
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
                 <h4 class="fabCMS-Wiki-CommentDescription">Commenti alla pagine</h4>
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

echo PHP_EOL .'<script type="application/ld+json">
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
              WHERE ID = ' . $page_ID .' 
              LIMIT 1 ';

    $db->query($query);
}

$stats->write(['IDX' => $page_ID, 'module' => 'wiki', 'submodule' => 'pageView']);