<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 01/06/2015
 * Time: 14:14
 */

$this->noTemplateParse = true;

if (strlen($_POST['filterPhrase']) > 1)
    $where .= ' phrase LIKE \'%' . $core->in($_POST['filterPhrase']) . '%\'';

if (strlen($_POST['filterIP']) > 1) {
    isset($where) ? $where .= ' AND ' : '';
    $where .= ' IP LIKE \'%' . $core->in($_POST['filterIP']) . '%\'';
}

if (isset($_POST['filterResult']) && (int)$_POST['filterResult'] > 0)
    $limit .= ' LIMIT ' . (int)$_POST['filterResult'];

$query = 'SELECT * FROM ' .
    $db->prefix . 'search_logs ' .
    (isset($where) ? ' WHERE ' . $where : ' ') .
    'ORDER BY ID desc ' .
    (isset($limit) ? $limit : '');

if (!$result = $db->query($query)) {
    echo 'Error. ' . $query;
} else {
    echo '
    <p>
    <table class="table table-striped table-borderd" id="searchAjaxTable" width="100%">
	    <thead>
            <tr>
                <th>Date</th>
                <th>Phrase</th>
                <th>Result</th>
                <th>Page</th>
                <th>Method</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>';
    while ($row = mysqli_fetch_array($result)) {
        echo '<tr>
              <td>' . $row['date'] . '</td>
              <td>' . $row['phrase'] . '</td>
              <td>' . $row['results'] . '</td>
              <td>' . $row['from_page'] . '</td>
              <td>' . $row['method'] . '</td>
              <td>' . $row['IP'] . '</td>
              </tr>';
    }
    echo '
</tbody>
</table>
</p>';
}