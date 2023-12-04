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



$out .= '
      <li class="nav-item dropdown">
        <a href="admin.php?module=wiki" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-oggle="dropdown" aria-haspopup="true" aria-expanded="false">
          User
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="admin.php?module=user">Homepage</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="admin.php?module=user&op=add"">Add user</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="admin.php?module=user&op=groups">Groups</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="admin.php?module=user&op=getPasswordHash">Get password hash</a>
        </div>
    </li>';
