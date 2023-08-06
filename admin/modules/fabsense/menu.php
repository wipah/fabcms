<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 31/08/2018
 * Time: 15:34
 */

if (!$core->adminBootCheck())
    die("Check not passed");

$out .= '
      <li class="nav-item">
        <a class="nav-link" href="admin.php?module=fabsense">FabSense</a>
      </li>';