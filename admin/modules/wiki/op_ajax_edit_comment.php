<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 12/04/2017
 * Time: 14:44
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$this->noTemplateParse = true;

if (!isset($_POST['ID'])){
    echo 'No ID was passed!';
    return;
}

if (isset($_GET['save'])){

    $ID = (int) $_POST['ID'];
    $text = $core->in($_POST['text']);
    $author = $core->in($_POST['author']);

    $_POST['enabled'] == 'true' ? $enabled = '1' : $enabled = '0';

    $query = 'UPDATE ' . $db->prefix . 'wiki_comments 
              SET 
              author = \'' . $author. '\',
              comment = \'' . $text . '\',
              visible = ' . $enabled . '  
              WHERE ID = ' . $ID . ' 
              LIMIT 1;';

    $db->setQuery($query);

    if (!$db->executeQuery('update')){
        echo 'Query error ' . $query;
        return;
    } else {
        echo 'Updated.';
    }

    return;
}

$ID = (int) $_POST['ID'];

$query = 'SELECT * FROM 
          ' . $db->prefix . 'wiki_comments 
          WHERE ID = ' . $ID . ' LIMIT 1; ';

$db->setQuery($query);
if (!$result = $db->executeQuery('select')){
    echo 'Query error: ' . $query;
    return;
}

if (!$db->numRows){
    echo 'No comment with the passed ID: ' . $ID;
    return;
}

$row = mysqli_fetch_assoc($result);

echo '

<div class="row">
    <div class="col-md-4">Author</div>    
    <div class="col-md-4"><input type="text" id="author" value="' . $row['author'] . '"/></div>    
</div>


<div class="row">
    <div class="col-md-4">Comment</div>    
    <div class="col-md-4"><textarea id="commentText">' . $row['comment'] . '</textarea></div>    
</div>

<div class="row">
    <div class="col-md-4">Enabled</div>
    <div class="col-md-4">
        <input id="commentEditChecked" type="checkbox" value="1" ' . ( (int) $row['visible'] == 1 ? 'checked="checked"' : '') . '/>
    </div>
</div>

<div class="row">
    <div class="col-md-12" id="updateStatus"></div>
</div>

<button onclick="updateComment(' . $ID . ');">Update comment</button>


<script type="text/javascript">
function updateComment(ID){
    
    var  enabled = $("#commentEditChecked").is(":checked");
    commentText = $("#commentText").val();
    author = $("#author").val();
    
    $.post( "admin.php?module=wiki&op=editComment&save", { ID: ID, enabled: enabled, text: commentText, author: author })
        .done(function( data ) {
            $("#comment-" + ID + "-author").html(author);
            $("#comment-" + ID + "-comment").html(commentText);
            $("#comment-" + ID + "-visible").html(enabled);
           
            $("#updateStatus").html(data);
    });

}
</script>
';