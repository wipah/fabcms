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
          
          <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="myNavigation" aria-expanded="false" aria-label="Toggle navigation">
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
			               
            <form method="post" action="' . $URI->getBaseUri() . 'search/simple/" class="form-inline">
                <input id="search" name="search" class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn FabCMS-navBar-Search my-2 my-sm-0" type="submit">Search</button>
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

            $db->setQuery($query);

            if (!$result = $db->executeQuery('select')){
                echo '<pre>' . $query . '</pre>';
                return;
            }

            if (!$db->numRows)
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
<html lang="<!--FabCMS:lang-->">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />    
       <style>
        .nav-link{ color: white !important;}
        
        :root {
            --fabCMS-primary: #182978;
            --fabCMS-secondary: #6688CC;
            --fabCMS-tertiary: #ACBFE6;
            --fabCMS-quaternary: #E9BD43;
        }
        
        .fabCMS-headerMenu{
            background-color: var(--fabCMS-secondary) !important;
 
        }
        
        .fabCms-navBar{
            
        }
        
        header {
            background-color: var(--fabCMS-secondary);
            padding: 8px;
            border-bottom: 4px solid var(--fabCMS-quaternary) !important;
        }

        .fabCMS-Main {
            margin: 0 auto;
            max-width: 1400px;
            position: relative;
            background-color: white;
            padding: 14px;
            margin-top: 70px;
        }

        footer {
            background-color: var(--fabCMS-primary);
            color: white;
            padding: 8px;
        }
        
        .fabCmsLogin a{
            color: white;
            vertical-align: middle;
        }       
        
        body {
            font-family: 'Roboto Slab', serif;
            text-align: justify;
            font-display: swap;
        }
        
        @font-face {
            font-display: swap;            
        }

        body h1 {
            font-size: xx-large;
            color: var(--fabCMS-primary);
            border-bottom: 1px solid var(--fabCMS-quaternary);
            margin-bottom: 1em;
        }
        
        body h2 {
            font-size: x-large;
            color: var(--fabCMS-primary);
            border-bottom: 1px solid var(--fabCMS-secondary);
       
        }
        
        body h3 {
            font-size: larger;
            border-bottom: 1px solid var(--fabCMS-primary);
            color: var(--fabCMS-primary);
            width: fit-content;
        }

        .fabCMS-Wiki-PageShowAuthor{
            border-bottom: 1px solid var(--fabCMS-primary);
            color: var(--fabCMS-primary);
        }


        .fabcms-Wiki-CommentContainer {
            border:1px solid var(--fabCMS-secondary);
            padding: 8px;    
        }
        
        .fabCMS-Wiki-wikiAuthorBoxName{
            color: white;
            font-size: x-large;
        }
        
        .fabCMS-Wiki-CommentDescription{
            border-bottom: 3px solid var(--fabCMS-quaternary);
            background-color: var(--fabCMS-secondary);
            padding: 8px;
            color: white;
        }

        .fabCMS-Wiki-SimilarPageDescription{
            border-bottom: 3px solid var(--fabCMS-quaternary);
            background-color: var(--fabCMS-secondary);
            padding: 8px;
            color: white;
        }
        
        .fabCms-Wiki-CopyrightNotice {
            border-bottom: 1px solid var(--fabCMS-secondary);
            border-top: 1px solid var(--fabCMS-secondary);
            text-align: right;
        }
        
        .fabCMS-Wiki-SimilarPage{
            border:1px solid var(--fabCMS-secondary);
            padding: 8px;            
        }
        
        .fabCMS-Wiki-PageAuthorBox {
            border: 1px solid var(--fabCMS-secondary);
            background-color: var(--fabCMS-tertiary);
            padding: 4px;
            position: absolute;
            width: inherit;
            display: none;
            padding: 8px;
        }
        .fabCMS-Wiki-PageInfoBox{
            padding: 4px;
            z-index: 1500;
        }
        
        .FabCms-Wiki-item {
            position:relative;
            padding-top:20px;
        }

        .FabCms-Wiki-notify-badge{
            position: absolute;
            background: #444;
            text-align: center;
            box-shadow: -1px 1px 0px 9px white;
            color:white;
            padding:8px 15px;
            font-size:18px;
        }
        
        .FabCms-Wiki-articleSmallText{
            margin-top: 12px;
            vertical-align: inherit;
        }
        
        .FabCms-Wiki-articleSmallText:before {
            content: "";
            display: inline-block;
            vertical-align: middle;
            width: 18px;
            height: 18px;
            margin-right: 8px;
            background: #444;
        }
        
        .shoppingCartIcon {
            color: var(--fabCMS-quaternary);
        }
        .shoppingCartLink {
            color: white;
        }
        
        .headerLogo{
            max-width: 85px !important;   
        }
        
        @media only screen and (max-width: 768px) {
            .headerLogo{
                object-fit: scale-down;
                max-width: 85px !important;
            }

            h1 {
                font-size: 1.7rem;
            }

            h2 {
                font-size: 1.5rem;
            }
        
            h3 {
                font-size: 1.4rem;
            }
        
            h4 {
                font-size: 1.2rem;
            }
        }

    /*
     * Media
     */
    . fabCMS-Media-ShowImageFromWiki {
        background-color  : var(--fabCMS-primary);
        border  : 1x solid  var(--fabCMS-tertiary);
        padding : 4px;
    }
    
    .FabCMS-navBar-Search {
        border: 1px solid var(--fabCMS-quaternary);
        background-color: var(--fabCMS-quaternary);
        color: white;
    }
