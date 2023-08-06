<?php

if (!$core->loaded)
    die ("Not loaded");

if (!$user->isAdmin)
    die ("Only admin");

$template->navBarAddItem('Formazione', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione'));
$template->navBarAddItem('Admin', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/');
$template->navBarAddItem('Video', $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/admin-cp/video/');
$template->navBarAddItem('Editor');

if (isset($_GET['ID'])) {
    $ID = (int) $_GET['ID'];

    if (isset($_GET['save'])) {

        if(!isset($_POST['dummy'])) {
            echo 'Reload detected.';
            return;
        }

        $query = '
            UPDATE ' . $db->prefix . 'formazione_media
            SET youtube_ID  = \'' . $core->in($_POST['youtube_ID']) . '\',
                full_ID     = \'' . $core->in($_POST['full_ID'])    . '\',
                name        = \'' . $core->in($_POST['name'])       . '\',
                trackback        = \'' . $core->getTrackback($_POST['name'])       . '\',
                access_level = \'' . ( (int) $_POST['accessLevel'] ) . '\',
                type = \'' . ( (int) $_POST['type'] ) . '\',
                subtype = \'' . ( (int) $_POST['subtype'] ) . '\',
                description_short     = \'' . $core->in($_POST['description_short']) . '\',
                description     = \'' . $core->in($_POST['description']) . '\',
                description_seo     = \'' . $core->in($_POST['seoDescription']) . '\',
                keywords     = \'' . $core->in($_POST['keywords']) . '\'
            WHERE ID = ' . $ID . '
            LIMIT 1';

        $db->setQuery($query);

        if (!$db->executeQuery('update')) {
            echo '<pre>' . $query . '</pre>';
            return;
        }

        echo '<div style="border: 1px solid green; padding: 4px;">Media updated</div>';
    }

    $action = $URI->getBaseUri() . $this->routed . '/admin-cp/video/editor/?ID=' . $ID . '&save';

    $query = 'SELECT * 
              FROM ' . $db->prefix . 'formazione_media
              WHERE ID = ' . $ID . ' 
              LIMIT 1';

    $db->setQuery($query);

    if (!$result = $db->executeQuery('select')) {
        echo '<pre>' . $query . '</pre>';
        return;
    }

    if (!$db->affected_rows){
        echo 'No row';
        return;
    }

    $row = mysqli_fetch_assoc($result);
} else {
    $action = $URI->getBaseUri() . $this->routed . '/admin-cp/video/editor/?new&save';

    if (isset($_GET['save'])) {
        if(!isset($_POST['dummy'])) {
            echo 'Reload detected on new.';
            // return;
        }

        $query = '
            INSERT INTO ' . $db->prefix . 'formazione_media
            (
                youtube_ID,
                full_ID,
                name,
                name_trackback,
                access_level,
                type,
                subtype,
                description_short,
                description,
                description_seo,
                keywords
            ) VALUES
            (
                \'' . $core->in($_POST['youtube_ID']) . '\',
                \'' . $core->in($_POST['full_ID'])    . '\',
                \'' . $core->in($_POST['name'])       . '\',
                \'' . $core->getTrackback($_POST['name']) . '\',
                \'' . ( (int) $_POST['accessLevel'] ) . '\',
                \'' . ( (int) $_POST['type'] ) . '\',
                \'' . ( (int) $_POST['subtype'] ) . '\',
                \'' . $core->in($_POST['description_short']) . '\',
                \'' . $core->in($_POST['description']) . '\',
                \'' . $core->in($_POST['seoDescription']) . '\',
                \'' . $core->in($_POST['keywords']) . '\'
            )';

        $db->setQuery($query);

        if (!$db->executeQuery('insert')) {
            echo '<pre>' . $query . '</pre>';
            return;
        }

        echo '<div style="border: 1px solid green; padding: 4px;">
                Media created. 
                <a href="' . $URI->getBaseUri() .
                             $this->routed .
                             '/admin-cp/video/editor/?ID=' .
                             $db->lastInsertID .'">Click here to update
                </a>
              </div>';
        return;
    }
}

echo '
<div>
  <form action="' . $action . '" method="post">

      <input type="hidden" id="dummy">
    
      <div class="form-group row">
        <label for="ID" class="col-4 col-form-label">ID</label> 
        <div class="col-8">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <i class="fa fa-500px"></i>
              </div>
            </div> 
            <input id="ID" name="ID" type="text" class="form-control" value="' . $row['ID']  . '" disabled>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label for="youtube_ID" class="col-4 col-form-label">YouTube ID</label> 
        <div class="col-8">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <i class="fa fa-youtube"></i>
              </div>
            </div> 
            <input id="youtube_ID" name="youtube_ID" placeholder="YouTube ID" type="text" class="form-control" value="' . $row['youtube_ID']  . '">
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label for="full_ID" class="col-4 col-form-label">Full ID</label> 
        <div class="col-8">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <i class="fa fa-battery-full"></i>
              </div>
            </div> 
            <input id="full_ID" name="full_ID" placeholder="Full ID" type="text" class="form-control" value="' . $row['full_ID']  . '">
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label for="name" class="col-4 col-form-label">Name</label> 
        <div class="col-8">
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <i class="fa fa-folder-o"></i>
              </div>
            </div> 
            <input id="name" name="name" type="text" class="form-control" value="' . $row['name']  . '">
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label for="accessLevel" class="col-4 col-form-label">Access Level</label> 
        <div class="col-8">
          <select id="accessLevel" name="accessLevel" class="custom-select">
            <option ' . ( (int) $row['access_level'] === 0 ? 'selected' : '' ) . ' value="0">0</option>
            <option ' . ( (int) $row['access_level'] === 1 ? 'selected' : '' ) . ' value="1">1</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label for="type" class="col-4 col-form-label">Type</label> 
        <div class="col-8">
          <select id="type" name="type" class="custom-select">
            <option ' . ( (int) $row['type'] === 0 ? 'selected' : '' ) . ' value="0">0</option>
            <option ' . ( (int) $row['type'] === 1 ? 'selected' : '' ) . ' value="1">1</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label for="subtype" class="col-4 col-form-label">SubType</label> 
        <div class="col-8">
          <select id="subtype" name="subtype" class="custom-select">
            <option ' . ( (int) $row['subtype'] === 0 ? 'selected' : '' ) . ' value="0">0</option>
            <option ' . ( (int) $row['subtype'] === 1 ? 'selected' : '' ) . ' value="1">1</option>
          </select>
        </div>
      </div>
      
        <div class="form-group row">
        <label for="description" class="col-4 col-form-label">Description short</label> 
        <div class="col-8">
          <textarea id="description" name="description_short" cols="40" rows="5" class="form-control" aria-describedby="descriptionHelpBlock">' . $row['description_short'] .'</textarea> 
          <span id="descriptionHelpBlock" class="form-text text-muted">Short description</span>
        </div>
      </div>
      
      <div class="form-group row">
        <label for="description" class="col-4 col-form-label">Description</label> 
        <div class="col-8">
          <textarea id="description" name="description" cols="40" rows="5" class="form-control" aria-describedby="descriptionHelpBlock">' . $row['description'] .'</textarea> 
          <span id="descriptionHelpBlock" class="form-text text-muted">Description of the video</span>
        </div>
      </div>
      <div class="form-group row">
        <label for="seoDescription" class="col-4 col-form-label">SEO description</label> 
        <div class="col-8">
          <textarea id="seoDescription" name="seoDescription" cols="40" rows="5" class="form-control">' . $row['description_seo'] . '</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label for="keywords" class="col-4 col-form-label">Keywords</label> 
        <div class="col-8">
          <input id="keywords" name="keywords" placeholder="Keywords1, keywords 2" type="text" class="form-control" aria-describedby="keywordsHelpBlock" value="' . $row['keywords'] . '"> 
          <span id="keywordsHelpBlock" class="form-text text-muted">Comma separated</span>
        </div>
      </div> 
      <div class="form-group row">
        <div class="offset-4 col-8">
          <button name="submit" type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
  </form>
</div>
';