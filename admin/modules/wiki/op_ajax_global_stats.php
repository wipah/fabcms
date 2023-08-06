<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 31/10/2018
 * Time: 16:56
 */

if (!$core->adminBootCheck())
     die("Check not passed");

$this->noTemplateParse = true;

if ( $conf['memcache']['enabled']  )
    $globalStats = $memcache->get('adminWikiGlobalStats');

if (!empty($globalStats)) {
    echo '<small>cached</small><br/>'.$globalStats;
} else {
// Global stats
    $query = '
 SELECT
   FWPM.ID,
   FWPM.title,

   week.totale   AS week,
   week_1.totale AS week_1,
   week_2.totale AS week_2,
   week_1.totale  - week_2.totale AS difference

   FROM ' . $db->prefix . 'wiki_pages FWPM

   LEFT JOIN
   (
       SELECT
            FS.IDX,
            FWP.language,
            COUNT(FS.ID) AS totale
       FROM ' . $db->prefix . 'stats FS
       LEFT JOIN ' . $db->prefix . 'wiki_pages FWP
            ON FWP.ID = FS.IDX
       WHERE FS.module = \'wiki\'
            AND FS.submodule = \'pageView\'
            AND (WEEK(FS.date) = @week)
            AND is_bot = 0
       GROUP BY(FS.IDX)
   ) AS week
     ON week.IDX = FWPM.ID
     AND week.language = FWPM.language

   LEFT JOIN
   (
        SELECT
            FS.IDX,
            FWP.language,
            COUNT(FS.ID) AS totale
        FROM ' . $db->prefix . 'stats FS
        LEFT JOIN ' . $db->prefix . 'wiki_pages FWP
            ON FWP.ID = FS.IDX
        WHERE FS.module = \'wiki\'
            AND FS.submodule = \'pageView\'
            AND (WEEK(FS.date) = @week -1)
            AND is_bot = 0
        GROUP BY(FS.IDX)
   ) AS week_1
     ON week_1.IDX = FWPM.ID
     AND week_1.language = FWPM.language

   LEFT JOIN
   (
        SELECT
            FS.IDX,
            FWP.language,
            COUNT(FS.ID) AS totale
        FROM ' . $db->prefix . 'stats FS
        LEFT JOIN ' . $db->prefix . 'wiki_pages FWP
            ON FWP.ID = FS.IDX
        WHERE FS.module = \'wiki\'
            AND FS.submodule = \'pageView\'
            AND (WEEK(FS.date) = @week -2)
            AND is_bot = 0
        GROUP BY(FS.IDX)
   ) AS week_2
     ON week_2.IDX = FWPM.ID
     AND week_2.language = FWPM.language
   WHERE FWPM.service_page != 1
     AND FWPM.visible = 1
     AND ( LENGTH(FWPM.internal_redirect) = 0 
            OR FWPM.internal_redirect IS NULL
   )
   ORDER BY difference ASC';

    mysqli_query($db->linkID, 'SET @week := WEEK(NOW());');

    if (!$resultStats = $db->query($query)) {
        echo 'Query error in stats. ' . $db->lastError . '<pre>' . $query . '</pre>';
        return;
    }

    $globalStats = '<h2>Global Stats</h2>
                    <table id="tableStats" class="table table-bordered table-striped table-condensed table-sm">';

    if (mysqli_num_rows($resultStats)){
        $globalStats .= '
         <thead>
           <tr>
             <th>Page</th>
             <th>'      . (date("W"))      .' (current)</th>
             <th>Week ' . (date("W") - 1)  .'</th>
             <th>Week ' . (date("W") - 2)  .'</th>
             <th>Difference</th>
           </tr>
         </thead>
         <tbody>';

        while ($rowStats = mysqli_fetch_assoc($resultStats))
        {
            $totalWeek += $rowStats['week'];
            $totalWeek_1 += $rowStats['week_1'];
            $totalWeek_2 += $rowStats['week_2'];
            $difference += ($rowStats['week_2'] - $rowStats['week_1']);

            $globalStats .= '
                  <tr>
                     <td>
                        <a href="admin.php?module=wiki&op=editor&ID=' . $rowStats['ID'] .'" target="_blank">' . $rowStats['title']  . '</a>
                     </td>
                     <td>' . $rowStats['week']   . '</td>
                     <td>' . $rowStats['week_1'] . '</td>
                     <td>' . $rowStats['week_2'] . '</td>
                     <td>' . ($rowStats['week_2'] - $rowStats['week_1']) . '</td>
                  </tr>';
        }

        $globalStats .= '
         </tbody>
         <tfoot>
            <tr>
                <td></td>
                <td>' . $totalWeek . '</td>
                <td>' . $totalWeek_1 . '</td>
                <td>' . $totalWeek_2 . '</td>
                <td>' . $difference . '</td>
            </tr>   
         </tfoot>
       </table>';
    } else {
        $globalStats .= 'Stats not found! First install?';
    }

    if ( $conf['memcache']['enabled']  )
        $memcache->set('adminWikiGlobalStats', $globalStats,0, 86400);

    echo $globalStats;
}