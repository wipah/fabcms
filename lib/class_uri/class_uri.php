<?php
/**
FabCMS - Reinventing the wheel.

Copyright (C) 2010-2012  Fabrizio Crisafulli

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. 
 */

class URI {
  /**
   * Path has https? For example https://secure.domain.com/
   * @var bool
   */
  var $useHTTPS = FALSE;

  /**
   * Server is listening to a specific port? IE: http://www.domain.com:8080
   * @var bool */
  var $usePort = FALSE;

  /**
   * Port where server is listeing to.
   * @var int
   */
  var $port = 80;

  /**
   * Domain where FabCMS is called from.
   * @var
   */
  var $domain;

  /**
   * Subdirectory where FabCMS is installed to. If FabCMS is not installed inside a subdir (IE: http://www.domain.com)
   * don't set this variable because slash is automatically added.
   * @var
   */
  var $subDirectory;

  /**
   * Returns full http(s) path. IE: https://www.domain.ext:8080/subdomain/en/
   * Please note that this function returns the <b>language</b> too, if the site is set on multilang directive.
   * If you wish to omit the language set <b>$omitLanguageCode</b> to true if you want to omit 2-char language. For example http://example.com/subdir/module/
   * instead of http://example.com/subdir/en/module/
   * If you wish to force the language use the $forceLanguage variable
   * @param bool $omitLanguageCode
   * @param null $forceLanguage
   * @return string
   */
  public function getBaseUri($omitLanguageCode = false, $forceLanguage = null) {
    global $core;
    global $conf;

    return
      ( $this->useHTTPS ? 'https://' : 'http://') .
      $this->domain .
      ($this->usePort === true ? ':' . $this->port : '') .
      '/' . (isset($this->subDirectory) && FALSE === empty($this->subDirectory) ? $this->subDirectory . '/' : '') .
      ( $conf['multilang'] === true ? ($omitLanguageCode === false ? (is_null($forceLanguage) ? $core->multiLangPath() : $forceLanguage . '/' ) : '' ) : '' );
  }
}