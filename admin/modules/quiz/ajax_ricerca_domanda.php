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
$this->noTemplateParse = TRUE;

// Gets alla categoryes
$query = 'SELECT * FROM ' . $db->prefix .'quiz_categories';
$db->setQuery($query);
$db->executeQuery();
$arrayCat = array();
while ($row = mysqli_fetch_array($db->getResultAsObject())){
    $arrayCat[$row['ID']] = $row['nome'];
}
$where = '';
if (isset($_POST['term'])){
    if (strlen($where) === 0 )
        $where = 'WHERE ';
    $where .= 'domanda LIKE \'%' . $core->in($_POST['term']) . '%\' AND ';
}

$where = substr($where, 0, -4);

$query = 'SELECT * FROM ' . $db->prefix . 'quiz_questions ' . $where . ';';

$db->setQuery($query);

if (!$result = $db->executeQuery('select')) {
    echo 'Query error. ' . $query;
    return;
}

echo '<i>' . $db->numRows . '</i> founds.<br />';

if (!$db->numRows)
    return;

$db->executeQuery();
echo '
	<table cellpadding="0" cellspacing="0" border="0" class="dataTable" id="tableItems' . (isset($_POST['appendIDToTable']) ? '_' . $ID : '') . '" width="100%">
	<thead>
		<tr>
			<th width="20">ID</th>
			<th width="60">Categorie</th>
			<th width="190">Domanda</th>
		</tr>
	</thead>
	<tbody>
	';

while ($row = mysqli_fetch_array($result)){
    $cats = explode('|', $row['categorie']);
    $categories = '';
    foreach ($cats as $cat){
        $categories .= $arrayCat[ (int) $cat] . ', ';
    }
    $categories = substr($categories,0,-2) . '.';

    echo '
    <tr>
        <td><a target="blank" href="admin.php?module=quiz&op=editaDomanda&ID=' . $row['ID'] . '">' . $row['ID'] . '</a></td>
        <td>' . $categories . '</td>
        <td>' . $row['domanda'] . '</td>
    </tr>';
}
echo '</tbody></table>';