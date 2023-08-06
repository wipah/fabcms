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

function plugin_simplesearch() {
  global $core;
  global $URI;

  $style = '
  <style type="text/css">
    #inputSearch{
      background-image: url("' . $URI->getBaseUri(TRUE) . 'modules/search/css/search.png");
      background-repeat: no-repeat;
      padding-left: 16px;
    }
    </style>';

  return "$style
    <input id='inputSearch' type='text' name='search' value=''>
    <button id='inputSearchButton' onclick='searchPlugin();' '></button>


<script type=\"text/javascript\">
function searchPlugin(){
    searchBase = \"{$URI->getBaseUri()}search/simple/\";
    theValue = $(\"#inputSearchButton\").val();
}

$(function() {
    $(\"#inputSearchButton\" ).button({
        icons: {
            primary: \"ui-icon-search\"
        },
        text: true
    })});
</script>
";
}