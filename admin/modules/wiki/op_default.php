<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 25/11/2016
 * Time: 12:44
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');

$template->sidebar .= $template->simpleBlock('Quick op.', '
                                                 &bull;<a href="admin.php?module=wiki">Wiki</a> <br/>
                                                 &bull;<a href="admin.php?module=wiki&op=editor">Editor</a>  <br/>
                                                 &bull;<a href="admin.php?module=wiki&op=categories">Categories</a> <br/>
                                                 &bull;<a href="admin.php?module=wiki&op=config">Config</a> <br/>
                                               ');

$query = 'SELECT COUNT(PAGES.ID) AS total, SUM(STATS.words) total_words 
          FROM ' . $db->prefix . 'wiki_pages PAGES 
          LEFT JOIN ' . $db->prefix . 'wiki_pages_statistics STATS
          ON STATS.page_ID = PAGES.ID
          WHERE PAGES.service_page != 1
            AND PAGES.visible = 1
            AND PAGES.internal_redirect = \'\'';

$db->setQuery($query);
if (!$resultCount = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
}
$rowCount = mysqli_fetch_assoc($resultCount);

$totalPages = $rowCount['total'];
$totalWords = $rowCount['total_words'];
unset ($resultCount);

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages_status';
$db->setQuery($query);

if (!$resultStatus = $db->executeQuery('select')) {
    echo $query;
    die();
}

$selectStatus = '<select id="status" class="form-control"><option value="0">Any</option>';
while ($row = mysqli_fetch_assoc($resultStatus)) {
    $selectStatus .= '<option value="' . $row['ID'] . '">' . $row['status'] . '</option>';
}
$selectStatus .= '</select>';


$query = 'SELECT C.* 
          FROM ' . $db->prefix . 'wiki_categories_details AS C';

$db->setQuery($query);

if (!$resultCategories = $db->executeQuery('select')) {
    echo $query;
    die();
}

$selectCategories = '<select id="category" class="form-control">
                        <option value="0">Any</option>';
while ($row = mysqli_fetch_assoc($resultCategories)) {
    $selectCategories .= '<option value="' . $row['ID'] . '">(' . $row['lang'] . ') ' . $row['name'] . '</option>';
}
$selectCategories .= '</select>';


$query = '
SELECT COMMENTS.*, 
       PAGES.title, 
       PAGES.ID page_ID,
       USERS.username,
       USERS.ID user_ID
FROM ' . $db->prefix . 'wiki_comments COMMENTS
LEFT JOIN ' . $db->prefix . 'wiki_pages PAGES
    ON COMMENTS.page_ID = PAGES.ID
LEFT JOIN ' . $db->prefix . 'users USERS
    ON USERS.ID = COMMENTS.author_ID    
ORDER BY ID DESC';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    $comments = 'Query error.';
} else {
    if (!$db->numRows) {
        $comments = 'No comments are present.';
    } else {
        $comments = '
        <div class="table-responsive">
            <table id="FabCMS-Wiki-Comments" class="table table-bordered table-hover table-striped table-sm">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>User ID</th>
                    <th>Author</th>
                    <th>Page</th>
                    <th>IP</th>
                    <th>Comment</th>
                    <th>Visible</th>
                    <th>Operation</th>
                  </tr>
                </thead>
                <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $comments .= '<tr id="comment-' . $row['ID'] . '">
                <td>' . $row['ID'] . '</td>
                <td>' . $core->getDateTime($row['date']) . '</td>
                <td>' . $row['user_ID'] . '</td>
                <td id="comment-' . $row['ID'] . '-author">' . $row['author'] . '(username: <a target="_blanj" href="admin.php?module=user&op=edit&ID=' . $row['user_ID'] .'">' . $row['username'] .' )</a></td>
                <td><a href="admin.php?module=wiki&op=editor&ID=' . $row['page_ID'] .  '" target="_blank">' . $row['title'] . '</a></td>
                <td>' . $row['IP'] . '</td>
                <td id="comment-' . $row['ID'] . '-comment">' . $row['comment'] . '</td>
                <td id="comment-' . $row['ID'] . '-visible">' . $row['visible'] . '</td>
                <td>
                    <span onclick="editComment(\'' . $row['ID'] . '\');">Edit</span> | <span onclick="deleteComment(' . $row['ID'] . ');">Delete</span>
                </td>

            </tr>';
        }

        $comments .= '</tbody></table></div>';
    }
}


