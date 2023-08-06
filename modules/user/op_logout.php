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

// Elimina le sessioni dal database
$query = '
DELETE
FROM ' . $db->prefix . 'sessions
WHERE user_ID = \'' . $user->ID . '\'
LIMIT 1;
';

$db->setQuery($query);
if (!$db->executeQuery('DELETE')) {
    $relog->write(['type'      => '4',
                   'module'    => 'USER',
                   'operation' => 'user_logout_delete_session',
                   'details'   => 'Unable to delete session. User ID is: ' . $user->ID,
    ]);
    echo 'Error. Unable to delete session from database.';
}

$relog->write(['type'      => '2',
               'module'    => 'USER',
               'operation' => 'user_logout_ok',
               'details'   => 'User logged out in. User ID : ' . $user->ID,
]);

// Elimina il cookie
setcookie('ID', $linea['ID'], time() - (60 * 60 * 24 * 30), '/');
setcookie(('hash'), $hash, time() - (60 * 60 * 24 * 30), '/');

echo '
<!--FabCMS-hook:beforeLogout-->
' . $language->get('user', 'logoutLogoutOk') . '
<!--FabCMS-hook:afterLogout-->
';