<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/07/2017
 * Time: 16:11
 */

namespace CrisaSoft\FabCMS;

class parts{

    public $partsRegistered = array();
    public $module;
    public $data;

    function isPartRegistered($part){
        if (isset($this->partsRegistered[$part])){
            return true;
        } else {
            return false;
        }
    }
}