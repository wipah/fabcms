<?php

if (!isset($core->adminLoaded)) {
    echo 'Chiamata diretta.';
    return;
}
$template->navBarAddItem('Hooks editor', 'admin.php?module=hooks');


echo '<h1>Hooks editor</h1>';

$template->sidebar .= $template->simpleBlock('Quick links', '<a href="admin.php?module=hooks&op=new">New hook</a><br />');

$query = "SELECT * FROM {$db->prefix}hooks";
$db->setQuery($query);

if (!$db->executeQuery('select')) {
    echo 'Query error. ' . $query;
}

if (!$db->numRows) {
    echo 'No records. Would you like to <a href="admin.php?module=hooks&op=add">add one block</a>?';
}
else {
    $dbData = $db->getResultAsObject();

    $table = '
	<table id="tablesortable" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>enabled</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
	';

    while ($row = mysqli_fetch_array($dbData)) {
        $table .=
            "<tr>
			<td>{$row['ID']}</td>
			<td>{$row['name']}</td>
			<td>{$row['enabled']}</td>
			<td><a href='admin.php?module=hooks&op=edit&ID={$row['ID']}'>Edit</a></td>
		</tr>";
    }

    $table .= "</tbody>
     </table>";


    echo $table;

    echo '<script>
         $("#tablesortable").DataTable({
             "order": [[ 0, "desc" ]]
           }
         );
</script>';
}