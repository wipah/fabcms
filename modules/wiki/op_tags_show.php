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

if (!$core->loaded)
    die('Direct call detected.tag.');

if (!isset($path[3])) {
    echo 'No tag was passed';
    return;
}

$tag_trackback = $core->in($path[3]);

$query = 'SELECT P.title, P.trackback 
          FROM '      . $db->prefix . 'wiki_pages AS P
          LEFT JOIN ' . $db->prefix . 'wiki_pages_tags AS T
          ON T.page_ID = P.ID
          WHERE tag_trackback = \'' . $tag_trackback . '\' 
            AND visible = 1 
            AND service_page != 1;';

$db->setQuery($query);

if (!$result = $db->executeQuery()){

    $relog->write(['type'      => '4',
                   'module'    => 'WIKI',
                   'operation' => 'wiki_page_show_query_error',
                   'details'   => 'Cannot select the page. Query error. ' . $query,
    ]);

    echo 'Query error.';
    return;
}

if (!$db->numRows) {
    echo $language->get('wiki', 'wikiShowTagNoPages');
    return;
}

echo '<h2>' . $language->get('wiki','wikiShowTagsH1') . '</h2><!--FabCMS-hook:showTagTop-' . $tag . '-->';

while ($row = mysqli_fetch_array($result)){
    echo '<a href="' . $URI->getBaseUri() . $this->routed . '/' . $row['trackback'] . '/">' . $row['title'] .'</a> -';
}

echo '<!--FabCMS-hook:showTagBottom-' . $tag . '-->';