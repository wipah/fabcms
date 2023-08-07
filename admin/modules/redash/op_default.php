<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/12/2017
 * Time: 12:44
 */

if (!$core->loaded || !$user->isAdmin)
    die("Security");

$modulesTable = explode('|',
                        $core->getConfig('admin', 'adminHomepageModulePosition', 'extended_value'));

echo '<div class="row FabCMS-Admin-HomepageIcons mb-5">';

$i = 1;
foreach ($modulesTable as $singleModule) {
    $singleModule = htmlentities($singleModule);

    if ($i === 12 ) {
        echo '</div>
              <div class="row">';
        $i = 1;
        $closeTag = true;
    }

    echo '<div class="col-md col-sm ">
            <a href="admin.php?module=' . $singleModule . '">
                <img src="' . $URI->getBaseUri(true) . '/admin/modules/' . $singleModule .'/_info/icon.png" alt="module icon">
            </a>
            <h4 class="FabCMS-Admin-HomepageModuleName">' . ucfirst($singleModule) . '</h4>
          </div>';

    $i++;
}
if ($closeTag === true)
    echo '</div>';

echo '</div>';

$query = 'SELECT type, 
                 COUNT(*) AS total 
          FROM ' . $db->prefix . 'relog
          WHERE date >= CURRENT_DATE - INTERVAL 30 DAY
          GROUP BY type
          ORDER BY type DESC';

if (!$result = $db->query($query)) {
    $resultRelog = 'Query error. ' . $query;
} else {
    if (!$db->affected_rows) {
        $resultRelog = 'No log';
    } else {
        $resultRelog = '
        <small class="float-right">
            <a href="admin.php?module=relog">Relog monitor</a>
          </small>
          
        <table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>Type</th>
                <th>Events number</th>
              </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $resultRelog .= '<tr>
                <td>' . $row['type'] . '</td>
                <td>' . $row['total'] . '</td>
              </tr>';
        }

        $resultRelog .= '
            </tbody>
          </table>';
    }
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'relog 
          ORDER BY ID DESC 
          LIMIT 5';

if (!$result = $db->query($query)) {
    $relogLatest = 'Query error. ' . $query;
} else {
    if (!$db->affected_rows) {
        $relogLatest = 'No log.';
    } else {
        $relogLatest .= '
    <div class="table-responsive">
        <table class="table">
            <thead>
              <tr>
                <th>Type</th>
                <th>Page</th>
                <th>User ID</th>
                <th>Operation</th>
              </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $relogLatest .= '
            <tr>
                <td>' . $row['type'] . '</td>
                <td>' . $row['page'] . '</td>
                <td>' . $row['user_ID'] . '</td>
                <td>' . $row['operation'] . '</td>
             </tr>';
        }

        $relogLatest .= '
            </tbody>
          </table>
        </div>';
    }
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'users 
          ORDER BY ID DESC LIMIT 10';

if (!$result = $db->query($query)) {
    $latestUsers = $query;
} else {
    if (!$db->affected_rows) {
        $latestUsers = 'No users.';
    } else {

        $latestUsers = '<table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Neswletter</th>
                <th>Enabled</th>
              </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $latestUsers .= '
            <tr>
                <td>' . $row['username'] . '</td>
                <td>' . $row['email'] . '</td>
                <td>' . $row['newsletter'] . '</td>
                <td>' . $row['enabled'] . '</td>
            </tr>
            ';
        }

        $latestUsers .= '</tbody>
        </table>';
    }
}

$query = 'SELECT T.*,
          TU.username AS start_username,
          TL.username AS latest_reply_username
          FROM ' . $db->prefix . 'forum_topics AS T 
          LEFT JOIN ' . $db->prefix . 'users AS TU
            ON T.user_ID = TU.ID
          LEFT JOIN ' . $db->prefix . 'users AS TL
            ON T.latest_reply_user_ID = TL.ID

          ORDER BY ID DESC LIMIT 10';

