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

    public function buildMenu($adminSide = false): string
    {
        global $db;
        global $core;
        global $URI;
        global $conf;
        global $user;
        global $language;


        if (file_exists($conf['path']['baseDir'] . 'templates/logo.png')) {
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.png' . '" alt="Logo">';
        }

        if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpg')) {
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.jpg' . '" alt="Logo">';
        }

        if (file_exists($conf['path']['baseDir'] . 'templates/logo.jpeg')) {
            $logoBrand = '<img class="headerLogo img-fluid" src="' . $URI->getBaseUri(true) . 'templates/logo.jpeg' . '" alt="Logo">';
        }


        $out = '
        <nav class="navbar navbar-expand-md navbar-dark flex-column flex-md-row bd-navbar fabCms-navBar fabCMS-headerMenu">
          
          <a class="navbar-brand" href="' . $URI->getBaseUri() . '">' . $logoBrand . '</a>
          
          <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="myNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          
          <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="' . $URI->getBaseUri() . '">Home</a>
                </li>';

        if ($adminSide === true) {
            foreach (glob($conf['path']['baseDir'] . 'admin/modules/*') as $menuFile) {
                $menuPath = $menuFile . '/menu.php';
                if (file_exists($menuPath)) {
                    require_once $menuPath;
                }
            }

        } else {
            $out .= $this->iterateMenu(0);
        }

        $out .= '</ul>';
        /* OLD CART
          echo '<span class="navbar-text">';

        if (!is_object($fabShop)) {
            require_once $conf['path']['baseDir'] . 'modules/shop/lib/class_shop.php';
            $fabShop = new \CrisaSoft\FabCMS\shop();
        }

        if ($user->logged) {
            $cart_ID = $fabShop->userHasCart($user->ID);
        } else {
            $cart_ID = $fabShop->anonymousHasCart();
        }

        if ($cart_ID < 0) {
            $out .= '<a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/">' . $language->get('shop', 'pluginCartWidgetNoItems') . '</a>';
        } else {
            $values = $fabShop->getCartValues($cart_ID);

            if ($values < 0) {
                $out .= '<a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/">' . $language->get('shop', 'pluginCartWidgetNoItems') . '</a>';

            } else {
                (int) $values['items'] === 1 ? $measure = 'articolo' : $measure = 'articoli';

                $out .= '<ins class="fa fa-shopping-cart shoppingCartIcon" aria-label="true"></ins>&nbsp;
                         <a class="shoppingCartLink" href="' . $URI->getBaseUri() . 'shop/cart/">' . $values['items'] . ' ' . $measure . '</a>&nbsp;
                         <ins class="fa fa-euro shoppingCartIcon" aria-label="true"></ins>&nbsp;
                         <span class="shoppingCartLink">' .  ( $values['taxable']) . '</span>';

            }

        }


        $out .= '</span> */
        $out .= '
                 <span class="navbar-text fabCmsLogin" id="fabCmsLoginPlace">';

        if ($user->logged === true) {
            $out .= sprintf($language->get('user','templateWelcomeUsername'), $user->username ). ' 
                      <a href="' . $URI->getBaseUri() . 'user/cp/">' . $language->get('user', 'templateControlPanel', null) . '</a> | 
                      <a href="' . $URI->getBaseUri() . 'user/logout/"> ' . $language->get('user', 'templateLogout', null) . ' </a>';
        } else {
            $out .=  '<a href="' . $URI->getBaseUri() . 'user/">' . $language->get('user', 'templateLoginOrSignUp', null) . '</a>';
        }

        $out .=
            '
			</span>
			               
            <form method="post" action="' . $URI->getBaseUri() . 'search/simple/" class="form-inline d-flex ms-auto" style="margin-right: 30px;">
                <input id="search" name="search" class="form-control mr-sm-2" type="search" placeholder="Cerca" aria-label="Search">
                <button class="btn FabCMS-navBar-Search my-2 my-sm-0" type="submit">Cerca</button>
            </form>

          </div>
        </nav>';

        return $out;
    }

    function iterateMenu( int $parent_ID, bool $child = false, bool $sub = false)
    {
        global $db;
        global $memcache;
        global $conf;

        if ($conf['memcache']['enabled'] === true)
            $out = $memcache->get('templateMenu');


        if (empty($out)) {
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
                                <a class="dropdown-item" href="#"> Y ' . $row['name'] . '</a>
                             </li>';

                    } else {
                        $out .= '<li  class="dropdown-submenu">
                                <a class="dropdown-item" href="' . $row['url'] . '">' . $row['name'] . '</a>
                             </li>';

                    }

                    if ( (int) $row['childs'] > 0 )
                        $out .= '<li class="dropdown-submenu">
                                 <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink-' . $row['ID'] . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    ' . $row['name'] . '
                                 </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink-' . $row['ID'] .'">' . $this->iterateMenu($row['ID'], true, true) . '</ul>';
                    continue;
                }

                if ( (int) $row['childs'] > 0) {
                    $out .= '
                    <li class="nav-item dropdown">
                        
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink-' . $row['ID'] .'" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

            if ($conf['memcache']['enabled'] === true) {
                $memcache->set('templateMenu', $out);
            }

        }

        return $out;

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
        global $user;

        if ($core->adminLoaded === true) {
            $page_name = $conf['path']['baseDir'] . 'admin/templates/fabadmin/page.html';
            $this->page = file_get_contents($page_name);
        } else {
            $this->page = $this->getPageTemplate();
        }
    }


    public function getPageTemplate()
    {
        global $user;
        global $URI;
        global $language;

        strlen($this->pageTitle) > 0 ? $pageTitle = '<h1><!--FabCMS:pageTitle--></h1>' : '';


        $page = /** @lang XHTML */
            <<<PAGE
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><!--FabCMS:pageTitle--></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,300;0,400;0,700;1,300;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
:root {
    --fabPrimary: #174F6D;
    --fabSecondary: #4a4a4a;
    --fabTertiary: #d98726;
    --fabTextOnDark: #D7D9D7;
    --fabWarning: #A31621;
    --fabOk: #04724D;
    --fabTextOnBright: #130303;
}

body {
    font-family: 'Open Sans',Fallback,sans-serif;
    background-color: #f4f4f8;
    color: #102027;
    margin-top: 0;
    text-align: justify;
    
    font-size: .93rem;
    line-height: 1.39rem;
    margin-bottom: 16px;
    letter-spacing: .01em;
}

header {
    background-color: var(--fabPrimary);
    color: var(--fabTextOnDark);
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 1040;
}

.preHeader {
    border-bottom: 1px solid var(--fabTextOnDark);
    padding-bottom: 6px;
}

.preHeader a {
    color: var(--fabTextOnDark);;
}

.preHeader a:hover {
    background-color: var(--fabTextOnDark);
    color: var(--fabPrimary);
}

.content {
    padding-top: 20px;
}

.nav-tabs {
    background-color: var(--fabPrimary);
}

.navbar-light .navbar-nav .nav-link {
    color: white;
}

footer {
    background-color: #343a40;
    color: #f0f0f0;
    padding: 20px 0;
    margin-top: 30px;
}

.breadcrumb {
    background-color: #e9ecef;
    padding: 10px 15px;
    border-radius: 5px;
    margin-top: 24px;;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: ">";
}

li {
    line-height: 1.7;
}
 
.FabCMS-navBar-Search{
    background-color: var(--fabTextOnDark);
    color: var(--fabPrimary)
}
.wikiArticle ul:not(.breadcrumb) {
  list-style: none; /* Rimuove il marker predefinito */
  padding: 0;
}

.wikiArticle ul:not(.breadcrumb .wikiArticle) li {
  position: relative;
  padding-left: 28px; /* Spazio per il marker personalizzato */
}

.wikiArticle ul:not(.breadcrumb .wikiArticle) li::before {
  content: ''; /* Quadrato vuoto */
  position: absolute;
  left: 0;
  top: 50%;
  width: 20px;
  height: 20px;
  background-color: var(--fabPrimary); /* Colore del quadrato */
  border: 1px solid var(--fabPrimary);; /* Sfondo nero */
  transform: translateY(-50%);
}
 
.wikiArticle ol:not(.breadcrumb) {
  list-style: none; /* Rimuove il marker predefinito */
  padding: 0;
  counter-reset: item;
}

.wikiArticle ol:not(.breadcrumb) li {
  counter-increment: item;
  position: relative;
  padding-left: 25px; /* Spazio per il marker personalizzato */
}

.wikiArticle ol:not(.breadcrumb) li::before {
  content: counter(item); /* Visualizza il numero */
  position: absolute;
  left: 0;
  top: 50%;
  width: 20px;
  height: 20px;
  background-color: var(--fabPrimary); 
  color: var(--fabTextOnDark);
  text-align: center;
  line-height: 20px; /* Allinea verticalmente il testo */
  transform: translateY(-50%);
}
.wikiEditArticle {
    padding: 4px;
    border: 1px solid var(--fabPrimary);
    background-color: white;
    text-transform: uppercase;
    text-align: right;
}
.approfondimento {
    background-color: #6c757d;
    color: #f0f0f0;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Aggiunta ombra */
    background-image: url('path-to-information-icon.png');
    background-repeat: no-repeat;
    background-position: right bottom;
    background-size: 50px 50px;
    opacity: 0.9;
}
        
.sidebar {
            position: sticky;
            top: 125px; /* Regolato per allinearsi sotto l'header */
            max-height: calc(100vh - 85px);
            overflow-y: auto;
}

.sidebar-block, .sidebar-block-alt {
    background-color: var(--fabPrimary);
    color: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.sidebar-block h3 {
    text-transform: uppercase;
    border-bottom: 1px solid white;
}

.sidebar-block-alt h3 {
    text-transform: uppercase;
    border-bottom: 1px solid white;
}
.sidebar-block-alt {
    background-color: var(--fabTertiary);
    color: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.sidebar-block a {
color: var(--fabTextOnDark);
}
h1, h2, h3, h4, h5, h6 {
   
    text-align: left;
}

h1 {
font-size: 2em;
}

h2 {
    background-color: var(--fabSecondary) ;
    color: white;
    padding: 6px;
    font-size: larger;
    font-weight: lighter;
    position: sticky;
    top: 120px;
    z-index: 1000;
}

.sidebar h3 {
    margin-top: 0;
}

.table-custom {
    background-color: #ffffff;
    border-collapse: collapse;
    width: 100%;
}

.table-custom th, .table-custom td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

p {
    line-height: 1.7;
    margin-bottom: 20px;
}

a {
    color: var(--fabPrimary);
}

a:hover {
    color: #0056b3;
}

.rowContainer {
background-color: white;
}


/* Wiki */
 .article-card {
    display: flex;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

.article-content {
    flex: 3;
    padding-right: 20px;
}

.article-meta {
    flex: 1;
    text-align: center;
}

.article-meta img {
    width: 100%;
    border-radius: 50%;
    margin-bottom: 10px;
}

.article-meta h3 {
    margin: 0;
    font-size: 1.2em;
}

.article-meta p {
    margin: 5px 0;
}

.article-description {
    padding: 10px;
    background-color: #e9ecef; /* Sfondo leggermente differente per enfasi */
    border-radius: 5px;
    margin-bottom: 10px; /* Spazio prima del meta dell'autore */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombreggiatura leggera per risalto */
}

.sticky-h2 {
    position: sticky;
    position: sticky;
    top: 125px; /* Adegua in base all'altezza dell'header e H1 */

    z-index: 1020;
   /* box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2); */

    margin-top: 0;
    width: 100%;
}

.video-card {
    background-color: #343a40; /* Sfondo scuro */
    color: white;
    margin-top: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
}

.video-section {
    background-color: #343a40; /* Sfondo scuro */
    padding: 10px;
    border-radius: 5px;
    max-height: 400px; /* Imposta un'altezza massima */
    overflow: hidden;
}

.video-section video {
    max-height: 100%;
    height: auto;
    width: auto;
    max-width: 100%;
    border-radius: 5px;
}

.video-section, .info-section {
    transition: flex-basis 0.5s ease; /* Transizione morbida */
    overflow: hidden; /* Nasconde il contenuto in eccesso durante la transizione */
}

.video-horizontal .video-section {
    flex: 2; /* Maggiore spazio per video orizzontali */
}

.video-horizontal .info-section {
    flex: 1; /* Meno spazio per le info sui video orizzontali */
}

.info-section {
    padding: 10px;
}

@media (max-width: 767.98px) {

    .video-section, .info-section {
        flex-basis: 100%; /* Full width su dispositivi mobili */
    }
    
    .article-card {
        flex-direction: column;
    }

    .article-content, .article-meta {
        padding-right: 0;
        text-align: center;
    }
}

@media (max-width: 991.98px) {
    .content .col-md-8, .content .col-md-4 {
        order: 0; /* Ordine predefinito per garantire che la barra laterale vada sotto il contenuto */
    }
    
     .content .col-md-8, .content .col-md-4 {
        order: 0;
    }

    .sidebar {
        position: relative;
        top: 0;
        max-height: 100%;
    }
}
</style>
<!--FabCMS:head-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<header>
    <div class="preHeader">
        <div style="text-align: right">
            <a href="https://formazionecrisafulli.com">FORMAZIONE E CORSI ONLINE</a> &diamond; <a href="/wiki/biologiawiki:lavora-con-noi/">LAVORA CON NOI</a> &diamond; <a href="/contacts">CONTATTACI</a>
        </div>
    </div>
    <!--FabCMS:menu-->
</header>

<div class="container">
    <div class="row rowContainer">
        <div class="col-md-12">
        <!--FabCMS:navBar-->
        <!--spot:beforeContent-->
        <!--FabCMS-hook:beforeContent-->
        <!--FabCMS:mainContent-->
        <!--spot:afterContent-->
        <!--FabCMS-hook:afterContent-->
        </div>
    </div>
</div>

<footer>
        <!--spot:beforeFooter-->
        <!--FabCMS-hook:beforeFooter-->
        <!--spot:afterFooter-->
        <!--FabCMS-hook:afterFooter-->
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>   
     
document.addEventListener('DOMContentLoaded', function() {
    var video = document.getElementById('featuredVideo');
    var videoColumn = document.getElementById('videoColumn');
    var infoColumn = document.getElementById('infoColumn');

    if (video === null) {
        console.log("L'elemento video non esiste nel DOM.");
        return;
    } else if (video instanceof HTMLVideoElement) {
        console.log("L'elemento video è stato trovato e è valido.");
    } else {
        console.log("L'elemento con id 'featuredVideo' esiste nel DOM ma non è un elemento video valido.");
        return;
    }
    
    var checkVideoSize = setInterval(function() {
        if (video.videoWidth && video.videoHeight) {
            clearInterval(checkVideoSize);

            if (video.videoWidth > video.videoHeight) {
                // Video è orizzontale
                videoColumn.classList.add('col-lg-8');
                videoColumn.classList.remove('col-lg-4', 'col-12');
                infoColumn.classList.add('col-lg-4');
                infoColumn.classList.remove('col-lg-8', 'col-12');
            } else {
                // Video è verticale o quadrato
                videoColumn.classList.add('col-lg-4');
                videoColumn.classList.remove('col-lg-8', 'col-12');
                infoColumn.classList.add('col-lg-8');
                infoColumn.classList.remove('col-lg-4', 'col-12');
            }
        }
    }, 100);
});
</script>
</body>
<!--FabCMS:tracker-->
<!--FabCMS:scripts-->
<!--FabCMS:debug-->
</html>
PAGE;
        return $page;
    }
}