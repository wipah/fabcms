<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Asus
 * Date: 12/07/13
 * Time: 21.50
 * To change this template use File | Settings | File Templates.
 */

switch ($_GET['command']){
    case 'render';
        include 'op_checkout_render.php';
        break;
}