// Yesterday and today stats
$query = 'SELECT
(
    SELECT COUNT(STATS.ID) AS yesterday 
    FROM ' . $db->prefix . 'stats STATS
    WHERE STATS.module 	= \'wiki\'
    AND STATS.subModule = \'pageView\'
    AND STATS.is_bot 	!= 1
    AND STATS.date BETWEEN SUBDATE(CURDATE(),1) AND CURDATE() 
) AS yesterday,
(
    SELECT COUNT(STATS.ID) AS today 
    FROM ' . $db->prefix . 'stats STATS
    WHERE STATS.module 	= \'wiki\'
    AND STATS.subModule = \'pageView\'
    AND STATS.is_bot 	!= 1
    AND STATS.date >= SUBDATE(CURDATE(),0) 
) AS today';


$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    die();
}

$row = mysqli_fetch_assoc($result);
$yesterdayHits = $row['yesterday'];
$todayyHits = $row['today'];

$main = '
<div class="row mb-5">
    <div class="col-md-4">
        <div class="card bg-info">
          <div class="card-header">Stats</div>
          <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-6">
                    <p style="font-size: 3em; color: #384937; text-align: center">' . ((int) $totalPages) . '<br/><span style="font-size: 14px !important; text-align: center">pages</span> </p>        
                </div>
                <div class="col-md-6">
                    <p style="font-size: 3em; color: #384937; text-align: center">' . ((int) $totalWords) . '<br/>
                    <span style="font-size: 14px !important; text-align: center">words</span>
                        
                </div>
            </div>
            </p> 
          </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info">
          <div class="card-header">Today hits</div>
          <div class="card-body bg-light"><p style="font-size: 4em; color: #384937; text-align: center">' . ((int) $todayyHits) . '</p></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info">
          <div class="card-header">Yesterday hits</div>
          <div class="card-body bg-light">
            <p style="font-size: 4em; color: #384937; text-align: center">' . ((int) $yesterdayHits) . '</p>
          </div>
        </div>    
    </div>
</div>

<div class="FabCMS-filterBox">
    <div class="row">
        <div class="col-md-2">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Title</span>
                    <input type="text" class="form-control" id="title">
                </div>
        </div>
        
        <div class="col-md-2">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Tag</span>
                    <input type="text" class="form-control" id="tag">
                </div>
        </div>
        
        <div class="col-md-2">        
                <div class="form-group">
                    <span class="FabCMS-filterValue">Language</span>
                    <input type="text" class="form-control" id="language">
                </div>
        </div>
        
        <div class="col-md-2">        
                <div class="form-group">
                    <span class="FabCMS-filterValue">Status</span>
                    ' . $selectStatus . '
                </div>
        </div>
        
        <div class="col-md-2">        
                <div class="form-group">
                    <span class="FabCMS-filterValue">Category</span>
                    ' . $selectCategories . '
                </div>
        </div>
        
        <div class="col-md-2">        
                <div class="form-group">
                    <span class="FabCMS-filterValue">Visibility</span>
                    <select id="visible">
                        <option value="-1" selected>Both</option>
                        <option value="1">Visible</option>
                        <option value="0">Hidden</option>
                    </select>
                </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Created from</span>
                    <input type="text" class="form-control datepicker" id="createdFrom">
                </div>
        </div>
        <div class="col-md-6">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Created to</span>
                    <input type="text" class="form-control datepicker" id="createdTo">
                </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Last update from</span>
                    <input type="text" class="form-control datepicker" id="lastUpdateFrom">
                </div>
        </div>
        <div class="col-md-6">
                <div class="form-group">
                    <span class="FabCMS-filterValue">Last update to</span>
                    <input type="text" class="form-control datepicker" id="lastUpdateTo">
                </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <button class="float-right" onclick="updateLatestPage()">Filter</button>
        </div>
    </div>
