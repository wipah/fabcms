<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 22/01/2019
 * Time: 12:13
 */
if (!$core->adminBootCheck())
    die("Check not passed");

$out .= '
      <li class="nav-item">
        <a class="nav-link" href="admin.php?module=fabmediamanager">Fabmediamanager</a>
      </li>';