if (!$result = $db->query($query)) {
    $latestTopic = $query;
} else {
    if (!$db->affected_rows) {
        $latestTopic = 'No topics.';
    } else {

        $latestTopic = '<table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>Topic</th>
                <th>Posted by</th>
                <th>Last reply by</th>
                <th>Reply count</th>
              </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $latestTopic .= '
            <tr>
                <td>
                    <a target="_blank" href="' . $URI->getBaseUri() . '/forum/' . $row['ID'] . '-' . $row['topic_trackback'] . '/">' . $row['topic_title'] . '</a>
                </td>
                <td>' . $row['start_username'] . '</td>
                <td>' . $row['latest_reply_username'] . '</td>
                <td>' . $row['reply_count'] . '</td>
            </tr>
            ';
        }

        $latestTopic .= '</tbody>
        </table>';
    }
}


$query = 'SELECT C.*,
            U.username, 
            C.ID AS cart_ID,
            (
              SELECT SUM(final_price) AS total
              FROM ' . $db->prefix . 'shop_cart_items AS I
              WHERE I.cart_ID = cart_ID
            ) as total 
          FROM ' . $db->prefix . 'shop_carts AS C
          LEFT JOIN ' . $db->prefix . 'users AS U
            ON C.user_ID = U.ID 
          WHERE status = 1
          GROUP BY C.ID
          ORDER BY ID DESC
          LIMIT 10';

if (!$result = $db->query($query)) {
    echo 'Query error.' . $query;

    return;
} else {
    if (!$db->affected_rows) {
        $latestOrders = 'No orders';
    } else {
        $latestOrders = '<table class="table table-bordered table-condensed table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>';

        while ($row = mysqli_fetch_array($result)) {
            $latestOrders .= '<tr>
                <td>' . $row['ID'] . '</td>
                <td>' . $row['username'] . '</td>
                <td>' . $row['total'] . '</td>
              </tr>';
        }

        $latestOrders .= '</tbody></table>';
    }
}

$query = 'SELECT * 
          FROM ' . $db->prefix . 'wiki_pages AS P
          ORDER BY ID DESC
          LIMIT 10;';

if (!$result = $db->query($query)) {
    $latestWikiPages = 'Query error.' . $query;

} else {
    if (!$db->affected_rows) {
        $latestWikiPages = 'No pages';
    } else {
        $latestWikiPages = '<table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>Page</th>
                <th>Lang</th>
                <th>Hits</th>
                <th>Enabled</th>
              </tr>
            </thead>
            <tbody>
              ';
        while ($row = mysqli_fetch_array($result)) {
            $latestWikiPages .= '
             <tr>
                <td>
         
                    <a href="admin.php?module=wiki&op=editor&ID=' . $row['ID'] . '">' . $row['title'] . '</a>
         
                    <a href="' . $URI->getBaseUri(true) . $row['language'] . '/wiki/' . $row['trackback'] . '/" class="btn btn-default btn-sm float-right" aria-label="View page">
                        <span class="glyphicon glyphicon-globe" aria-hidden="true"></span>
                    </a>           
                </td>
                <td>' . $row['language'] . '</td>
                <td>' . $row['hits'] . '</td>
                <td>' . ((int)$row['visible'] === 1 ? '<span style="color:green">&#10004; </span>' : '<span style="color:red">&#10006;</span>') . '</td>
             </tr>';
        }

        $latestWikiPages .= '</tbody></table>';
    }
}

$query = 'SELECT C.date, 
                 C.visible, 
                 P.title
          FROM ' . $db->prefix . 'wiki_comments AS C
            LEFT JOIN ' . $db->prefix . 'wiki_pages AS P
          ON C.page_ID = P.ID
          ORDER BY C.ID DESC
          LIMIT 10
          ';

if (!$result = $db->query($query)) {
    $latestComments = 'Query error.' . $query;
} else {
    if (!$db->affected_rows) {
        $latestComments = 'No row';
    } else {
        $latestComments = '<table class="table table-bordered table-condensed table-striped">
            <thead>
              <tr>
                <th>Date</th>
                <th>Page</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>';

        while ($row = mysqli_fetch_array($result)) {
            $latestComments .= '<tr>
                                    <td>' . $row['date'] . '</td>
                                    <td>' . $row['title'] . '</td>
                                    <td>' . ((int)$row['visible'] === 1 ? '<span style="color:green">&#10004; </span>' : '<span style="color:red">&#10006;</span>') . '</td>
                                </tr>';
        }

        $latestComments .= '</tbody></table>';

    }

}

