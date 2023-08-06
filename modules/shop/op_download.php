<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 29/11/2017
 * Time: 09:44
 */

if (!$core->loaded)
    die("Direct access");

if (!$user->logged)
    die('Non autorizzato al download. Effettuare il login');


if (!isset($_GET['file'])) {

    $relog->write(['type'      => '3',
                   'module'    => 'SHOP',
                   'operation' => 'shop_download',
                   'details'   => 'Unable to download the file. File is missing. ',
    ]);

    echo '<div class="alert alert-danger">
            <strong>Errore!</strong> Manca il nome del file.
          </div>';

    return;
}
$file = $_GET['file'];

$file = str_replace('..', '', $file);
$file = str_replace('%', '', $file);
$file = str_replace('{', '', $file);
$file = str_replace('}', '', $file);
$file = str_replace("\r", '', $file);
$file = str_replace("\n", '', $file);

$file = $core->in($file);

// Check if exists a public file to download  (type = 0)
$query = 'SELECT * 
          FROM ' . $db->prefix . 'shop_item_files 
          WHERE file = \'' . $file . '\' 
          AND   type = 0 
          AND   enabled = 1
          LIMIT 1;';
$db->setQuery($query);

if (!$result = $db->executeQuery()) {
    $relog->write(['type'      => '4',
                   'module'    => 'SHOP',
                   'operation' => 'shop_download_public_search',
                   'details'   => 'Unable to find a public file. ' . $query,
    ]);

    echo 'Query error while public search.';
    return;
}

if ($db->numRows){

    $filename = __DIR__ . '/files/' . $file;

    if (!file_exists($filename)) {
        echo '<div class="alert alert-danger">
                <strong>Errore!</strong> Il file selezionato non è più disponibile al download. Contatta l\'assistenza per risolvare il problema.
              </div>';

        return;
    }

    $this->noTemplateParse = true;

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    exit;

} else {
    if (!isset($_GET['hash'])) {
        echo '
    <div class="alert alert-danger">
        <strong>Errore!</strong> Manca l\'hash del file.
    </div>';

        return;
    }
    $hash = $_GET['hash'];

    $hashComputed = md5($file . date('YmdG') . $conf['security']['siteKey']);

    if ($hashComputed !== $hash) {
        echo '<div class="alert alert-danger">
                <strong>Errore!</strong> Problema durante la fase di autorizzazione. <a href="' . $URI->getBaseUri() . $this->routed . '/orders/">Torna alla lista degli ordini</a>.
              </div>';

        $relog->write(['type'      => '3',
                       'module'    => 'SHOP',
                       'operation' => 'shop_download_hash_missing',
                       'details'   => 'Hash is missing.',
        ]);

        return;
    }

    // Check if file exists
    $filename = __DIR__ . '/files/' . $file;

    if (!file_exists($filename)) {

        $relog->write(['type'      => '2',
                       'module'    => 'SHOP',
                       'operation' => 'shop_download_file_not_avaliable',
                       'details'   => 'File is not avaliable. ',
        ]);

        echo '
            <div class="alert alert-danger">
                <strong>Errore!</strong> Il file selezionato non è più disponibile al download. Contatta l\'assistenza per risolvare il problema.
            </div>';

        return;
    }

    $this->noTemplateParse = true;

    if (!$fabShop->addDownload(basename($file))) {
        echo '
            <div class="alert alert-danger">
                <strong>Errore!</strong> Errore in fase di registrazione e autorizzazione del download.
            </div>';

        return;
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    exit;
}