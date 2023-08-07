<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 31/01/2017
 * Time: 11:27
 */

if (!$core->adminBootCheck())
    die("Check not passed");

if ($_GET['command'] === 'save') {
    $this->noTemplateParse = true;

    if (!isset($_GET['lang'])) {
        echo 'No language was passed.';

        return;
    }

    $lang = $core->in($_GET['lang']);

    $wikiName = $core->in($_POST['wikiName'], true);
    $homePageNamespace = $core->in($_POST['homePageNamespace'], true);
    $nameSpaceSeparator = $core->in($_POST['nameSpaceSeparator'], true);
    $pageTitleFormat = $core->in($_POST['pageTitleFormat'], true);
    $customCSS = $core->in($_POST['customCSS'], false);
    $useToc = (int) $_POST['useToc'];
    $guestCanPrint = (int) $_POST['guestCanPrint'];
    $showPageLicense = (int) $_POST['showPageLicense'];

    $authorAttribution = (int)$_POST['authorAttribution'];
    $googleTagsManager = (int)$_POST['googleTagsManager'];
    $showSearchBox = (int)$_POST['showSearchBox'];

    $leftBannerMobile = $core->in($_POST['leftBannerMobile']);
    $rightBannerMobile = $core->in($_POST['rightBannerMobile']);
    $topBannerMobile = $core->in($_POST['topBannerMobile']);
    $bottomBannerMobile = $core->in($_POST['bottomBannerMobile']);
    $afterFirstPBannerMobile = $core->in($_POST['afterFirstPBannerMobile']);
    $beforeFirstH2BannerMobile = $core->in($_POST['beforeFirstH2BannerMobile']);

    $leftBannerTablet = $core->in($_POST['leftBannerTablet']);
    $rightBannerTablet = $core->in($_POST['rightBannerTablet']);
    $topBannerTablet = $core->in($_POST['topBannerTablet']);
    $bottomBannerTablet = $core->in($_POST['bottomBannerTablet']);
    $afterFirstPBannerTablet = $core->in($_POST['afterFirstPBannerTablet']);
    $beforeFirstH2BannerTablet = $core->in($_POST['beforeFirstH2BannerTablet']);

    $leftBannerPC = $core->in($_POST['leftBannerPC']);
    $rightBannerPC = $core->in($_POST['rightBannerPC']);
    $topBannerPC = $core->in($_POST['topBannerPC']);
    $bottomBannerPC = $core->in($_POST['bottomBannerPC']);
    $afterFirstPBannerPC = $core->in($_POST['afterFirstPBannerPC']);
    $beforeFirstH2BannerPC = $core->in($_POST['beforeFirstH2BannerPC']);

    $query = 'DELETE FROM ' . $db->prefix . 'wiki_config WHERE lang = \'' . $lang . '\'';
    

    if (!$db->query($query)) {
        echo 'Deleting old config error. ' . $query;

        return;
    }

    $query = 'INSERT INTO ' . $db->prefix . 'wiki_config 
    (param, lang, value)
    VALUES 
    (\'wikiName\', \'' . $lang . '\', \'' . $wikiName . '\'),
    (\'homePageNamespace\', \'' . $lang . '\', \'' . $homePageNamespace . '\'),
    (\'nameSpaceSeparator\', \'' . $lang . '\', \'' . $nameSpaceSeparator . '\'),
    (\'pageTitleFormat\', \'' . $lang . '\', \'' . $pageTitleFormat . '\'),
    (\'authorAttribution\', \'' . $lang . '\', \'' . $authorAttribution . '\'),
    (\'showSearchBox\', \'' . $lang . '\', \'' . $showSearchBox . '\'),
    (\'customCSS\', \'' . $lang . '\', \'' . $customCSS . '\'),
    (\'googleTagsManager\', \'' . $lang . '\', \'' . $googleTagsManager . '\'),
    (\'useToc\', \'' . $lang . '\', \'' . $useToc . '\'),
    (\'guestCanPrint\', \'' . $lang . '\', \'' . $guestCanPrint . '\'),
    (\'showPageLicense\', \'' . $lang . '\', \'' . $showPageLicense . '\'),
    
    (\'leftBannerMobile\', \'' . $lang . '\', \'' . $leftBannerMobile . '\'),
    (\'rightBannerMobile\', \'' . $lang . '\', \'' . $rightBannerMobile . '\'),
    (\'topBannerMobile\', \'' . $lang . '\', \'' . $topBannerMobile . '\'),
    (\'bottomBannerMobile\', \'' . $lang . '\', \'' . $bottomBannerMobile . '\'),
    (\'afterFirstPBannerMobile\', \'' . $lang . '\', \'' . $afterFirstPBannerMobile . '\'),
    (\'beforeFirstH2BannerMobile\', \'' . $lang . '\', \'' . $beforeFirstH2BannerMobile . '\'),
    
    (\'leftBannerTablet\', \'' . $lang . '\', \'' . $leftBannerTablet . '\'),
    (\'rightBannerTablet\', \'' . $lang . '\', \'' . $rightBannerTablet . '\'),
    (\'topBannerTablet\', \'' . $lang . '\', \'' . $topBannerTablet . '\'),
    (\'bottomBannerTablet\', \'' . $lang . '\', \'' . $bottomBannerTablet . '\'),
    (\'afterFirstPBannerTablet\', \'' . $lang . '\', \'' . $afterFirstPBannerTablet . '\'),
    (\'beforeFirstH2BannerTablet\', \'' . $lang . '\', \'' . $beforeFirstH2BannerTablet . '\'),
    
    (\'leftBannerPC\', \'' . $lang . '\', \'' . $leftBannerPC . '\'),
    (\'rightBannerPC\', \'' . $lang . '\', \'' . $rightBannerPC . '\'),
    (\'topBannerPC\', \'' . $lang . '\', \'' . $topBannerPC . '\'),
    (\'bottomBannerPC\', \'' . $lang . '\', \'' . $bottomBannerPC . '\'),
    (\'afterFirstPBannerPC\', \'' . $lang . '\', \'' . $afterFirstPBannerPC . '\'),
    (\'beforeFirstH2BannerPC\', \'' . $lang . '\', \'' . $beforeFirstH2BannerPC . '\');
    ';

    if (!$db->query($query)) {
        echo 'Query error. ' . $query;

        return;
    }


    echo '<div class="alert alert-success">
            <strong>Success!</strong> Configuration was saved.
        </div>';

    return;
}

if (!isset($_GET['lang'])) {
    echo '<h2>Wiki config</h2><p>Please, select a language.</p><ul>';

    foreach ($conf['langAllowed'] AS $singleLang) {
        echo '<li><a href="admin.php?module=wiki&op=config&lang=' . $singleLang . '">' . $singleLang . '</a></li>';
    }

    echo '</ul>';

    return;
}
$lang = $core->in($_GET['lang'], true);

echo '<h2>Wiki config (lang: ' . $lang . ')</h2>';

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Config', 'admin.php?module=wiki&op=config');

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_config 
          WHERE lang= \'' . $lang . '\';';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $query;

    return;
}

while ($row = mysqli_fetch_assoc($result)) {
    $wikiConfig[$row['param']] = $row['value'];
}

echo '
<div>
    <!-- Button -->
    <div class="form-group row">
      <label class="col-md-3 form-form-control-label" for="singlebutton">Operations</label>
      <div class="col-md-7" id="saveStatus"></div>
      <div class="col-md-2">
        <button onclick="saveConfig();" id="singlebutton" name="singlebutton" class="btn btn-primary float-right">Save config</button>
      </div>
    </div>
</div>

<ul class="nav nav-tabs FabCMS-Admin-Tabs">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#home">Home</a>
  </li>
  
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#pages">Pages</a>
  </li>
  
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#banner">Banner</a>
  </li>
</ul>

<div class="tab-content">
  <div id="home" class="tab-pane container active">
    <h3>Basic config</h3>
    
    <p>
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 form-form-control-label" for="textinput">Wiki name</label>  
          <div class="col-md-9">
          <input id="wikiName" name="wikiName" type="text" placeholder="" class="form-control input-md" value="' . $wikiConfig['wikiName'] . '">
          <span class="help-block">Wiki name</span>  
          </div>
        </div>   

        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 form-form-control-label" for="textinput">Homepage namespace</label>  
          <div class="col-md-9">
          <input id="homePageNamespace" name="homePageNamespace" type="text" placeholder="" class="form-control input-md" value="' . $wikiConfig['homePageNamespace'] . '">
          <span class="help-block">Defaul namespace</span>  
          </div>
        </div>   

        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 form-form-control-label" for="textinput">Namespace separator</label>  
          <div class="col-md-9">
          <input id="nameSpaceSeparator" name="nameSpaceSeparator" type="text" placeholder="" class="form-control input-md" value="' . $wikiConfig['nameSpaceSeparator'] . '">
          <span class="help-block">Namespace separator</span>  
          </div>
        </div>   
        
        <!-- Text input-->
        <div class="form-group row">
          <label class="col-md-3 form-control-label" for="textinput">Namespace separator</label>  
          <div class="col-md-9">
          <input id="pageTitleFormat" name="pageTitleFormat" type="text" placeholder="" class="form-control input-md" value="' . $wikiConfig['pageTitleFormat'] . '">
          <span class="help-block">Page title format. <br/><strong>%s</strong> Page name, <strong>%ta</strong> Title alternative, <strong>%tag1</strong> first tag, <strong>%tag2</strong> second tag</span>  
          </div>
        </div>   
        
        <div class="form-group row">
          <label class="col-md-3 form-control-label" for="useToc">Use TOC</label>  
          <div class="col-md-9">
               <select id="useToc" name="useToc" class="form-control">
                  <option ' . ((int)$wikiConfig['useToc'] === 1 ? 'selected' : '') . ' value="1">Yes, use TOC</option>
                  <option ' . ((int)$wikiConfig['useToc'] === 0 ? 'selected' : '') . ' value="0">Don\'t use TOC</option>
                </select> 
          </div>
        </div>   
        
        <div class="form-group row">
          <label class="col-md-3 form-control-label" for="guestCanPrint">Guest can print</label>  
          <div class="col-md-9">
               <select id="guestCanPrint" name="guestCanPrint" class="form-control">
                  <option ' . ((int) $wikiConfig['guestCanPrint'] === 1 ? 'selected' : '') . ' value="1">Yes, guest can print pages</option>
                  <option ' . ((int) $wikiConfig['guestCanPrint'] === 0 ? 'selected' : '') . ' value="0">No, guest cannot print pages</option>
                </select> 
          </div>
        </div>
        
        <div class="form-group row">
          <label class="col-md-3 form-control-label" for="guestCanPrint">Show License</label>  
          <div class="col-md-9">
               <select id="showPageLicense" name="showPageLicense" class="form-control">
                  <option ' . ((int) $wikiConfig['showPageLicense'] === 1 ? 'selected' : '') . ' value="1">Yes, show license</option>
                  <option ' . ((int) $wikiConfig['showPageLicense'] === 0 ? 'selected' : '') . ' value="0">No, don\'t show any license</option>
                </select> 
          </div>
        </div>
           
    </p>
  </div>
  
  <div id="pages" class="tab-pane container fade">
    <h3>Page config</h3>
    <p>
        
            <div class="form-group row">
              <label class="col-md-4 form-control-label" for="selectbasic">Author Box</label>
              <div class="col-md-4">
                <select id="authorAttribution" name="authorAttribution" class="form-control">
                  <option ' . ((int)$wikiConfig['authorAttribution'] === 1 ? 'selected' : '') . ' value="1">No author</option>
                  <option ' . ((int)$wikiConfig['authorAttribution'] === 2 ? 'selected' : '') . ' value="2">Author (only text)</option>
                  <option ' . ((int)$wikiConfig['authorAttribution'] === 3 ? 'selected' : '') . ' value="3">Author (with photo)</option>
                </select>
            </div>
         
        </div>
        
        
            <div class="form-group row">
              <label class="col-md-4 form-control-label" for="googleTagsManager">Google tag manager</label>
              <div class="col-md-4">
                <select id="googleTagsManager" name="googleTagsManager" class="form-control">
                  <option ' . ((int)$wikiConfig['googleTagsManager'] === 0 ? 'selected' : '') . ' value="0">No</option>
                  <option ' . ((int)$wikiConfig['googleTagsManager'] === 1 ? 'selected' : '') . ' value="1">Yes</option>
                </select>
            </div>
            
        </div>
        
        
            <div class="form-group row">
              <label class="col-md-4 form-control-label" for="showSearchBox">Show search box</label>
              <div class="col-md-4">
                <select id="showSearchBox" name="showSearchBox" class="form-control">
                  <option ' . ((int)$wikiConfig['showSearchBox'] === 0 ? 'selected' : '') . ' value="0">No</option>
                  <option ' . ((int)$wikiConfig['showSearchBox'] === 1 ? 'selected' : '') . ' value="1">Yes</option>
                </select>
            </div>
           
        </div>
       
            <div class="form-group row">
              <label class="col-md-4 form-control-label" for="customCSS">CustomCSS</label>
              <div class="col-md-4">
                <div style="height: 350px; top: 0; right: 0; bottom: 0; left: 0;" id="customCSS">' . $wikiConfig['customCSS'] . '</div>
            </div>
            </div>
       
        
    </p>
    
  </div>
  
  <div id="banner" class="tab-pane container fade">
    <h3>Banner</h3>
    
        <ul class="nav nav-tabs FabCMS-Admin-Tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#pc">PC</a>
            </li>
      
           <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tablet">Tablet</a>
            </li>
          
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#mobile">Mobile</a>
            </li>
        </ul>
    
    <div class="tab-content">
      <div id="pc" class="tab-pane container active">
              <h4>PC (default)</h4>
        

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Left banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="leftBannerPC" name="leftBannerPC">' . $wikiConfig['leftBannerPC'] . '</textarea>
                </div>
            </div>
 
            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Right banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="rightBannerPC" name="rightBannerPC">' . $wikiConfig['rightBannerPC'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">After first &lt;p&gt;</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="afterFirstPBannerPC" name="afterFirstPBannerPC">' . $wikiConfig['afterFirstPBannerPC'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Before first H2</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="beforeFirstH2BannerPC" name="beforeFirstH2BannerPC">' . $wikiConfig['beforeFirstH2BannerPC'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Top banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="topBannerPC" name="topBannerPC">' . $wikiConfig['topBannerPC'] . '</textarea>
                </div>
            </div>


            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Bottom banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="bottomBannerPC" name="bottomBannerPC">' . $wikiConfig['bottomBannerPC'] . '</textarea>
                </div>
            </div>
      </div>
      
      <div id="tablet" class="tab-pane container">
        <h4>Tablet</h4>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Left banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="leftBannerTablet" name="leftBannerTablet">' . $wikiConfig['leftBannerTablet'] . '</textarea>
                </div>
            </div>

        

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Right banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="rightBannerTablet" name="rightBannerTablet">' . $wikiConfig['rightBannerTablet'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">After first &lt;p&gt;</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="afterFirstPBannerTablet" name="afterFirstPBannerTablet">' . $wikiConfig['afterFirstPBannerTablet'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Top banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="topBannerTablet" name="topBannerTablet">' . $wikiConfig['topBannerTablet'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Before first H2 banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="beforeFirstH2BannerTablet" name="beforeFirstH2BannerTablet">' . $wikiConfig['beforeFirstH2BannerTablet'] . '</textarea>
                </div>
            </div>
        
            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Bottom banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="bottomBannerTablet" name="bottomBannerTablet">' . $wikiConfig['bottomBannerTablet'] . '</textarea>
                </div>
            </div>

      </div>
      
      <div id="mobile" class="tab-pane container">
         <h4>Mobile</h4>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Left banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="leftBannerMobile" name="leftBannerMobile">' . $wikiConfig['leftBannerMobile'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Right banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="rightBannerMobile" name="rightBannerMobile">' . $wikiConfig['rightBannerMobile'] . '</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">After first &lt;p&gt;</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="afterFirstPBannerMobile" name="afterFirstPBannerMobile">' . $wikiConfig['afterFirstPBannerMobile'] . '</textarea>
                </div>
            </div>              

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Top banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="topBannerMobile" name="topBannerMobile">' . $wikiConfig['topBannerMobile'] . '</textarea>
                </div>
            </div>
 
                        
            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Before first H2 banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="beforeFirstH2BannerMobile" name="beforeFirstH2BannerMobile">' . $wikiConfig['beforeFirstH2BannerMobile'] . '</textarea>
                </div>
            </div>
 

            <div class="form-group row">
                <label class="col-md-3 form-control-label" for="textarea">Bottom banner</label>
                <div class="col-md-8">                     
                    <textarea class="form-control" id="bottomBannerMobile" name="bottomBannerMobile">' . $wikiConfig['bottomBannerMobile'] . '</textarea>
                </div>
            </div>
            
      </div>
     </div> 
  </div>
   
        
  </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/ace.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.9/mode-css.js"></script>
<script type="text/javascript">

var editor = ace.edit("customCSS");

editor.getSession().setMode("ace/mode/css");
    
function saveConfig(){
   
   wikiName = $("#wikiName").val();
   homePageNamespace    =   $("#homePageNamespace").val();
   nameSpaceSeparator   =   $("#nameSpaceSeparator").val();
   pageTitleFormat      =   $("#pageTitleFormat").val();
   customCSS            =   editor.getValue();
   useToc               =   $("#useToc").val();
   guestCanPrint        =   $("#guestCanPrint").val();
   showPageLicense      =   $("#showPageLicense").val();
   
   authorAttribution    =   $("#authorAttribution").val();
   showSearchBox        =   $("#showSearchBox").val();
   googleTagsManager    =   $("#googleTagsManager").val();
   
   leftBannerMobile     =   $("#leftBannerMobile").val();
   rightBannerMobile    =   $("#rightBannerMobile").val();
   topBannerMobile      =   $("#topBannerMobile").val();
   bottomBannerMobile   =   $("#bottomBannerMobile").val();
   afterFirstPBannerMobile = $("#afterFirstPBannerMobile").val();
   beforeFirstH2BannerMobile = $("#beforeFirstH2BannerMobile").val();
   
   leftBannerTablet     = $("#leftBannerTablet").val();
   rightBannerTablet    = $("#rightBannerTablet").val();
   topBannerTablet      = $("#topBannerTablet").val();
   bottomBannerTablet = $("#bottomBannerTablet").val();
   afterFirstPBannerTablet = $("#afterFirstPBannerTablet").val();
   beforeFirstH2BannerTablet = $("#beforeFirstH2BannerTablet").val();
   
   leftBannerPC         = $("#leftBannerPC").val();
   rightBannerPC        = $("#rightBannerPC").val();
   topBannerPC          = $("#topBannerPC").val();
   bottomBannerPC       = $("#bottomBannerPC").val();
   afterFirstPBannerPC  = $("#afterFirstPBannerPC").val();
   beforeFirstH2BannerPC = $("#beforeFirstH2BannerPC").val();
 
   $.post( "admin.php?module=wiki&op=config&lang=' . $lang . '&command=save", { 
                                                                                wikiName: wikiName, 
                                                                                homePageNamespace: homePageNamespace, 
                                                                                nameSpaceSeparator: nameSpaceSeparator, 
                                                                                pageTitleFormat: pageTitleFormat, 
                                                                                customCSS: customCSS, 
                                                                                useToc: useToc, 
                                                                                guestCanPrint: guestCanPrint, 
                                                                                showPageLicense: showPageLicense, 
                                                                                authorAttribution: authorAttribution, 
                                                                                showSearchBox: showSearchBox, 
                                                                                googleTagsManager: googleTagsManager, 
                                                                                leftBannerMobile: leftBannerMobile, 
                                                                                rightBannerMobile: rightBannerMobile, 
                                                                                topBannerMobile: topBannerMobile, 
                                                                                bottomBannerMobile: bottomBannerMobile, 
                                                                                afterFirstPBannerMobile: afterFirstPBannerMobile,
                                                                                beforeFirstH2BannerMobile: beforeFirstH2BannerMobile,
                                                                                leftBannerTablet: leftBannerTablet, 
                                                                                rightBannerTablet: rightBannerTablet, 
                                                                                topBannerTablet: topBannerTablet, 
                                                                                bottomBannerTablet: bottomBannerTablet, 
                                                                                afterFirstPBannerTablet: afterFirstPBannerTablet,
                                                                                beforeFirstH2BannerTablet: beforeFirstH2BannerTablet,
                                                                                leftBannerPC: leftBannerPC, 
                                                                                rightBannerPC: rightBannerPC,                                                                                  
                                                                                afterFirstPBannerPC: afterFirstPBannerPC,
                                                                                beforeFirstH2BannerPC: beforeFirstH2BannerPC,
                                                                                topBannerPC: topBannerPC,
                                                                                bottomBannerPC: bottomBannerPC,
                                                                               })
    .done(function( data ) {
        $( "#saveStatus").html(data);
    });        
}
</script>';