<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!$core->loaded) {
    die('Direct call detected');
}

$this->noTemplateParse = true;

if (!$user->logged) {
    echo 'Not logged';
    return;
}

$query = 'SELECT q.ID, 
                 q.date, 
                 q.ok, 
                 q.ko,
                 q.blank, c.nome AS cat_name, c.ID as cat_ID FROM ' . $db->prefix . 'quiz_logs AS q
          LEFT JOIN '. $db->prefix .'quiz_categories AS c
          ON q.subtype = c.ID
          WHERE user_ID = ' . $user->ID . ' ORDER BY ID DESC';

if (!$result = $db->query($query)) {
    echo 'Query error';
    return;
}

if (!$db->affected_rows) {
    echo 'Gentile utente, non risultano quiz completati. <a href="' . $URI->getBaseUri() . $this->routed . '/scheda/">Prova a fare una scheda adesso</a>.';
    return;
}

echo '
<div class="table-responsive">
	<table id="table" class="dataTable table table-responsive table-striped table-bordered tableItems">
	<thead>
		<tr>
			<th width="20">ID</th>
			<th width="160">Data</th>
			<th width="160">Scheda</th>
			<th width="140">Corrette</th>
			<th width="100">Errate</th>
			<th width="100">Bianche</th>
			<th width="100">% corrette</th>
			<th width="100">Operazioni</th>
		</tr>
	</thead>
	<tbody>
';
while ($row = mysqli_fetch_array($result)) {


    $ok = count(explode('|', $row['ok']));
    if (!empty($row['ko'])){
        $ko = count(explode('|', $row['ko']));
    }else{
        $ko = 0;
    }

    if (!empty($row['blank'])){
        $blank = count(explode('|', $row['blank']));
    }else{
        $blank = 0;
    }


    echo '<tr>
          <td>' . $row['ID'] . '</td>
          <td>' . $core->getDateTime($row['date']) . '</td>
          <td>' . $row['cat_name'] . '</td>
          <td>' . $ok . '</td>
          <td>' . $ko . '</td>
          <td>' . $blank . '</td>
          <td>' . floor( ($ok / 10) * 100) . '%</td>
          <td>
            <a href="' . $URI->getBaseUri() . 'quiz/mylog/show/' . $row['ID'] . '">Visualizza scheda</a> | <br />
            <a href="' . $URI->getBaseUri() . 'quiz/scheda/' . $row['ID'] . '-' . $core->getTrackback($row['cat_name']) . '/">Nuovo quiz</a> |
          </td>
        </tr>';
}

echo '</tbody>
</table>
</div>';