<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2013
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

/*
 * Returns all of the topics for the specific keywords.
 */
function plugin_ipb4($dataArray)
{
    global $core;
    global $db;
    global $contents;
    global $user;
    global $URI;
    global $conf;

    if ($user->isAdmin) {
        $return = $dataArray['wholeString'] . ' ';
    } else {
        $return = '';
    }

    if (!isset($dataArray['parseInAdmin']) && $core->adminLoaded) {
        return $return;
    }


    if (!file_exists($conf['path']['baseDir'] . 'modules/connector/plugins/plg_ipb4_config.php')) {
        return '<div class="ui-state-error">Plugin IPB must be configured first</div>';
    }

    require_once $conf['path']['baseDir'] . 'modules/connector/plugins/plg_ipb4_config.php';

    $return = '';

    if (!isset($dataArray['keywords'])) {
        $query = '
        SELECT *
        FROM ' . $conf['ipb4']['prefix'] . 'forums_topics
        WHERE state = \'open\'
        ORDER BY tid DESC
        LIMIT 5';
    } else {
        $query = '
        SELECT *
        FROM ' . $conf['ipb4']['prefix'] . 'forums_topics
        WHERE ' . $db->buildExtendedQuery($dataArray['keywords'], 'title', ', ', 'OR') . '
        AND state = \'open\';';
    }
    $db->setQuery($query);
    if (!$result = $db->executeQuery()) {
        return 'Query error. ' . $query;
    } else {
        if (!$db->affected_rows) {
            $return .= 'Nel forum non esistono ancora discussioni simili all\'argomento di questa pagina. Se vuoi puoi <a href="' . $conf['ipb4']['path'] . '">iniziarne una</a>, ad esempio richiedere aiuto per comprendere qualche argomento.';
        } else {
            while ($row = mysqli_fetch_array($result)) {
                $return .= '&bull; <a href="' . $conf['ipb4']['path'] . 'topic/' . $row['tid'] . '-' . $row['title_seo'] . '/">' . $row['title'] . '</a><br/>';
            }
        }
    }
    return $return;
}