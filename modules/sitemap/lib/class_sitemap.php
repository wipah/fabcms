<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class siteMap {
  public $result;

  public function generate() {
    global $conf;
    global $core;
    global $db;
    global $URI;
    global $log;

    $scanPath = $conf['path']['baseDir'] . 'modules';

    foreach (scandir($scanPath) as $res) {

      if ($scanPath == __FILE__) {
        continue;
      }

      // Strips any unwanted files
      if ($res == '.git' || $res == '..' || $res == '.') {
        continue;
      }

      $searchFilePath = $scanPath . '/' . $res . '/helper_sitemap.php';

      if (file_exists($searchFilePath)) {
        include $searchFilePath;
      }

    }
    // Send header
    header("content-type: text/xml; charset: utf-8");

    echo '<?xml version="1.0" encoding="UTF-8"?> ' . "\n" .
      '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
      xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
      xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
      $this->result .
      '</urlset> ';

  }
}