// Select top yesterday pages
$query = '
SELECT COUNT(S.ID) AS hits, 
                      P.title,
                      P.ID
FROM fabcms_stats AS S
LEFT JOIN fabcms_wiki_pages P
  ON P.ID = S.IDX
WHERE (S.date     >= \'' . date('Y-m-d') . ' 00:00:00\' - INTERVAL 1 DAY
      AND S.date <  \'' . date('Y-m-d') . ' 00:00:00\') 
GROUP BY P.title
ORDER BY hits DESC
LIMIT 10';

if (!$resultYesterdayTopPages = $db->query($query)) {
    echo $yesterdayTopPages = 'Query error. ' . $query;
} else {
    if (!$db->affected_rows) {
        $yesterdayTopPages = 'No data. ';
    } else {
        $yesterdayTopPages = '<table class="table">
            <thead>
              <tr>
                <th>Page</th>
                <th>Hits</th>
              </tr>
            </thead>
            <tbody>';

        while ($rowYesterdayTopPages = mysqli_fetch_assoc($resultYesterdayTopPages)) {
            $yesterdayTopPages .= '
                <tr>
                    <td><a href="admin.php?module=wiki&op=editor&ID=' . $rowYesterdayTopPages['ID'] . '">' . $rowYesterdayTopPages['title'] . '</a></td>
                    <td>' . $rowYesterdayTopPages['hits'] . '</td>
                </tr>
            ';
        }
        $yesterdayTopPages .= '</tbody></table>';
    }
}


// Select top today pages
$query = '
SELECT COUNT(S.ID) AS hits, 
                     P.title,
                     P.ID
FROM fabcms_stats AS S
LEFT JOIN fabcms_wiki_pages P
  ON P.ID = S.IDX
WHERE S.date >= \'' . date('Y-m-d') . ' 00:00:00\' 
GROUP BY P.title
  ORDER BY hits DESC
  LIMIT 10';

if (!$resultTodayTopPages = $db->query($query)) {
    echo $todayTopPages = 'Query error. ' . $query;
} else {
    if (!$db->affected_rows) {
        $todayTopPages = 'No data';
    } else {
        $todayTopPages = '<table class="table">
            <thead>
              <tr>
                <th>Page</th>
                <th>Hits</th>
              </tr>
            </thead>
            <tbody>';

        while ($rowTodayTopPages = mysqli_fetch_assoc($resultTodayTopPages)) {
            $todayTopPages .= '
                <tr>
                    <td><a href="admin.php?module=wiki&op=editor&ID=' . $rowTodayTopPages['ID'] . '">' . $rowTodayTopPages['title'] . '</a></td>
                    <td>' . $rowTodayTopPages['hits'] . '</td>
                </tr>
            ';
        }
        $todayTopPages .= '</tbody></table>';
    }
}
echo '
<style type="text/css">
    .separator{
        border: 1px solid dodgerblue;
        padding: 4px;
        background-color: #44AAFF;
        color: white;
    }
</style>

<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4"><h4 class="separator">Relog (latest 30 days)</h4>    ' . $resultRelog . '<br/>
    <h4 class="separator">Relog latest events</h4>
    ' . $relogLatest . '
    </div>
    <div class="col-md-4"><h4 class="separator">Latest users</h4>    ' . $latestUsers . '</div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4 class="separator">Top pages (yesterday)</h4>
        ' . $yesterdayTopPages . '
    </div>
    <div class="col-md-6">
        <h4 class="separator">Top pages (today)</h4>
        ' . $todayTopPages . '
    </div>
</div>

<div class="row">
    <div class="col-md-6"><h4 class="separator">Latest topics</h4>' . $latestTopic . '</div>
    <div class="col-md-6"><h4 class="separator">Latest Replies</h4></div>
</div>

<div class="row">
    <div class="col-md-6"><h4 class="separator">Latest orders</h4>' . $latestOrders . '</div>
    <div class="col-md-6"></div>
</div>

<div class="row">
    <div class="col-md-6">
        <h4 class="separator">Latest pages</h4>' . $latestWikiPages . '
    </div>
    <div class="col-md-6">
        <h4 class="separator">Latest comments</h4>' . $latestComments . '
    </div>
</div>';