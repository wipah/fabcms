<?php

// Videos
$query = "SELECT * 
          FROM {$db->prefix}formazione_media ";
$db->setQuery($query);
$result = $db->executeQuery('select');

while ($row = mysqli_fetch_array($result)) {
    $this->result .= '<url>
                        <loc>'. $URI->getBaseUri() . $core->router->getRewriteAlias('formazione') . '/video/' . $row['name_trackback'] . '/</loc>
                        <video:video>
                        <video:title>' . $row['name'] . '</video:title>
                        <video:live>no</video:live>
                        <video:description>' . $row['description_short'] . '</video:description>
                        <video:requires_subscription>' . ( (int) $row['access_level'] === 1 ? 'yes' : 'no') .'</video:requires_subscription>
                        </video:video>
                      </url>' . "\n";

}