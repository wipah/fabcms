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

if (!$core->loaded) {
  die('Direct call detected');
}

$this->noTemplateParse = TRUE;

$siteMap = $core->classLoader('sitemap', 'sitemap');

if (file_exists('config.php'))
    require_once 'config.php';

if ($path[2] == 'create') {
  if ( $conf['sitemap']['private'] === true ){
      if ($conf['sitemap']['privatePath'] == $path[3]){
          $log->write('sitemap_generated_with_private_path','Sitemap',
                      'Sitemap was generated using private path: ' .
                          htmlentities($path[3]) . '.');

          $siteMap->generate();
      } else {
      $log->write('sitemap_errer','Sitemap',
          'Sitemap was not generated. Passed private path is: ' .
          htmlentities($path[3]) . '.');
      echo 'Cannot generate sitemap';
      }
  } else {
      $log->write('sitemap_errer','Sitemap',
          'Sitemap was generated without any path.');
      $siteMap->generate();
  }
}