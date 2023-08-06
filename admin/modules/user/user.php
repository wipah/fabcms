<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
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
if (!$core->adminBootCheck())
    die("Check not passed");

// todo: this should be globally loaded
$this->addJsFile($URI->getBaseUri(true) . 'lib/datatables/js/jquery.dataTables.min.js');
echo '
<style type="text/css" title="currentStyle">
	@import "' . $URI->getBaseUri(true) . 'lib/datatables/css/jquery.dataTables.css";
</style>';

switch ($_GET['op']) {
    case 'config':
        require_once 'op_config.php';
        break;
    case 'getPasswordHash';
        require_once 'op_get_password_hash.php';
        break;
    case 'edit':
    case 'add':
    case 'new':
        require_once 'op_crud.php';
        break;
    case 'updatePassword':
        require_once 'op_crud_ajax_update_password.php';
        break;
    case 'getUsers':
        require_once 'op_ajax_get_users.php';
        break;
    case 'groups':
        require_once 'op_groups.php';
        break;
    default:
        require_once 'op_default.php';
        break;
}