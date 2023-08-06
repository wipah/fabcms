<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2017
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

if (!isset($core->loaded))
    die('Direct call detected. search.');

/*
$contents = $core->classLoader('contents_remove', 'contents_remove');

// Check and build the array of the miners
$minersTagPath  = $conf['path']['baseDir'] . '/modules/search/miners/tag/';
$minersTagArray = array();

foreach (glob($minersTagPath . '*') as $singleMiner) {
    $minersTagArray[] = basename($singleMiner, '.php');
}
*/

$query = ' 
SELECT P.ID,
	   P.trackback,
       P.content,
       P.title,
       MATCH (P.title) AGAINST      (\'' . $phrase . '\' WITH QUERY EXPANSION) AS title_score,
       MATCH (P.content) AGAINST    (\'' . $phrase . '\' WITH QUERY EXPANSION) AS content_score
FROM ' . $db->prefix . 'wiki_pages AS P
WHERE MATCH (P.content) AGAINST     (\'' . $phrase . '\' WITH QUERY EXPANSION)
       OR MATCH (P.title) AGAINST   (\'' . $phrase . '\' WITH QUERY EXPANSION)
       AND P.service_page != 1
       AND P.language =              \'' . $core->shortCodeLang . '\'
       AND P.visible = 1
ORDER BY title_score DESC, content_score DESC
LIMIT 50';


$db->setQuery($query);
if (!$result = $db->executeQuery('select')) {

    $relog->write(['type'      => '4',
                   'module'    => 'WIKI',
                   'operation' => 'wiki_search_helper',
                   'details'   => 'Query error in the helper. ' . $query,
    ]);

    $chunk .= 'QUERY ERROR';
    return;
}

if (!$db->numRows) {
    $this->totalResults = 0;
    $chunk .= '<br/>No result';
} else {

    $this->totalResults += $db->numRows;
    $this->results['wiki'] = array();

    while ($row = mysqli_fetch_array($result)) {

        // Check if the item should be parsed by any miner
        $arrayTags = explode(', ', $row['tags']);
        foreach ($arrayTags as $singleArraySearch) {

        }

        $theData = strip_tags($row['content']);

        $pos = stripos($theData, $phrase);
        if ($pos - 50 <= 0) {
            $snippet = substr($theData, 0, 300) . '...';
        } else {
            $snippet = '... ' . substr($theData, $pos - 50, 300) . '...';
        }

        $snippet = str_ireplace($phrase, '<strong>' . $phrase . '</strong>', $snippet);

        $this->results['wiki'][] = ['name'      => $row['title'],
                                    'trackback' => $URI->getBaseUri() .
                                                   $core->router->getRewriteAlias('wiki') . '/' .
                                                   $row['trackback'],
                                    'ID'        => $row['ID'],
                                    'snippet'   => $snippet
                                   ];
    }
}