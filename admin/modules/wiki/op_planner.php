<?php
if (!$core->adminBootCheck())
    die("Check not passed");

$template->navBarAddItem('Wiki', 'admin.php?module=wiki');
$template->navBarAddItem('Statistics', 'admin.php?module=wiki&op=planner');

$this->addTitleTag("Wiki - Planner");

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_planner 
          WHERE enabled = 1';

if (!$result = $db->query($query)) {
    echo $query;
    return;
}

if (!$db->affected_rows) {
    echo 'No planner were defined.';
    return;
}

$headers   = [];
$contents  = [];

while ($row = mysqli_fetch_assoc($result)) {
    $headers[] = $row['title'];
    $sql = $row['sql'];

    $contents[] = $row['notes'] . '<hr/>' . createTable_from_sql_select_query($sql, $row['title']);

}

echo $template->getTabs('planner',$headers, $contents, []);

function createTable_from_sql_select_query($query, $title) {
    global $db;
    global $core;

    $title = $core->getTrackback($title);

    if (!$result = $db->query($query) ){
        return 'Query error. ' . $query;
    }

    $headings = json_decode(json_encode($result->fetch_fields()), true);

    $headings = array_column($headings, 'name');

    $return = '<div class="table-responsive">
                <table class="table table-responsive table-bordered table-striped">
                    <thead>
                        <tr>';

    for ($x = 0; $x <= (count($headings) - 1); $x++) {
        $return .= '<th>' . ucwords(str_replace('_', ' ', (strtolower($headings[$x])))) . '</th>';
    }

    $return .= '<th>Operations</th>';

    $return .= '</tr>
              </thead>
              <tbody>';

    $i = 0;
    while ($row = $result->fetch_array()) {
        $return .= '<tr>';

        for ($x = 0; $x <= (count($headings) - 1); $x++) {
            $return .= '<td>' . $row[$x] . '</td>';
        }

        $return .= '    <td>
                            <a target="blank" 
                               href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">Edit page
                            </a>
                        </td>
                    </tr>';
        $i++;
    }
    $return .= '    </tbody>
                </table>
            </div>
            
            <script>
                $("#home-tab-' . $title . '").append(" (' . $i .')");
            </script>';

    return $return;
}