<?php

/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/10/2016
 * Time: 09:39
 */
class wiki
{
    public $config;
    public $menuLink = [];
    public $wikiLinks = [];
    public $existingPages = [];
    public $banner = [];
    public $parsers = [];
    public $images = [];
    public $authorName;
    public $authorSurname;
    public $authorUsername;
    public $creationDate;
    public $updateDate;
    public $title;
    public $metaDataDescription;
    public $trackback;
    public $publishedID = [];
    public $internalSpots = [];
    public $cacheExpired;
    public $mandatoryIDs = [];
    public $disabledParsers = [];
    public $parserNoLink = false;

    private $_hooks;

    function __construct()
    {
        $this->initParsers();

        global $db;
        global $core;
        global $relog;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'wiki_tags_menu
                  WHERE language = \'' . $core->shortCodeLang . '\' ORDER BY ID ASC;';

        $db->setQuery($query);
        if (!$result = $db->executeQuery('select')) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_class_construct',
                           'details'   => 'Cannot construct the class. Query error. ' . $query,
            ]);

            return;
        }

        while ($row = mysqli_fetch_array($result)) {
            $this->menuLink[(int)$row['depth']][strtolower($row['tag'])] = ['URI' => $row['URI'], 'name' => $row['name']];
        }

    }

    public function updateRevision($page_ID, $revision, $content, $user_ID){
        global $core;
        global $db;

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_revisions (page_ID, revision, content, update_date, user_ID)
        VALUES
        (
        \'' . $page_ID . '\',
        \'' . $revision . '\',
        \'' . $core->in($content) . '\',
        NOW(),
        \'' . $user_ID  .'\'
        )
        ';

        $db->setQuery($query);

        if (!$db->executeQuery('insert')){
            return false;
        } else {
            return true;
        }
    }

    private function initParsers()
    {
        global $db;
        global $relog;
        global $core;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'wiki_parsers 
                  WHERE enabled = 1 ORDER BY `order`';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {
            echo '<pre>Query error while loading parsers</pre>';
            $relog->write(['details'   => $query,
                           'type'      => 4,
                           'module'    => 'WIKI',
                           'operation' => 'initParsers']);

            return false;
        }

        while ($row = mysqli_fetch_array($result)) {
            $this->parsers[] = $row['parser'];
        }
    }
    public function isBetweenDate($startDate, $endDate)
    {
        return true; //Todo fix this bug
        if ((empty($startDate) || $startDate == '0000-00-00') && (empty($endDate) || $startDate == '0000-00-00'))
            return true;

        $todayDate = new DateTime(); // Today

        if (!empty($startDate) || $startDate != '0000-00-00') {
            $startDate = new DateTime($startDate);
        } else {
            $startDate = new DateTime('1900-00-00');
        }

        if (!empty($endDate) || $startDate != '0000-00-00') {
            $endDate = new DateTime($endDate);
        } else {
            $endDate = new DateTime('2080-00-00');
        }

        if ($todayDate->getTimestamp() > $startDate->getTimestamp() && $todayDate->getTimestamp() < $endDate->getTimestamp()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateFiles($ID, array $options = null)
    {
        global $db;
        global $core;
        global $relog;

        $ID = (int)$ID;

        $query = 'SELECT P.content 
                  FROM ' . $db->prefix . 'wiki_pages AS P
                  WHERE ID = ' . $ID . ' LIMIT 1';

        $db->setQuery($query);
        if (!$result = $db->executeQuery('select')) {


            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_files',
                           'details'   => 'Update files. Cannot select pages due to a query error. ' . $query,
            ]);


            return;
        }


        if (!$db->numRows) {

            $relog->write(['type'      => '3',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_files_pages_not_found',
                           'details'   => 'Pages were not found while updating files. ' . $query,
            ]);

            return;
        }

        $row = mysqli_fetch_assoc($result);

        $content = $row['content'];


        /**********
         * Images
         **********/
        if ($options['noDelete'] !== true) {
            $query = 'DELETE FROM ' . $db->prefix . 'wiki_pages_files WHERE page_ID = ' . $ID . ';';
            $db->setQuery($query);

            if (!$db->executeQuery('delete')) {


                $relog->write(['type'      => '4',
                               'module'    => 'WIKI',
                               'operation' => 'wiki_update_files_delete_page_error',
                               'details'   => 'Cannot delete files. Query error. ' . $query,
                ]);

                return;
            }
        }

        preg_match_all('/\[\$img\W?src=(.*?)\|(.*?)\$\]/ims', $content, $matches);

        $hasData = false;

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_files (
                  page_ID, fabmedia_ID, `type`, filename, title) VALUES ';

        foreach ($matches[0] as $singleMatch) {
            $hasData = true;

            //Gets filename
            preg_match('#\[\$img\W?src=(.*?)\|(.*?)\$\]#is', $singleMatch, $fullData);
            $fileName = $fullData[1];

            // Gets otherData
            $pieces = explode('||', $fullData[2]);
            foreach ($pieces as $singlePiece) {

                $subPieces = explode('==', $singlePiece);

                if (strtolower($subPieces[0]) == 'id')
                    $fabMedia_ID = $subPieces[1];

                if (strtolower($subPieces[0]) == 'alt')
                    $alt = $subPieces[1];

            }

            $query .= '
            (
                \'' . $ID . '\',
                \'' . $fabMedia_ID . '\',
                \'image\',
                \'' . $fileName . '\',
                \'' . $core->in($alt) . '\'
            ),';

        }

        if ($hasData === true) {
            $query = substr($query, 0, -1);

            $db->setQuery($query);
            if (!$db->executeQuery('insert')) {


                $relog->write(['type'      => '4',
                               'module'    => 'WIKI',
                               'operation' => 'wiki_update_files',
                               'details'   => 'Cannot update the files. Query error. ' . $query,
                ]);

                return false;
            }
        }

        return true;
    }

    public function loadConfig()
    {
        global $core;
        global $db;
        global $relog;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'wiki_config 
                  WHERE lang = \'' . $core->shortCodeLang . '\';';

        $db->setQuery($query);

        if (!$result = $db->executeQuery()) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_load_config',
                           'details'   => 'Cannot load config. Query error. ' . $query,
            ]);

            echo 'Query error. ';

            return;
        }

        while ($row = mysqli_fetch_array($result)) {
            $this->config[$row['param']] = $row['value'];
        }

    }

    public function getTranslatedPage($master_ID)
    {
        global $db;
        global $core;
        global $relog;

        $query = 'SELECT P.ID, 
                         P.title, 
                         P.trackback,
                         P.language
                  FROM      ' . $db->prefix . 'wiki_pages AS P
                  LEFT JOIN ' . $db->prefix . 'wiki_masters AS M
                  ON P.master_ID = M.ID
                  WHERE M.ID = ' . (int)$master_ID . ';';

        $db->setQuery($query);

        if (!$result = $db->executeQuery($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_get_translated_pages_query_error',
                           'details'   => 'Cannot get the translated pages. Query error. ' . $query,
            ]);

            return false;
        }

        $return = [];

        while ($row = mysqli_fetch_array($result)) {
            $return[] = $row['language'] . '|||' . $row['trackback'] . '|||' . $row['title'];
        }

        return $return;
    }

    public function parseInboundTracback($inboundTrackbacks)
    {
        global $language;
        global $URI;
        global $module;

        $inboundBody = '';

        if ($inboundTrackbacks !== false) {
            $entries = count($inboundTrackbacks);

            if ($entries === 0) {
                $inboundBody = $language->get('wiki', 'showPageNoInboundPages');
            } else {
                foreach ($inboundTrackbacks as $item => $value) {
                    $inboundBody .= '&bull; <a href="' . $URI->getBaseUri() . $module->routed . '/' . $item . '/">' . $value . '</a>';
                }
            }

        } else {
            $inboundBody = 'Query error.';
        }

        return $inboundBody;

    }

    public function createOutboundTrackbacks($ID, array $options = null)
    {
        global $db;
        global $core;
        global $relog;

        $ID = (int)$ID;

        $query = 'SELECT M.ID AS master_ID,
                         P.title,
                         P.trackback,  
                         P.content
                  FROM ' . $db->prefix . 'wiki_pages AS P
                  LEFT JOIN ' . $db->prefix . 'wiki_masters AS M
                  ON P.master_ID = M.ID
                  WHERE P.ID = ' . $ID . ';';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {


            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_create_outbound_trackbacks_query_error',
                           'details'   => 'Unable to select pages. ' . $query,
            ]);


            return false;
        }

        if (!$db->numRows) {

            $relog->write(['type'      => '3',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_create_outbound_trackbacks_page_not_exists',
                           'details'   => 'Cannot select the page. ' . $query,
            ]);

            return false;
        }

        $row = mysqli_fetch_assoc($result);
        $master_ID = $row['master_ID'];
        $content = $row['content'];

        preg_match_all('#\[\[(.*?)\]\]#m', $content, $matches);

        $outbound = [];
        $outboundName = [];

        foreach ($matches[1] as $singleMatch) {

            $singleMatch = explode('|', $singleMatch);

            if (in_array($singleMatch[0], $outbound))
                continue;

            $outbound[] = $core->getTrackback($singleMatch[0]);

            if (count($singleMatch) > 1) {

                $outboundName[$core->getTrackback($singleMatch[0])] = $singleMatch[1];
            } else {

                $outboundName[$core->getTrackback($singleMatch[0])] = $singleMatch[0];
            }

        }

        if ($options['noDelete'] !== true) {
            $query = 'DELETE FROM ' . $db->prefix . 'wiki_outbound_trackback 
                  WHERE page_ID = ' . $ID . ' AND 
                  master_ID = ' . $master_ID . ';';

            $db->setQuery($query);
            $db->executeQuery('delete');
        }

        // We have no outbound links here
        if (count($outbound) < 1)
            return;

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_outbound_trackback (page_ID, master_ID, trackback_page_ID, link_name)
                  VALUES ';

        foreach ($outbound as $singleOutbound) {
            $query .= '(\'' . $ID . '\', 
                        \'' . $master_ID . '\',
                        \'' . $singleOutbound . '\', 
                        \'' . $core->in($outboundName[$singleOutbound], true) . '\' 
                        
                        ), ';
        }

        $query = substr($query, 0, -2);

        $db->setQuery($query);

        if (!$db->executeQuery('insert')) {


            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_create_outbound_trackbacks_insert_query_error',
                           'details'   => 'Unable to store trackbacks. Query error. ' . $query,
            ]);

            return false;
        }

        return true;

    }

    public function getInboundTrackback($trackback)
    {
        global $db;
        global $core;
        global $relog;

        $trackback = $core->in($trackback);

        $query = 'SELECT T.trackback_page_ID, P.title, P.trackback
                  FROM ' . $db->prefix . 'wiki_outbound_trackback AS T
                  LEFT JOIN ' . $db->prefix . 'wiki_pages AS P
                  ON T.page_ID = P.ID
                  WHERE T.trackback_page_ID = \'' . $trackback . '\'
                  AND P.visible = 1
                  AND P.service_page = 0
                  ;';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_get_inbound_trackbacks_query_error',
                           'details'   => 'Cannot get inbound trackbacks. Query error. ' . $query,
            ]);
            return false;
        }

        $return = [];
        while ($row = mysqli_fetch_array($result)) {
            $return[$row['trackback']] = $row['title'];
        }

        return $return;
    }

    public function parseBanner($content)
    {
        global $mobileDetect;



        if ($mobileDetect->isMobile()) {
            if (isset($this->config['leftBannerMobile'])) {
                $content = '<div class="float-left">
                            <!--spot:beforeLeftBanner--> 
                            <!--hook:beforeLeftBanner--> ' .
                    $this->config['leftBannerMobile'] . '
                            <!--spot:afterLeftBanner-->
                            <!--hook:afterLeftBanner-->
                        </div>' . $content;
            }

            if (isset($this->config['rightBannerMobile'])) {
                $content = '<div class="right-left">
                            <!--spot:beforeLeftBanner--> 
                            <!--hook:beforeLeftBanner--> ' .
                    $this->config['rightBannerMobile'] . '
                            <!--spot:afterLeftBanner-->
                            <!--hook:afterLeftBanner-->
                        </div>' . $content;
            }


            if (isset($this->config['topBannerMobile'])) {
                $content = $this->config['topBannerMobile'] . $content;;
            }

            if (isset($this->config['bottomBannerMobile'])) {
                $content .= $this->config['bottomBannerMobile'];
            }

            if (isset($this->config['afterFirstPBannerMobile'])) {
                // Banner after the first paragraph
                $pos = strpos($content, '</p>');
                $content = substr($content, 0, $pos + 4) . '<!--spot:beforeFirstParagraphBanner-->' . $this->config['afterFirstPBannerMobile'] . substr($content, $pos + 4, strlen($content)) . '<!--spot:beforeFirstParagraphBanner-->';
            }

            if (isset($this->config['beforeFirstH2BannerMobile'])) {
                // Banner before the first h2
                $pos = strpos($content, '<h2');
                $content = substr($content, 0, $pos) . '<!--spot:beforeFirstH2hBanner--><!--hook:beforeFirstH2hBanner-->' . $this->config['beforeFirstH2BannerMobile'] . substr($content, $pos, strlen($content)) . '<!--spot:afterFirstH2Banner--><!--hook:afterFirstH2Banner-->';
            }

        } else if ($mobileDetect->isTablet()) {
            if (isset($this->config['leftBannerTablet'])) {
                $content = '<div class="float-left">
                            <!--spot:beforeLeftBanner--> 
                            <!--hook:beforeLeftBanner--> ' .
                    $this->config['leftBannerTablet'] . '
                            <!--spot:afterLeftBanner-->
                            <!--hook:afterLeftBanner-->
                        </div>' . $content;
            }

            if (isset($this->config['rightBannerTablet'])) {
                $content = '<div class="float-right">
                            <!--spot:beforeRightBanner--> 
                            <!--hook:beforeRightBanner--> ' .
                    $this->config['rightBannerTablet'] . '
                            <!--spot:afterRightBanner-->
                            <!--hook:afterRightBanner-->
                        </div>' . $content;
            }

            if (isset($this->config['topBannerTablet'])) {
                $content = $this->config['topBannerTablet'] . $content;;
            }

            if (isset($this->config['bottomBannerTablet'])) {
                $content .= $this->config['bottomBannerTablet'];
            }

            if (isset($this->config['afterFirstPBanner'])) {
                // Banner after the first paragraph
                $pos = strpos($content, '</p>');
                $content = substr($content, 0, $pos + 4) . '<!--spot:beforeFirstParagraphBanner--><!--hook:beforeFirstParagraphBanner-->' . $this->config['afterFirstPBanner'] . substr($content, $pos + 4, strlen($content)) . '<!--spot:afterFirstParagraphBanner--><!--hook:afterFirstParagraphBanner-->';
            }

            if (isset($this->config['beforeFirstH2BannerTablet'])) {
                // Banner before the first h2
                $pos = strpos($content, '<h2');
                $content = substr($content, 0, $pos) . '<!--spot:beforeFirstH2hBanner--><!--hook:beforeFirstH2hBanner-->' . $this->config['beforeFirstH2BannerTablet'] . substr($content, $pos, strlen($content)) . '<!--spot:afterFirstH2Banner--><!--hook:afterFirstH2Banner-->';
            }
        } else {
            if (isset($this->config['leftBannerPC'])) {
                $content = '<div class="float-left">
                            <!--spot:beforeLeftBanner--> 
                            <!--hook:beforeLeftBanner--> ' .
                    $this->config['leftBannerPC'] . '
                            <!--spot:afterLeftBanner-->
                            <!--hook:afterLeftBanner-->
                        </div>' . $content;
            }

            if (isset($this->config['rightBannerPC'])) {
                $content = '<div class="float-right">
                            <!--spot:beforeRightBanner--> 
                            <!--hook:beforeRightBanner--> ' .
                    $this->config['rightBannerPC'] . '
                            <!--spot:afterRightBanner-->
                            <!--hook:afterRightBanner-->
                        </div>' . $content;
            }

            if (isset($this->config['topBannerPC'])) {
                $content = $this->config['topBannerPC'] . $content;
            }

            if (isset($this->config['bottomBannerPC'])) {
                $content .= $this->config['bottomBannerPC'];
            }


            if (isset($this->config['afterFirstPBannerPC'])) {
                // Banner after the first paragraph
                $pos = strpos($content, '</p>');
                $content = substr($content, 0, $pos + 4) . '<!--spot:beforeFirstParagraphBanner--><!--hook:beforeFirstParagraphBanner-->' . $this->config['afterFirstPBannerPC'] . substr($content, $pos + 4, strlen($content)) . '<!--spot:beforeFirstParagraphBanner--><!--hook:beforeFirstParagraphBanner-->';
            }

            if (isset($this->config['beforeFirstH2BannerPC'])) {
                // Banner before the first h2
                $pos = strpos($content, '<h2');
                $content = substr($content, 0, $pos) . '<!--spot:beforeFirstH2hBanner--><!--hook:beforeFirstH2hBanner-->' . $this->config['beforeFirstH2BannerPC'] . substr($content, $pos, strlen($content)) . '<!--spot:afterFirstH2Banner--><!--hook:afterFirstH2Banner-->';
            }


        }

        return $content;
    }

    public function parseContent($content, $options = null)
    {
        global $db;
        global $core;
        global $language;
        global $template;

        if (!in_array('box', $this->disabledParsers)) {
            // {{box}} --> box
            $regex = ('/\{\{([^|\{\}\}]*)\}\}/i');
            $content = preg_replace_callback($regex,
                function ($matches) {
                    return $this->getDescriptionFromName($matches[1]);
                }
                , $content);

            // {{box|par1==value||par2==value}} --> box with parameters
            $regex = ('/\{\{([^\{\}\}\|]*)\|(.[^\}\}]*)\}\}/i');
            $content = preg_replace_callback($regex,
                function ($matches) {
                    return '<div>' . $this->getDescriptionFromName($matches[1], $matches[2]) . '</div>';
                }, $content);
        }

        // Parse any hooked spots
        if (isset($this->_hooks)) {
            foreach ($this->_hooks as $element => $elementContent) {
                $content = preg_replace('/\<\!\-\-spot\:' . $element . '\-\-\>/i', $elementContent . '<!--spot:' . $elementContent . '--->', $content);
            }
        }

        // [[link|Description of the link]]
        $content = preg_replace_callback('#\[\[(.[^\]\]]*?)\|(.*?)\]\]#uim',
            function ($matches) {
                global $core;

                if ($this->parserNoLink === true ) {
                    return $matches[2];
                } else {
                    $this->wikiLinks[] = $core->getTrackback($matches[1]);
                    return $this->createWikiLink($matches[1], $matches[2]);
                }

            },
            $content
        );

        // [[link]]
        $content = preg_replace_callback('#\[\[(.*?)\]\]#uim',
            function ($matches) {
                global $core;
                $matches[1] = html_entity_decode($matches[1]);
                $this->wikiLinks[] = $core->getTrackback($matches[1]);

                if ($this->parserNoLink === true) {
                    return $matches[1];
                } else {
                    return $this->createWikiLink($matches[1]);
                }

            },
            $content
        );

        // At this point check for outbound references
        if (count($this->wikiLinks) > 0) {

            $query = 'SELECT * 
                      FROM ' . $db->prefix . 'wiki_pages WHERE trackback = ';

            foreach ($this->wikiLinks as $wikiLink) {
                $query .= '\'' . $core->in($wikiLink) . '\' OR trackback = ';
            }

            $query = substr($query, 0, -15) . ';';

            $db->setQuery($query);
            if (!$result = $db->executeQuery('select')) {
                echo '<pre>Query error ' . $query . '</pre>';
            }

            if ($db->numRows) {
                while ($row = mysqli_fetch_array($result)) {
                    $this->existingPages[] = $row['trackback'];
                }
            }
        }

        $regex = '#\[!--FabCMS:InternalLink\|(.*?)\|--]#im';

        $content = preg_replace_callback($regex,
            function ($matches) use ($options) {
                global $core;
                global $module;
                global $URI;
                //global $options;

                $pieces = explode('|-|', $matches[1]);

                $trackback = $core->getTrackback($pieces[0]);

                if (in_array($trackback, $this->existingPages)) {
                    if (empty($pieces[1])) {
                        return '<a href="' . $URI->getBaseURI() . $module->routed . '/' . $trackback . '/">' . $pieces[0] . '</a>';
                    } else {
                        return '<a href="' . $URI->getBaseURI() . $module->routed . '/' . $trackback . '/">' . $pieces[1] . '</a>';
                    }
                } else {
                    if ($options['underLinkNotExistingPages'] === true) {
                        if (empty($pieces[1])) {
                            return '<u>' . $pieces[0] . '</u>';
                        } else {
                            return '<u>' . $pieces[1] . '</u>';
                        }
                    } else {
                        if (empty($pieces[1])) {
                            return $pieces[0];
                        } else {
                            return $pieces[1];
                        }
                    }

                }
            }, $content);

        // Parser engine
        $content = $this->parsers($content);

        // Parse any internal spots
        if (isset($this->internalSpots)) {
            foreach ($this->internalSpots as $element => $elementContent) {
                foreach ($elementContent as $singleElement) {
                    $content .= '[!hook=wikiInsideArticleBottom!]
                                    ' . $singleElement . '
                                 [!endhook!]';
                }
            }
        }

        // Hooks engine
        $content = $this->parseHooks($content);

        return $content;
    }

    public function parseToc($content){
        global $language;
        global $mobileDetect;
        global $conf;
        global $memcache;

        if (substr_count($content, '<h2') >= 2) {
            $toc = '<div style="display:block; padding: 8px; border: 1px solid grey; background-color: #e5e5e5;">
                    <strong>' . $language->get('wiki', 'pageShowToc') . '</strong>: ';

            $content = preg_replace_callback('#\<h2(.*)?\>(.*?)\</h2\>?#im',
                function ($matches) use (&$toc) {

                    global $core;

                    $toc .= '<a href="#' . $core->getTrackback($matches[2]) . '">' . $matches[2] . '</a> - ';

                    return ('<a name="' . $core->getTrackback($matches[2]) . '" ></a><h2 ' . $matches[1] . '>' . $matches[2] . '</h2>');

                }, $content
            );

            $toc = substr($toc, 0, -2) . '</div>';
            if ($mobileDetect->isMobile() === true ) {
                $pos = strpos($content, '</p>');

                $content = substr($content, 0, $pos + 4) . $toc . substr($content, $pos + 4, strlen($content));
            } else {
                $content = $toc . $content;
            }

        }



        return $content;
    }

    public function getDescriptionFromName($page, $param = null)
    {
        global $core;
        global $db;
        global $debug;
        global $user;
        global $relog;

        $page = $core->in($page, false);
        $debug->write('info', "Sarching for box. Passed params are: page: $page, param: $param, lang=$lang", 'CONTENTS');

        is_null($lang) == true ? $lang = $core->shortCodeLang : $lang = $core->in($lang);

        $query = 'SELECT ID, content' .
            ' FROM ' . $db->prefix . 'wiki_pages ' .
            ' WHERE title   = \'' . $page . '\'' .
            ' AND language  = \'' . $core->shortCodeLang . '\'' .
            ' AND visible = 1' .
            ' LIMIT 1';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_get_description_from_name_select_query_error',
                           'details'   => 'Cannot get the page. ' . $query,
            ]);

            return 'Query error with page selection. Name was ' . $page;
        }

        if (!$db->numRows) {
            if ($user->isAdmin)
                return '--- Admin message: box ' . $page . ' was not found ---';

        } else {

            $row = mysqli_fetch_array($result, MYSQLI_BOTH);

            $content = $row['content'];

            if ($param != null) {
                $dataTemp = explode('||', $param);
                $unusedParams = [];

                foreach ($dataTemp as $fragment) {
                    $fragmentArray = explode('===', $fragment);

                    // {{{variabile}}}
                    $search = '{{{' . $fragmentArray[0] . '}}}';
                    $debug->write('info', 'Box parsing. Searching for ' . $search, 'CONTENTS');

                    if (strpos($content, $search)) {
                        $debug->write('info', 'Box parsing. Found ' . $search . ' replacing with ' . $fragmentArray[1], 'CONTENTS');
                        $content = str_replace($search, $fragmentArray[1], $content);
                    } else {
                        // Imagine that a page has a parameter like {{box:anybox|foo==bar}} but foo doesn't exists.
                        // We store "foo" as a key of $unusedParams array and "bar" as the value.
                        // In other words:
                        // $unusedParams['foo'] = 'bar';
                        $unusedParams[$fragmentArray[0]][] = ($fragmentArray[1]); //@todo: probably the stripcslashes is buggy

                        $debug->write('info', 'Box parsing. The variable {{{' . $fragmentArray[0] . '}}} was not found in the page, but it was passed with the value:' . $fragmentArray[1], 'CONTENTS');
                    }
                }

                // {{{variable|p1=a|p2=b|p3=SB}}}
                $regex = '/\{\{\{([^\{\}\}\}\|]*)\|(.[^\}\}]*)\}\}\}/i';
                $content = preg_replace_callback($regex, function ($matches) use ($unusedParams) {
                    global $debug;
                    $debug->write('info', 'Parsing unused params', 'CONTENTS');

                    //@Todo: Eliminate all the mandatory IDS, maybe using SimpleXML.
                    return $this->parseBoxParam($matches[1], $matches[2], $unusedParams);

                }, $content);
            }

            // We have all the mandatoryIDs and we remove them, by checking for the ID
            if (count($this->mandatoryIDs) > 0 ) {
                $dom    =   new DOMDocument;
                $dom->validateOnParse = false;
                $dom->loadHTML( $content,LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
                $xp     =   new DOMXPath( $dom );

                foreach ($this->mandatoryIDs as $singleMandatory) {

                    $col = $xp->query( '//*[ @id="'. $singleMandatory .'" ]' );

                    if( !empty( $col ) ){
                        foreach( $col as $node ){
                            $node->parentNode->removeChild( $node );
                        }
                    }
                }

                $content = $dom->saveHTML();
                $dom     =   null;

            }

            // Is the page hooked on a specific spot?
            if (preg_match('/\[\!hookOnSpot\=(.*?)\!\]/i', $content, $matches)) {
                $spot = explode(':', $matches[1]);
                $spot = $spot[1];
                $debug->write('info', 'The page ' . $page . ' is hooked with the spot ' . $spot, 'CONTENTS');
                $this->_hooks[$spot] = $content;
            } else {
                return $content;
            }
        }
    }

    private function parseBoxParam($variableName, $passedParameter, $unusedParams)
    {
        global $debug;
        $parArray = explode('||', $passedParameter);

        foreach ($parArray as $fragment) {
            $fragmentArray = explode('===', $fragment);
            $param[$fragmentArray[0]] = $fragmentArray[1];
        }

        if (!isset($unusedParams[$variableName])) {
            if ($param['mandatory'] == 'true') {
                $debug->write('info', 'Box parsing. Found a mandatory field: ' . $variableName);
                $this->mandatoryIDs[] = $variableName;

            } else {
                if (isset($param['default'])) {
                    return $param['default'];
                } else {
                    return '*NA*';
                }
            }
        } else {
            return $param['pre'] . $unusedParams[$variableName][0] . $param['post'];
        }
    }

    private function createWikiLink($link, $name = null)
    {
        return '[!--FabCMS:InternalLink|' . $link . '|-|' . $name . '|--]';
    }

    private function parsers($content)
    {
        global $conf;
        global $relog;
        global $core;
        global $module;

        foreach ($this->parsers as $parser) {
            $filename = $conf['path']['baseDir'] . '/modules/wiki/parsers/parser_' . $parser . '.php';
            if (file_exists($filename)) {
                include $filename;
            } else {
                $relog->write(['module'    => 'WIKI',
                               'operation' => 'parsers',
                               'details'   => $filename,
                               'type'      => 3]
                );
                echo '<pre>' . $filename . 'doesn\' exist' . '</pre>';
            }
        }

        return $content;
    }

    private function parseHooks($content)
    {

        // Hooks (https://bitbucket.org/thewiper/fabcms/issue/8/sidebar-interactions && https://bitbucket.org/thewiper/fabcms/wiki/Hooks)
        $theRegex = '#\[!hook=([a-z0-9\-\_]+)!\](.*?)\[!endhook!\]#mis';

       $content = preg_replace_callback($theRegex,
            function ($theFirstMatch) {
                global $template;
                if (!is_array($theFirstMatch[0]))
                    return;


                for ($i = 0; $i < count($theFirstMatch[0]); $i++) {
                    $template->hooks[] = $theFirstMatch[1];
                    $template->hooksData[] = $theFirstMatch[2];
                }
            }, $content);

        return $content;
    }

    public function getTagsFromPageID($page_ID, $case)
    {
        global $db;

        $page_ID = (int)$page_ID;

        if ($conf['memcache']['enabled']  === true && $this->cacheExpired === 0)
            $return = $memcache->get('wikiPageTags-' . $page_ID);

        if (empty($return)) {
            $query = 'SELECT * 
                  FROM ' . $db->prefix . 'wiki_pages_tags 
                  WHERE page_ID = ' . $page_ID . ' 
                  ORDER BY ID ASC';

            $db->setQuery($query);

            $result = $db->executeQuery('select');

            $return = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $return[] = ($case == 'case_insensitive' ? strtolower($row['tag']) : $row['tag']);
            }

            if ($conf['memcache']['enabled']  === true)
                $memcache->set('wikiPageTags-' . $page_ID, $return, 604800);
        }
        return $return;
    }

    public function getKeywordsFromPageID($page_ID)
    {
        global $db;
        global $conf;
        global $memcache;


        $page_ID = (int) $page_ID;

        if ($conf['memcache']['enabled']  === true && $this->cacheExpired === 0)
            $return = $memcache->get('wikiPageKeywords-' . $page_ID);

        if (empty($return)) {

            $query = 'SELECT * 
                      FROM ' . $db->prefix . 'wiki_pages_keywords 
                      WHERE page_ID = ' . $page_ID;

            $db->setQuery($query);
            $result = $db->executeQuery('select');

            $return = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $return[] = $row['keyword'];
            }

            if ($conf['memcache']['enabled']  === true)
                $memcache->set('wikiPageKeywords-' . $page_ID, $return, 604800);

        }

        return $return;
    }

    public function updateFirstTag(int $page_ID) : bool
    {
        global $db;
        $query = 'SELECT ID 
                  FROM ' . $db->prefix . 'wiki_pages_tags 
                  WHERE page_ID = ' . $page_ID . ' 
                  LIMIT 1';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')){
            echo 'Query error. ' . $query;
            return false;
        }

        if (!$db->numRows) {
            echo 'No row';
            return false;
        }

        $row = mysqli_fetch_assoc($result);

        $query = 'UPDATE ' . $db->prefix . 'wiki_pages 
                  SET first_tag_ID = ' . $row['ID'] . '
                  WHERE ID = ' . $page_ID . '
                  LIMIT 1';

        $db->setQuery($query);

        if (!$db->executeQuery($query)){
            echo 'Query error ' . $query;
            return false;
        }

        return true;
    }

    public function updateFirstInternalTag(int $page_ID) : bool
    {
        global $db;
        $query = 'SELECT ID 
                  FROM ' . $db->prefix . 'wiki_pages_internal_tags 
                  WHERE page_ID = ' . $page_ID . ' 
                  LIMIT 1';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')){
            echo 'Query error. ' . $query;
            return false;
        }

        if (!$db->numRows) {
            echo 'No row';
            return false;
        }

        $row = mysqli_fetch_assoc($result);

        $query = 'UPDATE ' . $db->prefix . 'wiki_pages 
                  SET first_internal_tag_ID = ' . $row['ID'] . '
                  WHERE ID = ' . $page_ID . '
                  LIMIT 1';

        $db->setQuery($query);

        if (!$db->executeQuery($query)){
            echo 'Query error ' . $query;
            return false;
        }

        return true;
    }

    public function updateTags($page_ID, $tags)
    {
        global $core;
        global $db;
        $page_ID = (int)$page_ID;

        // Delete old references
        $query = 'DELETE FROM ' . $db->prefix . 'wiki_pages_tags WHERE page_ID = ' . $page_ID;
        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            echo 'Query error: ' . $query;
            return;
        }

        $tagsArray = explode(', ', $tags);
        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_tags (page_ID, tag, tag_trackback) VALUES';
        foreach ($tagsArray as $singleTag) {
            $query .= '(\'' . $page_ID . '\', \'' . $core->in($singleTag) . '\', \'' . $core->in($core->getTrackback($singleTag)) . '\'), ';
        }

        $query = substr($query, 0, -2);

        $db->setQuery($query);
        if (!$db->executeQuery('insert')) {
            echo 'Query error. ' . $query;
        }

        $this->updateFirstTag($page_ID);
    }

    public function updateInternalTags($page_ID, $tags)
    {
        global $core;
        global $db;
        $page_ID = (int) $page_ID;

        // Delete old references
        $query = 'DELETE FROM ' . $db->prefix . 'wiki_pages_internal_tags WHERE page_ID = ' . $page_ID;
        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            echo 'Query error: ' . $query;
            return;
        }

        $tagsArray = explode(', ', $tags);
        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_internal_tags (page_ID, tag) VALUES ';
        foreach ($tagsArray as $singleTag) {
            $query .= '(\'' . $page_ID . '\', \'' . $core->in($singleTag) . '\'), ';
        }

        $query = substr($query, 0, -2);

        $db->setQuery($query);
        if (!$db->executeQuery('insert')) {
            die ('Query error. ' . $query);
        }

        $this->updateFirstInternalTag($page_ID);
    }

    public function updateKeywords($page_ID, $keywords)
    {
        global $core;
        global $db;
        global $relog;

        $page_ID = (int)$page_ID;

        // Delete old references
        $query = 'DELETE 
                  FROM ' . $db->prefix . 'wiki_pages_keywords 
                  WHERE page_ID = ' . $page_ID;
        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_keywords_delete_query_error',
                           'details'   => 'Cannot delete while update keywords. Query error. ' . $query,
            ]);

            return;
        }

        $keywordsArray = explode(', ', $keywords);
        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_keywords (page_ID, keyword) VALUES';
        foreach ($keywordsArray as $singleKeyword) {
            $query .= '(\'' . $page_ID . '\', \'' . $core->in($singleKeyword) . '\'), ';
        }

        $query = substr($query, 0, -2);

        $db->setQuery($query);
        if (!$db->executeQuery('insert')) {
            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_keywords_query_error',
                           'details'   => 'Cannot update keywords. Query error. ' . $query,
            ]);

        }
    }

    public function updateSeoKeywords($page_ID, $keywords){
        global $core;
        global $db;
        global $relog;

        $page_ID = (int) $page_ID;

        // Delete old references
        $query = 'DELETE 
                  FROM ' . $db->prefix . 'wiki_pages_seo 
                  WHERE page_ID = ' . $page_ID;

        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_keywords_delete_query_error',
                'details'   => 'Cannot delete while update keywords. Query error. ' . $query,
            ]);

            return;
        }

        $keywordsArray = explode(', ', $keywords);

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_seo 
                  (page_ID, keyword, `order`) 
                  VALUES';

        $i = 0;
        foreach ($keywordsArray as $singleKeyword) {
            $query .= '(\'' . $page_ID . '\', \'' . $core->in($singleKeyword, true) . '\', ' . $i . '), ';
            $i++;
        }

        $query = substr($query, 0, -2);

        $db->setQuery($query);
        if (!$db->executeQuery('insert')) {
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_keywords_query_error',
                'details'   => 'Cannot update keywords. Query error. ' . $query,
            ]);

        }

        foreach ($keywordsArray as $singleKeyword) {
            $this->updateSeo($page_ID, $singleKeyword);
        }

        $this->updateSeoFirsKeyword($page_ID);
    }

    public function updateSeoFirsKeyword(int $page_ID) {
        global $db;
        global $core;
        global $relog;

        $query = 'SELECT score 
                  FROM ' . $db->prefix . 'wiki_pages_seo
                  WHERE page_ID = ' . $page_ID . '
                  ORDER BY `order` ASC
                  LIMIT 1';

        $db->setQuery($query);
        if (!$result = $db->executeQuery('select')) {
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_seo_first_keywords_query_error',
                'details'   => 'Cannot update keywords. Query error. ' . $query,
            ]);

            return -3;
        }

        if (!$db->numRows)
            return -4;

        $row = mysqli_fetch_assoc($result);

        $query = 'UPDATE ' . $db->prefix . 'wiki_pages
        SET seo_score = ' . $row['score'] . '
        WHERE ID = ' . $page_ID . '
        LIMIT 1';

        $db->setQuery($query);
        if (!$result = $db->executeQuery('insert')) {
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_seo_first_keywords_query_error',
                'details'   => 'Cannot update keywords. Query error. ' . $query,
            ]);
        }

        return true;
    }
    public function updateSeo( int $page_ID, string $keyword) {
        global $db;
        global $core;
        global $relog;

        $keyword = ($core->in(htmlentities($keyword)));
        $score = $this->computeSeo($page_ID, $keyword);

        // echo '---|--> Updating keyword ID:' . $page_ID . ', ' . $keyword . ', score ' . $score['score'] . PHP_EOL;

        $query = 'UPDATE ' . $db->prefix . 'wiki_pages_seo 
                  SET score = '   . $score['score']    .',
                      results = \'' . $score['analysis'] . '\'
                  WHERE page_ID = ' . $page_ID . ' 
                    AND keyword = \'' . $keyword . '\' 
                  LIMIT 1;';

        $db->setQuery($query);

        if (!$db->executeQuery('update')) {
            echo 'Query error. ' . $query;
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_update_seo_query_error',
                'details'   => 'Cannot update stats, cannot select. Query error. ' . $query,
            ]);

            return -2;
        }

        // echo 'Done updating. Result rowset is  ' . $db->numRows. PHP_EOL;
    }

    public function computeSeo($page_ID, $keyword)
    {
        global $db;
        global $relog;


        $page_ID = (int) $page_ID;

        // echo 'Computing SEO for page_ID: '. $page_ID;
        $query = 'SELECT PAGES.title, 
                         PAGES.content, 
                         PAGES.metadata_description,
                         STATS.headings,
                         STATS.words
                  FROM ' . $db->prefix . 'wiki_pages PAGES
                  LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics STATS
                    ON STATS.page_ID = PAGES.ID
                  WHERE PAGES.ID = ' . $page_ID . ' 
                  LIMIT 1';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {
            $relog->write(['type'      => '4',
                'module'    => 'WIKI',
                'operation' => 'wiki_compute_seo_query_error',
                'details'   => 'Cannot update stats, cannot select. Query error. ' . $query,
            ]);

            return -2;
        }

        if (!$db->numRows) {
            echo $query;
            return -3;
        }

        $row = mysqli_fetch_assoc($result);

        $score = 0;
        $analysis = '';

        // Keyword density @todo: check if we need some other kind of stats here, like linear regression.
        if (!empty($keyword)) {
            $density = (substr_count($row['content'], $keyword) * 100) / strlen(strip_tags($row['content']));
            if ($density < 0.5 ) {
                $analysis .= '--Keyword density is less than 0.5%.' . PHP_EOL;
            } elseif ($density >= 0.5 && $density < 1) {
                $analysis .= '==Keyword density is between 0.5% and 1%' . PHP_EOL;
            } elseif ($density >=1 && $density < 2) {
                $analysis .= '++Keyword density is between 1% and 2%' . PHP_EOL;
                $score += 10; // Maximum
            } elseif ($density >= 2) {
                $analysis .= '--Keyword density is more than 2%' . PHP_EOL;
            }
            $analysis .= '<br/>';
        }

        // Total words
        $words = (int) $row['words'];
        if ( $words < 100) {
            $analysis .= '--Less than 100 words.' .PHP_EOL;
            $score += 5;
        } elseif ( $words >= 100 && $words < 300) {
            $analysis .= '--Less than 300 words.' .PHP_EOL;
            $score += 15;
        } elseif ($words >= 300 && $words <= 600) {
            $analysis .= '==Less than 600 words.' .PHP_EOL;
            $score += 35;
        } elseif ($words > 600) {
            $analysis .= '++More than 600 words.' .PHP_EOL;
            $score += 50; // Maximum
        }
        $analysis .= '<br/>';

        // Headings
        $headings = (int) $row['headings'];
        if ($headings < 1) {
            $analysis .= '--Less than one heading.' .PHP_EOL;
            $score += 0;
        } elseif ($headings >= 1 && $headings < 2) {
            $analysis .= '--Less than 2 headings.' .PHP_EOL;
            $score +=5;
        } elseif ($headings >= 3 && $headings < 5) {
            $analysis .= '==Less than 5 headings.' .PHP_EOL;
            $score +=10;
        } elseif ($headings >= 6) {
            $analysis .= '++More than 5 headings.' .PHP_EOL;
            $score += 15; // Maximum
        }
        $analysis .= '<br/>';

        // img with keyword in alt
        // $regex = '/<img(.*)?alt="(.*' . $keyword .'.*)?">/i';
        $regex = '/\[\$img src="(.*)?"\|(.*)?|alt==(.*?' . $keyword . '.*?)\|\|/i';

        preg_match($regex, $row['content'], $matches, PREG_OFFSET_CAPTURE, 0);
        if (is_array($matches[1])) {
            $analysis .= '++At least one img (n= ' . count($matches) . ') has the alt with the keyword.' . PHP_EOL;
            $score += 5; // Maximum
        } else {
            $analysis .= '--No img has the alt with the keyword.' .PHP_EOL;
        }
        $analysis .= '<br/>';

        // Keyword in metadata description
        if (false !== stripos($row['metadata_description'], $keyword)) {
            $analysis .= '++Keyword is in the metadata description.' .PHP_EOL;
            $score += 10; // Maximum
        } else {
            $analysis .= '--Keyword is not the metadata description.' .PHP_EOL;
        }
        $analysis .= '<br/>';

        // Keyword in title

        if (false !== stripos($row['title'], $keyword)) {
            $analysis .= '++Keyword is in the title.' .PHP_EOL;
            $score += 10; // Maximum
        } else {
            $analysis .= '--Keyword is not in the title.' .PHP_EOL;
        }
        $analysis .= '<br/>';

        // echo 'Returning score: ' . $score . PHP_EOL;
        return ['score' => $score, 'analysis' => $analysis];

}

    public function updateStats($page_ID)
    {
        global $db;
        global $relog;

        $page_ID = (int)$page_ID;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'wiki_pages 
                  WHERE ID = ' . $page_ID . ' 
                  LIMIT 1';

        $db->setQuery($query);

        if (!$result = $db->executeQuery('select')) {
            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_stats_select_query_error',
                           'details'   => 'Cannot update stats, cannot select. Query error. ' . $query,
            ]);

            return -2;
        }

        if (!$db->numRows) {
            echo $query;

            return -2;
        }

        $row = mysqli_fetch_assoc($result);

        $query = 'DELETE FROM ' . $db->prefix . 'wiki_pages_statistics 
                  WHERE page_ID = ' . $page_ID . ' LIMIT 1';
        $db->setQuery($query);

        if (!$db->executeQuery('delete')) {
            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_stats_keywords_delete_query_error',
                           'details'   => 'Cannot delete while updating stats. Query error. ' . $query,
            ]);

            return -2;
        }

        $content = $row['content'];

        // Characters
        $char = strlen($content);

        // words
        $words = str_word_count($content);

        // tables
        $theRegex = '#\{\|(.*?)\|\}#ism';
        preg_match_all($theRegex, $content, $matches);
        $tables = count($matches[0]);

        // images
        $theRegex = '/\[\$img src=([\a-z0-9\.\-\_\w\:\/]+)\|?(.*?)\$\]/ims';
        preg_match_all($theRegex, $content, $matches);
        $images = count($matches[0]);

        // links
        $query = 'SELECT COUNT(ID) AS total  
                  FROM ' . $db->prefix . 'wiki_outbound_trackback 
                  WHERE page_ID = ' . $page_ID . '
                  GROUP BY page_ID';

        $db->setQuery($query);
        if (!$resultLinks = $db->executeQuery('select')) {

            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_stats_outbounds_query_error',
                           'details'   => 'Cannot update outbounds trackback. Query error. ' . $query,
            ]);

            return -3;
        }

        if (!$db->numRows) {
            $links = 0;
        } else {
            $rowLinks = mysqli_fetch_assoc($resultLinks);
            $links = $rowLinks['total'];
        }

        // Headings
        $regex = '/<h(\d)>(.*)?<\/h(\d)>/i';
        preg_match_all($regex, $content, $matches);
        $headings = count($matches);

        $query = 'INSERT INTO ' . $db->prefix . 'wiki_pages_statistics
        (
            page_ID,
            characters,
            words,
            tables,
            images,
            links,
            headings
        )
        VALUES
        (
            ' . $page_ID . ',
            ' . $char . ',
            ' . $words . ',
            ' . $tables . ',
            ' . $images . ',
            ' . $links . ',
            ' . $headings . '
        )
        ';

        $db->setQuery($query);

        if (!$db->executeQuery('insert'))
        {
            $relog->write(['type'      => '4',
                           'module'    => 'WIKI',
                           'operation' => 'wiki_update_stats_insert_error',
                           'details'   => 'Cannot update stats. Query error. ' . $query,
            ]);

            return -4;
        }

        return 1;
    }
}