</style>

    <!--FabCMS:head-->
</head>

<body>

    <header>
        <!--FabCMS:menu-->
    </header>

    <div class="fabCMS-Main">
        <!--FabCMS:navBar-->
        
        <!--spot:beforeContent-->
        <!--FabCMS-hook:beforeContent-->
        <!--FabCMS:mainContent-->
        <!--spot:afterContent-->
        <!--FabCMS-hook:afterContent-->
  
    </div>

    <footer class="mt-2">
        <!--spot:beforeFooter-->
        <!--FabCMS-hook:beforeFooter-->
        <!--spot:afterFooter-->
        <!--FabCMS-hook:afterFooter-->
    </footer>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    

<link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;800&display=swap" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<script defer="defer" async="" src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF" crossorigin="anonymous"></script>
<script defer="defer" src="https://kit.fontawesome.com/e3fdf687e4.js" crossorigin="anonymous"></script>

<script>
/*! jQuery & Zepto Lazy v1.7.9 - http://jquery.eisbehr.de/lazy - MIT&GPL-2.0 license - Copyright 2012-2018 Daniel 'Eisbehr' Kern */
!function(t,e){"use strict";function r(r,a,i,u,l){function f(){L=t.devicePixelRatio>1,i=c(i),a.delay>=0&&setTimeout(function(){s(!0)},a.delay),(a.delay<0||a.combined)&&(u.e=v(a.throttle,function(t){"resize"===t.type&&(w=B=-1),s(t.all)}),u.a=function(t){t=c(t),i.push.apply(i,t)},u.g=function(){return i=n(i).filter(function(){return!n(this).data(a.loadedName)})},u.f=function(t){for(var e=0;e<t.length;e++){var r=i.filter(function(){return this===t[e]});r.length&&s(!1,r)}},s(),n(a.appendScroll).on("scroll."+l+" resize."+l,u.e))}function c(t){var i=a.defaultImage,o=a.placeholder,u=a.imageBase,l=a.srcsetAttribute,f=a.loaderAttribute,c=a._f||{};t=n(t).filter(function(){var t=n(this),r=m(this);return!t.data(a.handledName)&&(t.attr(a.attribute)||t.attr(l)||t.attr(f)||c[r]!==e)}).data("plugin_"+a.name,r);for(var s=0,d=t.length;s<d;s++){var A=n(t[s]),g=m(t[s]),h=A.attr(a.imageBaseAttribute)||u;g===N&&h&&A.attr(l)&&A.attr(l,b(A.attr(l),h)),c[g]===e||A.attr(f)||A.attr(f,c[g]),g===N&&i&&!A.attr(E)?A.attr(E,i):g===N||!o||A.css(O)&&"none"!==A.css(O)||A.css(O,"url('"+o+"')")}return t}function s(t,e){if(!i.length)return void(a.autoDestroy&&r.destroy());for(var o=e||i,u=!1,l=a.imageBase||"",f=a.srcsetAttribute,c=a.handledName,s=0;s<o.length;s++)if(t||e||A(o[s])){var g=n(o[s]),h=m(o[s]),b=g.attr(a.attribute),v=g.attr(a.imageBaseAttribute)||l,p=g.attr(a.loaderAttribute);g.data(c)||a.visibleOnly&&!g.is(":visible")||!((b||g.attr(f))&&(h===N&&(v+b!==g.attr(E)||g.attr(f)!==g.attr(F))||h!==N&&v+b!==g.css(O))||p)||(u=!0,g.data(c,!0),d(g,h,v,p))}u&&(i=n(i).filter(function(){return!n(this).data(c)}))}function d(t,e,r,i){++z;var o=function(){y("onError",t),p(),o=n.noop};y("beforeLoad",t);var u=a.attribute,l=a.srcsetAttribute,f=a.sizesAttribute,c=a.retinaAttribute,s=a.removeAttribute,d=a.loadedName,A=t.attr(c);if(i){var g=function(){s&&t.removeAttr(a.loaderAttribute),t.data(d,!0),y(T,t),setTimeout(p,1),g=n.noop};t.off(I).one(I,o).one(D,g),y(i,t,function(e){e?(t.off(D),g()):(t.off(I),o())})||t.trigger(I)}else{var h=n(new Image);h.one(I,o).one(D,function(){t.hide(),e===N?t.attr(C,h.attr(C)).attr(F,h.attr(F)).attr(E,h.attr(E)):t.css(O,"url('"+h.attr(E)+"')"),t[a.effect](a.effectTime),s&&(t.removeAttr(u+" "+l+" "+c+" "+a.imageBaseAttribute),f!==C&&t.removeAttr(f)),t.data(d,!0),y(T,t),h.remove(),p()});var m=(L&&A?A:t.attr(u))||"";h.attr(C,t.attr(f)).attr(F,t.attr(l)).attr(E,m?r+m:null),h.complete&&h.trigger(D)}}function A(t){var e=t.getBoundingClientRect(),r=a.scrollDirection,n=a.threshold,i=h()+n>e.top&&-n<e.bottom,o=g()+n>e.left&&-n<e.right;return"vertical"===r?i:"horizontal"===r?o:i&&o}function g(){return w>=0?w:w=n(t).width()}function h(){return B>=0?B:B=n(t).height()}function m(t){return t.tagName.toLowerCase()}function b(t,e){if(e){var r=t.split(",");t="";for(var a=0,n=r.length;a<n;a++)t+=e+r[a].trim()+(a!==n-1?",":"")}return t}function v(t,e){var n,i=0;return function(o,u){function l(){i=+new Date,e.call(r,o)}var f=+new Date-i;n&&clearTimeout(n),f>t||!a.enableThrottle||u?l():n=setTimeout(l,t-f)}}function p(){--z,i.length||z||y("onFinishedAll")}function y(t,e,n){return!!(t=a[t])&&(t.apply(r,[].slice.call(arguments,1)),!0)}var z=0,w=-1,B=-1,L=!1,T="afterLoad",D="load",I="error",N="img",E="src",F="srcset",C="sizes",O="background-image";"event"===a.bind||o?f():n(t).on(D+"."+l,f)}function a(a,o){var u=this,l=n.extend({},u.config,o),f={},c=l.name+"-"+ ++i;return u.config=function(t,r){return r===e?l[t]:(l[t]=r,u)},u.addItems=function(t){return f.a&&f.a("string"===n.type(t)?n(t):t),u},u.getItems=function(){return f.g?f.g():{}},u.update=function(t){return f.e&&f.e({},!t),u},u.force=function(t){return f.f&&f.f("string"===n.type(t)?n(t):t),u},u.loadAll=function(){return f.e&&f.e({all:!0},!0),u},u.destroy=function(){return n(l.appendScroll).off("."+c,f.e),n(t).off("."+c),f={},e},r(u,l,a,f,c),l.chainable?a:u}var n=t.jQuery||t.Zepto,i=0,o=!1;n.fn.Lazy=n.fn.lazy=function(t){return new a(this,t)},n.Lazy=n.lazy=function(t,r,i){if(n.isFunction(r)&&(i=r,r=[]),n.isFunction(i)){t=n.isArray(t)?t:[t],r=n.isArray(r)?r:[r];for(var o=a.prototype.config,u=o._f||(o._f={}),l=0,f=t.length;l<f;l++)(o[t[l]]===e||n.isFunction(o[t[l]]))&&(o[t[l]]=i);for(var c=0,s=r.length;c<s;c++)u[r[c]]=t[0]}},a.prototype.config={name:"lazy",chainable:!0,autoDestroy:!0,bind:"load",threshold:500,visibleOnly:!1,appendScroll:t,scrollDirection:"both",imageBase:null,defaultImage:"data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==",placeholder:null,delay:-1,combined:!1,attribute:"data-src",srcsetAttribute:"data-srcset",sizesAttribute:"data-sizes",retinaAttribute:"data-retina",loaderAttribute:"data-loader",imageBaseAttribute:"data-imagebase",removeAttribute:!0,handledName:"handled",loadedName:"loaded",effect:"show",effectTime:0,enableThrottle:!0,throttle:250,beforeLoad:e,afterLoad:e,onError:e,onFinishedAll:e},n(t).on("load",function(){o=!0})}(window);

