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

class templateBase implements iFabTemplate
{
    public $title;
    public $template;
    public $templateVariant;
    public $head;
    public $scripts = [];
    public $navBar = [];
    public $menu = [];
    public $page;
    public $sidebar;
    public $prebody;

    public $fullPage;

    public $jsVar = [];
    public $hooks = [];
    public $hooksData = [];

    public $bypassNavBarGeneration = false;

    public $registeredParts = [];

    /**
     * This property stores the keywords generated by the module, ie: 'cars, tuning, energy';
     * @var
     */
    public $metaKeywords;

    /**
     * This property stores the description generated by the module;
     * @var
     */
    public $metaDescription;

    /**This property stores the "title/module"
     * @var
     */
    public $moduleH1;

    public $pageTitle;

    function fillAds()
    {
        global $db;
        global $log;
        global $core;
        global $debug;

        $debug->write('info', 'Calling fillAds', 'template');

        // Get the hooks from the page
        preg_match_all('/<!--FabCMS-hook:([a-z0-9\_\-\.\ \w]+)-->/mis', $this->page, $matches);

        if (count($matches) === 0)
            return;

        $hooks = '';
        foreach ($matches[1] as $singleMatch) {
            $hooks .= 'H.hook = \'' . $singleMatch . '\' OR ';
        }
        $hooks = substr($hooks, 0, -4);

        $query = '
        SELECT * 
          FROM ' . $db->prefix . 'sense_hooks AS H
        WHERE H.enabled = 1 AND H.lang = \'' . $core->shortCodeLang . '\'
        AND ' . $hooks;

        if ($result = $db->query($query)) {
            while ($rowHooks = mysqli_fetch_array($result)) {

                $query = '
                SELECT B.ID, 
                       B.code, 
                       B.probability, 
                       B.probability_progression_start, 
                       B.probability_progression_end,
                       FLOOR(' . rand(1, 100) . ') AS computed_prob
                FROM ' . $db->prefix . 'sense_hooks AS H
                LEFT JOIN ' . $db->prefix . 'sense_banner AS B 
                    ON B.hook_ID = H.ID
                WHERE H.enabled = 1 
                  AND H.hook = \'' . $rowHooks['hook'] . '\' 
                HAVING (probability_progression_start <= computed_prob) 
                       AND (probability_progression_end >= computed_prob)
                ORDER BY computed_prob ASC
                LIMIT 1';

                if (!$resultBanner = $db->query($query)) {
                    echo '<pre>' . $query . '</pre>';
                } else {

                    if ($db->affected_rows) {

                        $rowBanner = mysqli_fetch_array($resultBanner);

                        $this->hooks[]     = $rowHooks['hook'];
                        $this->hooksData[] = $rowBanner['code'];

                        $query = 'UPDATE ' . $db->prefix . 'sense_banner 
                                  SET hits = hits + 1 
                                  WHERE ID = ' . $rowBanner['ID'] . ' 
                                  LIMIT 1';

                        if (!$db->query($query)){
                            die ($query);
                        }

                    }
                }
            }
        }
    }

    /**
     * Get all the hooks stored in prefix_hooks table.
     */
    function fillHooks()
    {
        global $db;
        global $log;
        global $debug;
        global $core;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'hooks 
                  WHERE enabled = 1';


        $debug->write('info', 'Called fillHooks', 'TEMPLATE');

        if (!$data = $db->query($query)) {
            $debug->write('error', 'Query error while selecting templates', 'TEMPLATE');
            $log->write('template_hook_query_error', 'template', 'Query error: ' . $query);
        } else {


            while ($row = mysqli_fetch_array($data)) {
                $preData = json_decode($row['html'], true);
                if (json_last_error()) {
                    $debug->write('error', 'Cannot load the hook ' . htmlentities($row['name']) . '. Error while parsing JSON.', 'TEMPLATE');
                } else {
                    $debug->write('info', 'Hook ' . htmlentities($row['name']) . ' loaded.', 'TEMPLATE');
                    $this->hooks[] = $row['name'];
                    $this->hooksData[] = $preData[$core->shortCodeLang]['data'];
                }
            }
        }
    }

