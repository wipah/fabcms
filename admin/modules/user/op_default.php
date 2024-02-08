<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('User','admin.php?module=user');
$template->navBarAddItem('Default',);

// Count number of users
$query = 'SELECT count(ID) as total from ' . $db->prefix . 'users';

$result = $db->query($query);
$row = mysqli_fetch_assoc($result);
$template->sidebar .= $template->simpleBlock('Statistic', 'Total user: ' . $row['total']);

// Get latest users by month
$query = '
SELECT Count( ID ) AS registered, 
       MONTH( registration_date ) AS M, 
       YEAR ( registration_date ) AS Y
FROM `' . $db->prefix . 'users`
GROUP BY YEAR (registration_date), MONTH( registration_date )
ORDER BY Y DESC, M DESC;';

$result = $db->query($query);

$lastUser = '
<table class="table table-responsive table-bordered table-sm table-hover table-striped">
	<thead class="table-dark">
		<tr>
			<th>Year</th>
			<th>Month</th>
			<th>Count</th>
		</tr>
	</thead>
	<tbody>';

while ($row = mysqli_fetch_array($result)) {

    $lastUser .= '
        <tr>
            <td>' . $row['Y'] . '</td>
            <td>' . $row['M'] . '</td>
            <td>' . $row['registered'] . '</td>
        </tr>';
}

$lastUser .= '</tbody></table>
<script type="text/javascript">
$(document).ready(function(){
    $("#tableItems").dataTable();
});
</script>
';

// Get latest 5 users
$query = 'SELECT U.*,
            G.group_name,
            G.ID AS group_ID 
          FROM ' . $db->prefix . 'users AS U
          LEFT JOIN ' . $db->prefix . 'users_groups AS G
          ON U.group_ID = G.ID
          ORDER BY U.ID DESC
          LIMIT 5;';

if (!$result = $db->query($query)) {
    echo 'Query error. ' . $db->lastError . ' ' . $query;
    return;
}

if (!$db->affected_rows) {
    echo 'No user.';
    return;
}
$lastFiveUser = '
<table class="table table-responsive table-bordered table-sm table-hover table-striped">
	<thead class="table-dark">
		<tr>
			<th>Group</th>
			<th>Username</th>
			<th>Email</th>
			<th>Registration date</th>
			<th>Enabled</th>
			<th>Operations</th>
		</tr>
	</thead>
	<tbody>';

while ($row = mysqli_fetch_array($result)) {

    $lastFiveUser .= '
        <tr>
            <td>
                ' . $row['group_name'] . ' (' . $row['group_ID'] . ')
            </td>
            <td>
                <a href="admin.php?module=user&op=edit&ID=' . $row['ID'] . '">' . $row['username'] . '</a>
            </td>
            <td>' . $row['email'] . '</td>
            <td>' . $row['registration_date'] . '</td>
            <td>' . ((int)$row['enabled'] === 1 ? '<span style="color:green">&#10004; </span>' : '<span style="color:red">&#10006;</span>') . '</td>
            <td> <a href="?module=user&op=edit&ID=' . $row['ID'] . '">Edit</a></td>
        </tr>';
}

$lastFiveUser .= '</tbody></table>';

// Build group selecte
$query = 'SELECT * 
          FROM ' . $db->prefix . 'users_groups';

if (!$result = $db->query($query)){
    echo 'Query error. ' . $query;
    return;
}

if (!$db->affected_rows){
    echo 'No group!';
    return;
}

$selectGroups = '<select class="form-control " id="filterGroup"><option value="0">All groups</option>';
while ($row = mysqli_fetch_assoc($result)){
    $selectGroups .= '<option value="' . $row['ID'] . '">' . $row['group_name'] . ' (' . $row['ID'] . ')</option>';
}
$selectGroups .= '</select>';

echo '<h2>User management</h2>

<div class="FabCMS-filterBox">
	<div class="row">
	    
	    <div class="col-md-2">
	        <span class="FabCMS-filterValue">Name</span>
	            <input class="form-control input-md" type="text" id="filterNameUsers" />
	    </div>
	    
	    <div class="col-md-3">
	        <span class="FabCMS-filterValue">Email</span>
	            <input class="form-control input-md" type="text" id="filterEmailUsers" />
	    </div>

	    <div class="col-md-3">
	        <span class="FabCMS-filterValue">Tag</span>
	            <input class="form-control input-md" type="text" id ="filterTagUser" />
	    </div>
	    
	    <div class="col-md-3">
	        <span class="FabCMS-filterValue">Group</span>
	            ' . $selectGroups . '
	    </div>
	    
	    <div class="col-md-1"><span class="FabCMS-filterValue">Operations</span><br/>
	        <button class="btn btn-black" onclick="filterNames();">Search</button>
        </div>
    </div>
</div>

<div class="FabCMS-optionBox clearfix">
	<a class="btn btn-primary float-right" href="admin.php?module=user&op=add">Add an user</a>
	<a class="btn btn-primary float-right" href="admin.php?module=user&op=groups">Group manager</a>
	<a class="btn btn-primary float-right" href="admin.php?module=user&op=config">Config</a>
</div>

<div id="divUsers" class="FabCMS-filterResult clearfix"></div>

<div>
    <h2>Latest five users</h2>
    ' . $lastFiveUser . '
    <h2>Registration by month</h2>
' . $lastUser . '
</div>

<script type="text/javascript">
function filterNames() {
    nameFilter      =   $("#filterNameUsers").val(); 
    emailFilter     =   $("#filterEmailUsers").val(); 
    groupFilter     =   $("#filterGroup").val(); 
    tagFilter       =   $("#filterTagUser").val();
    
    $.ajax({
        type: "POST",
        url: "admin.php?module=user&op=getUsers",
        data: "nameFilter="     + nameFilter + 
              "&emailFilter="   + emailFilter + 
              "&groupFilter="   + groupFilter + 
              "&tagFilter="     + tagFilter
    }).done(function( data ) {
        $("#divUsers").html(data);
        $("#tableItems").dataTable();
    });
}
</script>';