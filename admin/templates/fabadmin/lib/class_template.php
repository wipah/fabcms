<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 24/08/2018
 * Time: 10:50
 */

class template extends templateBase implements iFabTemplate
{
    function __construct()
    {
        global $conf;
        global $core;

        if ($core->adminLoaded === true) {
            $this->template = $conf['admin']['template'];
        } else {
            $this->template = $conf['template'];
        }

        $this->loadPage();

        // Ads
        $this->fillAds();
        $this->fillHooks();

    }

    public function getTabs(string $ID, array $tabsName, array $tabsContent, array $tabsConfig): string
    {
        return parent::getTabs($ID, $tabsName, $tabsContent, $tabsConfig);
    }

    public function getPanel(string $header, string $content, string $type, bool $collapsible = false, bool $startCollapsed = false): string
    {
        return parent::getPanel($header, $content, $type, $collapsible, $startCollapsed);
    }

    public function getCustomBox(array $options): string
    {
        return parent::getCustomBox($options);
    }

    public function simpleBlock(string $title, string $content): string
    {

        return '
        <div class="fabTemplateSimpleBlock">
            <div class="h3">' . $title . '</div>
            <div class="simpleBlockContent">' . $content . '</div>
        </div>
        ';
    }


    public function loadPage()
    {
        global $debug;
        global $conf;
        global $core;
        global $module;

        if ($core->adminLoaded === true) {
            $page_name = $conf['path']['baseDir'] . 'admin/templates/fabadmin/page.html';
            $this->page = file_get_contents($page_name);
        } else {
            $this->page = $this->getPageTemplate();
        }

    }


    public function getPageTemplate()
    {

        $page = '<!DOCTYPE html>
<html lang="<!--FabCMS:lang-->">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.theme.min.css" rel="stylesheet"
          type="text/css">

    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <link href="<!--FabCMS:basePath-->templates/fabtemplate/css/full.css" rel="stylesheet"
          type="text/css">
    <!--FabCMS:head-->
</head>

<body>
<div class="container wrapper">
    <div class="mainBody">

        <section class="topBar">
            <div class="row">
                <div class="col-md-8">
                    <!--FabCMS:menu-->
                </div>
                
                <div class="col-md-3">
                    <div class="fabCmsLogin" id="fabCmsLoginPlace">
                        <a href="<!--FabCMS:path-->user/">
                            <i class="ti-user"></i>Login</a></div>
                </div>

            </div>
        </section>

        <header>

            <div class="row">
                <div class="col-md-12">
                    <!--FabCMS-hook:beforeContent-->
                    <!--spot:beforeContent-->
                    <!--FabCMS:navBar-->
                    <!--FabCMS-hook:afterContent-->
                    <!--spot:afterContent-->
                </div>
            </div>
        </header>

        <div class="row mainArticle">

            <div id="fabCMSMainContent" class="'. ($this->fullPage === true ? 'col-md-12' : 'col-md-9') . '">
                <!--FabCMS:moduleH1-->
   
                <article>
                    <h1><!--FabCMS:pageTitle--></h1>
                    <div class="post-entry">
                        <!--FabCMS:mainContent-->
                    </div>
                </article>
            </div>';

        if (!$this->fullPage) {
            $page .= '<div id="fabCmsSidebar" class="col-md-3">
                <aside>
                    <!--FabCMS:sidebar-->
                </aside>
            </div>';
        }

        $page .= '
        </div>

        <div class="sectionSeparator"></div>
        <div class="row">
            <div class="col-md-12">

                <footer>
                    <!--FabCMS-hook:beforeFooter-->
                    <!--spot:beforeFooter-->
                    <!--FabCMS-hook:afterFooter-->
                    <!--spot:afterFooter-->
                </footer>
            </div>
        </div>

    </div>
</div>

<!--FabCMS:debug-->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha256-VsEqElsCHSGmnmHXGQzvoWjWwoznFSZc6hs7ARLRacQ=" crossorigin="anonymous"></script>

<script>
    $(\'.dropdown-menu a.dropdown-toggle\').on(\'click\', function (e) {
        if (!$(this).next().hasClass(\'show\')) {
            $(this).parents(\'.dropdown-menu\').first().find(\'.show\').removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass(\'show\');

        $(this).parents(\'li.nav-item.dropdown.show\').on(\'hidden.bs.dropdown\', function (e) {
            $(\'.dropdown-submenu .show\').removeClass("show");
        });

        return false;
    });

    if (typeof fabcms_isFullPage === \'undefined\') {
        fabcms_isFullPage = false;
    }

    $(function () {
        if (fabcms_userLogged == true) {
            $("#fabCmsLoginPlace").html("Bentornato " + fabcms_userUsername + ". <a href=\\\' / user / cp / \\\'>Pannello di controllo</a>");
        }
        
    });
</script>
</body>
<!--FabCMS:scripts-->
</html>';

        return $page;
    }
}