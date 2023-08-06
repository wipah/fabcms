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
if (!$core->adminBootCheck())
    die("Check not passed");

switch ($_GET['op']) {
  case 'edit':
  case 'new':
    include 'op_editor.php';
    break;
  case 'saveUpdate':
    include 'op_save_update.php';
    break;
  case 'saveNew':
    include 'op_save_new.php';
    break;
    default:
        require_once 'op_default.php';
        break;
}