/*! jQuery & Zepto Lazy - All Plugins v1.7.9 - http://jquery.eisbehr.de/lazy - MIT&GPL-2.0 license - Copyright 2012-2018 Daniel 'Eisbehr' Kern */
!function(t){function a(a,e,r,o){o=o?o.toUpperCase():"GET";var i;"POST"!==o&&"PUT"!==o||!a.config("ajaxCreateData")||(i=a.config("ajaxCreateData").apply(a,[e])),t.ajax({url:e.attr("data-src"),type:"POST"===o||"PUT"===o?o:"GET",data:i,dataType:e.attr("data-type")||"html",success:function(t){e.html(t),r(!0),a.config("removeAttribute")&&e.removeAttr("data-src data-method data-type")},error:function(){r(!1)}})}t.lazy("ajax",function(t,e){a(this,t,e,t.attr("data-method"))}),t.lazy("get",function(t,e){a(this,t,e,"GET")}),t.lazy("post",function(t,e){a(this,t,e,"POST")}),t.lazy("put",function(t,e){a(this,t,e,"PUT")})}(window.jQuery||window.Zepto),function(t){t.lazy(["av","audio","video"],["audio","video"],function(a,e){var r=a[0].tagName.toLowerCase();if("audio"===r||"video"===r){var o=a.find("data-src"),i=a.find("data-track"),n=0,c=function(){++n===o.length&&e(!1)},s=function(){var a=t(this),e=a[0].tagName.toLowerCase(),r=a.prop("attributes"),o=t("data-src"===e?"<source>":"<track>");"data-src"===e&&o.one("error",c),t.each(r,function(t,a){o.attr(a.name,a.value)}),a.replaceWith(o)};a.one("loadedmetadata",function(){e(!0)}).off("load error").attr("poster",a.attr("data-poster")),o.length?o.each(s):a.attr("data-src")?(t.each(a.attr("data-src").split(","),function(e,r){var o=r.split("|");a.append(t("<source>").one("error",c).attr({src:o[0].trim(),type:o[1].trim()}))}),this.config("removeAttribute")&&a.removeAttr("data-src")):e(!1),i.length&&i.each(s)}else e(!1)})}(window.jQuery||window.Zepto),function(t){t.lazy(["frame","iframe"],"iframe",function(a,e){var r=this;if("iframe"===a[0].tagName.toLowerCase()){var o=a.attr("data-error-detect");"true"!==o&&"1"!==o?(a.attr("src",a.attr("data-src")),r.config("removeAttribute")&&a.removeAttr("data-src data-error-detect")):t.ajax({url:a.attr("data-src"),dataType:"html",crossDomain:!0,xhrFields:{withCredentials:!0},success:function(t){a.html(t).attr("src",a.attr("data-src")),r.config("removeAttribute")&&a.removeAttr("data-src data-error-detect")},error:function(){e(!1)}})}else e(!1)})}(window.jQuery||window.Zepto),function(t){t.lazy("noop",function(){}),t.lazy("noop-success",function(t,a){a(!0)}),t.lazy("noop-error",function(t,a){a(!1)})}(window.jQuery||window.Zepto),function(t){function a(a,e,i){var n=a.prop("attributes"),c=t("<"+e+">");return t.each(n,function(t,a){"srcset"!==a.name&&a.name!==o||(a.value=r(a.value,i)),c.attr(a.name,a.value)}),a.replaceWith(c),c}function e(a,e,r){var o=t("<img>").one("load",function(){r(!0)}).one("error",function(){r(!1)}).appendTo(a).attr("src",e);o.complete&&o.load()}function r(t,a){if(a){var e=t.split(",");t="";for(var r=0,o=e.length;r<o;r++)t+=a+e[r].trim()+(r!==o-1?",":"")}return t}var o="data-src";t.lazy(["pic","picture"],["picture"],function(i,n){if("picture"===i[0].tagName.toLowerCase()){var c=i.find(o),s=i.find("data-img"),d=this.config("imageBase")||"";c.length?(c.each(function(){a(t(this),"source",d)}),1===s.length?(s=a(s,"img",d),s.on("load",function(){n(!0)}).on("error",function(){n(!1)}),s.attr("src",s.attr(o)),this.config("removeAttribute")&&s.removeAttr(o)):i.attr(o)?(e(i,d+i.attr(o),n),this.config("removeAttribute")&&i.removeAttr(o)):n(!1)):i.attr("data-srcset")?(t("<source>").attr({media:i.attr("data-media"),sizes:i.attr("data-sizes"),type:i.attr("data-type"),srcset:r(i.attr("data-srcset"),d)}).appendTo(i),e(i,d+i.attr(o),n),this.config("removeAttribute")&&i.removeAttr(o+" data-srcset data-media data-sizes data-type")):n(!1)}else n(!1)})}(window.jQuery||window.Zepto),function(t){t.lazy(["js","javascript","script"],"script",function(t,a){"script"===t[0].tagName.toLowerCase()?(t.attr("src",t.attr("data-src")),this.config("removeAttribute")&&t.removeAttr("data-src")):a(!1)})}(window.jQuery||window.Zepto),function(t){t.lazy("vimeo",function(t,a){"iframe"===t[0].tagName.toLowerCase()?(t.attr("src","https://player.vimeo.com/video/"+t.attr("data-src")),this.config("removeAttribute")&&t.removeAttr("data-src")):a(!1)})}(window.jQuery||window.Zepto),function(t){t.lazy(["yt","youtube"],function(t,a){"iframe"===t[0].tagName.toLowerCase()?(t.attr("src","https://www.youtube.com/embed/"+t.attr("data-src")+"?rel=0&amp;showinfo=0"),this.config("removeAttribute")&&t.removeAttr("data-src")):a(!1)})}(window.jQuery||window.Zepto);

 $(function() {
    $('.lazy').Lazy();
 });

$('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
    if (!$(this).next().hasClass('show')) {
        $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
    }
    var subMenu = $(this).next(".dropdown-menu");
    subMenu.toggleClass('show');

    $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
        $('.dropdown-submenu .show').removeClass("show");
    });

    return false;
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