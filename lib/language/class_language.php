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
class language {
  /**
   * Check if string exists within the module
   * @param $module
   * @param $string
   * @return bool
   */
  public function stringExists($module, $string) {
    global $lang;
    if (isset($lang[$module][$string])) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

    /**
     * Gets the translation for the string given as <b>$string</b>
     * @param $module
     * @param $string
     * @param null $postOp
     * @return string
     */
  public function get($module, $string, $postOp = null) {
    global $lang;
    global $debug;
    if (isset($lang[$module][$string])) {
      return $lang[$module][$string];
    }
    else {
      $debug->write('warning','String ' . $string . ' not found.', 'LANGUAGE');
      return $string . ' &bull;';
    }
  }

  public function loadLang($module) {
    global $core;
    global $conf;
    global $lang;
    global $debug;

    $debug->write('info', 'Locating language for the module ' . $module, 'LANGUAGE');

    // Check for the correct file
    $pathToSearch = $conf['path']['baseDir'] . '/modules/' . $module . '/lang/' . $core->shortCodeLang . '.php';
    $pathToSearchEn = $conf['path']['baseDir'] . '/modules/' . $module . '/lang/en.php';

    if (file_exists($pathToSearch)) {
      $debug->write('info', 'Found language file at ' . $pathToSearch, 'LANGUAGE');
      include $pathToSearch;
    } elseif ($core->shortCodeLang !== 'en' && file_exists($pathToSearchEn)) {
      $debug->write('info', 'Not found language definition, loading default english located at: ' . $pathToSearchEn, 'LANGUAGE');
      include $pathToSearchEn;
    } else {
      $debug->write('warn', 'Failed to load language for both native (' . $core->shortCodeLang . ') and english', 'LANGUAGE');
      // @todo: noLanguage handler
    }
  }
}