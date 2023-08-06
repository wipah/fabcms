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
if (!$core->loaded) {
    die('Direct call detected');
}

require_once($conf['path']['baseDir'] . 'lib/recaptcha/recaptchalib.php');

$template->moduleH1 = '<h1 class="FabCMSH1">' . $language->get('user', 'userManagement') . '</h1>';

switch ($path[2]) {
    case 'showuser':
        include 'op_showuser.php';
        break;
    case 'privacy':
        require_once 'op_privacy.php';
        break;
    case 'rules':
        require_once 'op_rules.php';
        break;
    case 'register':
        include 'op_register.php';
        break;
    case 'login':
        include 'op_login.php';
        break;
    case 'logout':
        include 'op_logout.php';
        break;
    case 'reset_password':
        include 'op_reset_password.php';
        break;
    case 'resend_email':
        include 'op_resend_email.php';
        break;
    case 'confirm':
        include 'op_confirm.php';
        break;
    case 'cp':
        include 'op_cp.php';
        break;
    default:
        include 'op_default.php';
        break;
}