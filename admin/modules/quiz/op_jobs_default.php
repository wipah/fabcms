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

if (!$core->adminLoaded) {
    die('Direct call detected');
}

$query = '
SELECT * FROM ' . $db->prefix  . 'quiz_jobs
';

$db->setQuery($query);
if (!$db->executeQuery('select')){
    echo 'Query error. ' . $query;
    return;
}

if ($result = !$db->numRows){
    echo 'No jobs already set.';
}else{
    echo '
	<table cellpadding="0" cellspacing="0" border="0" class="dataTable" id="tableItems' . (isset($_POST['appendIDToTable']) ? '_' . $ID : '') . '" width="100%">
	<thead>
		<tr>
			<th>ID</th>
			<th>Job ID</th>
			<th>Language</th>
			<th>Title</th>
			<th>Questions per page</th>
			<th>Max questions</th>
		</tr>
	</thead>
	<tbody>';

    while ($row = mysqli_fetch_array($result)){
        echo '
        <tr>
            <td>' . $row['ID'] .'</td>
            <td>' . $row['job_IT'] .'</td>
            <td>' . $row['lang'] .'</td>
            <td>' . $row['title'] .'</td>
            <td>' . $row['questions_per_page'] .'</td>
            <td>' . $row['max_questions'] .'</td>
        </tr>
        ';
    }

    echo '</tbody>
    </table>';
}