</div>

<div class="FabCMS-optionBox clearfix">
	<a class="btn btn-primary float-right" href="admin.php?module=wiki&op=editor">New page</a>
	<a class="btn btn-primary float-right" href="admin.php?module=wiki&op=config">Config</a>
</div>

<hr />

<div class="row">
    <div class="col-md-12">
        <div id="latestPages"></div>
    </div>
</div>';


echo '
<ul class="nav nav-tabs" id="myTab" role="tablist">
  
  <li class="nav-item active">
    <a  class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
  </li>
  
  <li class="nav-item">
    <a  class="nav-link" id="home-tab" data-toggle="tab" href="#comments" role="tab" aria-controls="comments" aria-selected="true">Comments</a>
  </li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
    <h3>FabWiki</h3>
    <p>' . $main . '</p>
    <p id="globalStats">Please wait. Stats are generated.</p>
  </div>
  <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
    <h3>Comments</h3>
    <p>' . $comments . '</p>
  </div>
</div>

<!-- Modal -->
<!-- Button trigger modal -->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        <div id="modalBody"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';

echo '
<script type="text/javascript">
    $(function() {
        
        
        $("#FabCMS-Wiki-Comments").DataTable({
         "order": [[ 0, "desc" ]]
       });
        
        $("#tableStats").html("<div class=\'spinner-border text-primary\' role=\'status\'><span class=\'sr-only\'>Loading...</span></div>");
       
        updateLatestPage();
        
        
        $("#tableStats").DataTable({ 
             "order": [[ 4, "desc" ]]
           }
        );
    });
    
    function deleteComment(ID) {
      if ( !confirm("Delete comment?") )
        return;
      
      $.post( "admin.php?module=wiki&op=deleteComment", { ID: ID })
      .done(function( data ) {
        if (data == "ok"){
            $("#comment-" + ID ).hide();
        }
      })      
    }
    
    function editComment(ID) {
      $("#myModal").modal("toggle");  
      
      $.post( "admin.php?module=wiki&op=editComment", { ID: ID })
      .done(function( data ) {
        $("#modalBody").html(data);
      }) 
    }
    
    $( function() {
        $.post( "admin.php?module=wiki&op=showGlobalStats", {})
      .done(function( data ) {
        $("#globalStats").html(data);
      
         $("#tableStats").DataTable({
             "order": [[ 1, "desc" ]]
           }
         );
      })
        
        
        $( ".datepicker" ).datepicker({
          changeMonth: true,
          changeYear: true,
          dateFormat: "yy-mm-dd"
        });
    } );
    
    function updateLatestPage() {
       var title            =   $("#title").val();
       var tag              =   $("#tag").val();
       var language         =   $("#language").val();
       var createdFrom      =   $("#createdFrom").val();
       var createdTo        =   $("#createdTo").val();
       var lastUpdateTo     =   $("#lastUpdateTo").val();
       var lastUpdateFrom   =   $("#lastUpdateFrom").val();
       var visible          =   $("#visible").val();
       var status           =   $("#status").val();
       var category         =   $("#category").val();
       
       $("#latestPages").html("<div class=\'spinner-border text-primary\' role=\'status\'><span class=\'sr-only\'>Loading...</span></div>");
       
      $.post( "admin.php?module=wiki&op=showLatestPages", { createdFrom     : createdFrom, 
                                                            createdTo       : createdTo, 
                                                            lastUpdateTo    : lastUpdateTo, 
                                                            lastUpdateFrom  : lastUpdateFrom, 
                                                            title           : title, 
                                                            tag             : tag, 
                                                            visible         : visible, 
                                                            status          : status, 
                                                            category        : category, 
                                                            language        : language })
      .done(function( data ) {
        $("#latestPages").html(data);
      
         $("#tablesortable").DataTable({
             "order": [[ 1, "desc" ]]
           }
         );
    });
    }
</script>';