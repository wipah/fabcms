<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 30/11/2017
 * Time: 12:34
 */


$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_items
          WHERE enabled = 1';

if (!$result = $db->query($query))
    return false;


if (!$db->affected_rows)
    return false;

while ($row = mysqli_fetch_assoc($result)) {

    $this->result .= '<url>
                        <loc>' .
        $URI->getBaseUri() . $core->router->getRewriteAlias('shop') . '/item/' . $row['ID'] . '-' . $row['trackback'] . '/</loc>
                      </url>' . PHP_EOL;

}