    public function loadTranslation() :bool {
        global $core;
        global $conf;
        global $log;
        global $lang;

        $file = $conf['path']['baseDir'] . 'templates/' . $this->template . '/lang/' . $core->shortCodeLang . '.php';

        if (file_exists($file)) {
            $log->write('info', 'template', 'Template language found. ' . $file );

            require_once $file;

            return true;
        } else {
            $log->write('info', 'template', 'Template language not found. ' . $file );

            return false;
        }
    }

    public function buildMenu($adminSide = false): string
    {
        global $db;
        global $core;
        global $URI;
        global $conf;

        if (file_exists($conf['path']['baseDir'] . 'templates/logo.png')){
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.png' .'" alt="Logo">';
        }

        if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpg')){
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.jpg' .'" alt="Logo">';
        }

        if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpeg')){
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.jpeg' .'" alt="Logo">';
        }


        $out = '
        <nav class="navbar navbar-static-top navbar-expand-lg navbar-light bg-light bg-faded">
          <a class="navbar-brand" href="' . $URI->getBaseUri() . '">' . $logoBrand . '<!--FabCMS:siteName--></a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
          <li class="nav-item">
                    <a class="nav-link" href="' . $URI->getBaseUri() . '">Home</a>
                </li>';

        if ($adminSide === true ) {
            foreach (glob($conf['path']['baseDir'] .'admin/modules/*') AS $menuFile){
                $menuPath = $menuFile . '/menu.php';
                if (file_exists($menuPath)){
                    require_once $menuPath;
                }
            }

        } else {
            $out .= $this->iterateMenu(0);
        }

        $out .= '</ul>
        <form method="post" action="' . $URI->getBaseUri() . 'search/simple/" class="form-inline">
            <input id="search" name="search" class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form></div></nav>';

        return $out;
    }

    function iterateMenu( int $parent_ID, bool $child = false, bool $sub = false)
    {
        global $db;

        $out = '';

        /*
         * Select all the menu, based on the parent_ID. Checks if the menù has children by counting them on "childs"
         */
        $query = '
        SELECT FF.*, 
        (
            SELECT COUNT(ID) AS childs 
            FROM fabcms_fabmenu FFP
            WHERE FFP.parent_ID = FF.ID
        ) childs 
        FROM fabcms_fabmenu FF
        WHERE parent_ID = ' . $parent_ID . ' 
        ORDER BY `order` ASC';

        if (!$result = $db->query($query)){
            echo '<pre>' . $query . '</pre>';
            return;
        }

        if (!$db->affected_rows)
            return;


        while ($row = mysqli_fetch_assoc($result)){

            if ($child) {
                if ($sub) {
                    $out .= '<li class="nav-item">
                                <a class="dropdown-item" href="#">' . $row['name'] . '</a>
                             </li>';

                } else {
                    $out .= '<li  class="dropdown-submenu">
                                <a class="dropdown-item" href="#">' . $row['name'] . '</a>
                             </li>';

                }

                if ( (int) $row['childs'] > 0 )
                    $out .= '<li class="dropdown-submenu">
                             
                             <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink-' . $row['ID'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                ' . $row['name'] . '
                             </a>
                             <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink-' . $row['ID'] .'">' . $this->iterateMenu($row['ID'], true, true) . '</ul>';
                continue;
            }

            if ( (int) $row['childs'] > 0) {
                $out .= '
                    <li class="nav-item dropdown">
                        
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink-' . $row['ID'] .'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            ' . $row['name'] . '
                        </a>
                        
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink-' . $row['ID'] .'">
                           ' . $this->iterateMenu($row['ID'], true, false) . '
                        </ul>
                     </li>';
            } else {
                $out .= '<li class="nav-item">
                            <a class="nav-link" href="' . $row['url'] . '">' . $row['name'] . '</a>
                         </li>';
            }
        }

        return $out;

    }

    function setPageTitle($pageTitle)
    {
        $this->pageTitle = htmlentities($pageTitle);
    }

    /**
     * Custom box
     *
     * Gets a custom box, by using standard bootstrap 3 classes.
     * Example: $template->getCustomBox( ['title' => 'Foo', 'message' => 'bar', 'class' => 'warning' ] )
     *
     * @param array $options
     *
     * @return string
     */
    function getCustomBox(array $options): string
    {
        return '<div class="alert alert-' . htmlentities($options['class']) . '">
                    <strong>' . $options['title'] . '</strong> ' . $options['message'] . '
                </div>';
    }

    function parse()
    {
        global $URI;
        global $module;
        global $debug;
        global $plugin;
        global $core;
        global $user;
        global $conf;
        global $db;

        if ($module->noTemplateParse) {
            $debug->write('info', 'Detected "noTemplateParse", so the buffer will output raw data', 'TEMPLATE');
            $this->page = $module->content;

            return;
        }




        // Adds code before closing <body> tag and in the <head> section
        if ($core->adminLoaded === true) {
            $beforeClosingBody = $core->getConfig('template', 'templateAdminBeforeBody', 'extended_value');
            $this->page = str_replace('</body>', $beforeClosingBody . '</body>', $this->page);

            $module->head .= $core->getConfig('template', 'templateAdminHead', 'extended_value');
        } else {
            $beforeClosingBody = $core->getConfig('template', 'templateBeforeBody', 'extended_value');
            $this->page = str_replace('</body>', $beforeClosingBody . '</body>', $this->page);
            $module->head .= $core->getConfig('template', 'templateHead', 'extended_value');
        }

        // Replace <!--FabCMS:lang--> with the real lang provided by config file
        $this->page = preg_replace('/<!--FabCMS:lang-->/', $core->shortCodeLang, $this->page);

        // Replace <!--FabCMS:siteName--> with the real site name
        $this->page = preg_replace('/<!--FabCMS:siteName-->/', $conf['site']['name'], $this->page);

        // Replace <!--FabCMS:siteSlogan--> with the slogan of the site
        $this->page = preg_replace('/<!--FabCMS:siteSlogan-->/', $conf['site']['slogan'], $this->page);

        // Replace <!--FabCMS:mainContent--> with the content provided by $module object
        $this->page = preg_replace('/<!--FabCMS:mainContent-->/', $module->content, $this->page);

        // Inject template/variant
        $templateUsed    = $core->getConfig('template', 'template') ?? 'fabtemplate';
        $templateVariant = $core->getConfig('template', 'templateVariant') ?? 'default.css';

        /*
        $this->page = preg_replace('/<!--FabCMS:head-->/',
                                '<!--FabCMS:head--><link type = "text/css" 
                                                                     rel="stylesheet" 
                                                                     href = "' . $URI->getBaseUri(true) . 'templates/' . $templateUsed . '/css/' . $templateVariant . '">', $this->page);
        */

        // Replace <!--FabCMS:head--> with the head provided by $module object
        $this->page = preg_replace('/<!--FabCMS:head-->/', $module->head, $this->page);

        // Replace <!--FabCMS:pageTitle--> with the page/module name or title
        $this->page = preg_replace('/<!--FabCMS:pageTitle-->/', $this->pageTitle, $this->page);

        // Replace <!--FabCMS:navBar--> with the navigation bar
        if ($this->bypassNavBarGeneration === true) {
            $debug->write('info', 'Bypassing NavBar generation into the template', 'TEMPLATE');
            $this->page = preg_replace('/<!--FabCMS:navBar-->/', '', $this->page);
        } else {
            $debug->write('info', 'Parsing NavBar into the template', 'TEMPLATE');
            $root = '<a href="' . $URI->getBaseUri() . '">Homepage</a>';
            $this->page = preg_replace('/<!--FabCMS:navBar-->/', $this->renderNavBar($root), $this->page);
        }

        // Replace <!--FabCMS:tracker--> with the tracker
        $this->page = preg_replace('/<!--FabCMS:tracker-->/', $conf['tracker'], $this->page);

        // Replace <!--FabCMS:sidebar--> with the sidebar
        $this->page = preg_replace('/<!--FabCMS:sidebar-->/', $this->sidebar, $this->page);

        // Replace <!--FabCMS:menu--> with the menu
        $this->page = preg_replace('/<!--FabCMS:menu-->/', $this->buildMenu( $core->adminLoaded ), $this->page);

        // Replace <!--FabCMS:currentURI--> with the current URI
        $this->page = preg_replace('/<!--FabCMS:currentURI-->/', "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], $this->page);

        // Replace <!--FabCMS:keywords--> with the keywords of the page
        $this->page = preg_replace('/<!--FabCMS:keywords-->/', $this->metaKeywords, $this->page);

        // Plugin chain
        $this->page = $plugin->pluginsChainPage($this->page);

        $this->fillAds();
        $this->fillHooks();

        $numHooks = count($this->hooks);
        for ($i = 0; $i < $numHooks; $i++) {
            $theHook = '<!--FabCMS-hook:' . $this->hooks[$i] . '-->';
            $this->page = str_replace($theHook, $this->hooksData[$i] . PHP_EOL . $theHook, $this->page);
        }

        // Plugins
        $this->page = $plugin->parsePlugin($this->page);

        //Scripts
        if (!strpos($this->page, '<!--FabCMS:scripts-->')) {
            die ('Please review your template because the script tag was not found.');
        }

        // Builds template variables
        if (!empty($core->jsVar)) {
            $templateVariables = '';
            foreach ($core->jsVar as $templateVar => $templateValue) {
                $templateVariables .= 'var ' . $templateVar . ' = ' . $templateValue . ';' . PHP_EOL;
            }
            $module->addScript($templateVariables);
        }

        // Adds all the scripts that we should load later (SEO)
        $module->scripts .= '<script>
                                $(function() {' . $core->getConfig('template', 'templateScripts' ,'extended_value') . '});
                             </script>';


        $this->page = preg_replace('/<!--FabCMS:scripts-->/', $module->scripts, $this->page);

        // Replace <!--FabCMS:path--> with the real path provided by URI object
        $this->page = preg_replace('/<!--FabCMS:path-->/', $URI->getBaseUri(), $this->page);

        // Replace <!--FabCMS:basePath--> with the real path provided by URI object
        $this->page = preg_replace('/<!--FabCMS:basePath-->/', $URI->getBaseUri(true), $this->page);

        // Replace <!--FabCMS:fullPath--> with the real path provided by URI object adding the language
        $this->page = preg_replace('/<!--FabCMS:fullBasePath-->/', $URI->getBaseUri(), $this->page);

        // Prebody
        $this->page = str_ireplace('</body>', $this->prebody . '</body>', $this->page);
        $this->page = preg_replace('/<\/body>/i', $this->prebody . '</body>', $this->page);

        if ($conf['debug']['enabled'] === true) {
            if ($user->isAdmin === true || $conf['debug']['showDebugToGuest'] === true)
                $this->page = preg_replace('/<!--FabCMS:debug-->/', '<div class="row"><div class="col-md-12">' . $this->getTabs('debugFabCMS', ['Log', 'MySql'], [$debug->getDebugData(), $dbDebug], ['tabType' => 'normal']) . '</div></div>', $this->page);
        }

    }

    function navBarAddItem($name, $navBarURI = '')
    {
        $this->navBar[$name] = $navBarURI;
    }

    function renderNavBar()
    {
        global $URI;

        $navBar = '
        <nav aria-label="breadcrumb">            
                <ol itemscope itemtype="https://schema.org/BreadcrumbList" class="breadcrumb" id="breadcrumb" aria-labelledby="breadcrumblabel">';

        $navBar .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="' . $URI->getBaseURI() . '" title="Home">
                            <span itemprop="name">Home</span>
                            <meta itemprop="position" content="1" />
                        </a>
                   </li>';

        $i = 3;

        foreach ($this->navBar as $name => $navBarURI) {
            $navBar .= '&nbsp;›&nbsp;';

            if (empty($navBarURI)) {
                $navBar .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <span itemprop="name">' . $name . '</span>
                                <meta itemprop="position" content="' . ++$i . '" />
                            </li>';
            } else {
                $navBar .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <a itemprop="item" itemprop="item" href="' . $navBarURI . '">
                                    <span itemprop="name">' . $name . '</span>
                                    <meta itemprop="position" content="' . ++$i . '" />
                                </a>
                            </li>';
            }

        }

        $navBar .= '</ol>
                </nav>';

        return $navBar;
    }

    public function getTabs(string $ID, array $tabsName, array $tabsContent, array $tabsConfig): string
    {
        global $core;

        if ($ID == -1)
            $ID = rand(0, 10000);

        $tabsHtml = '';
        $tabsHref = [];

        if (isset($tabsConfig['activeTab'])) {
            $activeTab = $tabsConfig['activeTab'];
        } else {
            $activeTab = 0;
        }

        $i = 0;
        foreach ($tabsName as $singleTab) {
            $tabsHref[] = $core->getTrackback($singleTab);

            $tabsHtml .= '
            <li class="nav-item">
                <a ' . ($i === $activeTab ? 'class="nav-link active" aria-selected="true"' : 'class="nav-link" aria-selected="false"') . ' href="#' . $core->getTrackback($singleTab) . '" 
                 id="home-tab-' . $core->getTrackback($singleTab) . '" 
                 data-toggle="tab"
                 role="tab" aria-controls="' . $core->getTrackback($singleTab) . '">' .
                $singleTab . '
                </a>
            </li>';
            $i++;
        }

        $tabsContentHtml = '';
        $i = 0;
        foreach ($tabsContent as $singleContent) {
            $tabsContentHtml .= '
            <div class="tab-pane container fade ' . ($i === $activeTab ? 'show active' : '') . '" id="' . $tabsHref[$i] . '">
                <p>' . $singleContent . '</p>
            </div>';

            $i++;
        }

        return '
                <ul class="nav nav-tabs" id="' . $ID . '" role="tablist">
                    ' . $tabsHtml . '
                </ul>
                <div class="tab-content">
                    ' . $tabsContentHtml . '
                </div>';
    }

    function getPanel(string $header, string $content, string $type, bool $collapsible = false, bool $startCollapsed = false): string
    {
        global $URI;
        global $core;

        switch ($type) {
            case 'success':
                $panelClass = 'panel-success';
                break;
            case 'danger':
                $panelClass = 'panel-danger';
                break;
            case 'warning':
                $panelClass = 'panel-warning';
                break;
            case 'dark':
                $panelClass = 'panel-default-dark';
                break;
            case 'light':
                $panelClass = 'panel-default-light';
                break;
            case 'info':
            default:
                $panelClass = 'panel-info';
        }


        // If the panel is collapsible build some JS
        if ($collapsible === true) {
            $ID = rand(0, 1000);
            $startCollapsed === true
                ? $theIcon = ' <i id="icon_' . $ID . '" onclick="changeState(\'' . $ID . '\');" class="fa fa-plus-square-o"></i>'
                : $theIcon = ' <i id="icon_' . $ID . '" onclick="changeState(\'' . $ID . '\');" class="fa fa-minus-square-o"></i>';

            $startCollapsed === true
                ? $theClass = 'hidden'
                : $theClass = '';

        }


        return '
            <div class="card">
              <div class="card-header">
                ' . $header . $theIcon .'
              </div>
              <div id="'.$ID.'" class="card-body ' . $theClass . '">
                <p>' . $content . '</p>
              </div>
            </div>';
    }

    public function simpleBlock(string $title, string $content): string
    {
        return '<div class="FabCMS-templateBlock">
                    <div class="FabCMS-templateBlock-title">' . $title . '</div>
                    <div class="FabCMS-templateBlock-content">' . $content . '</div>
                </div>';
    }
}