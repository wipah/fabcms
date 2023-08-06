<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 11/04/2018
 * Time: 11:26
 */

namespace CrisaSoft\FabCMS;

class cronjobs
{

    public $additionalData;

    public $log = [];

    /** 1 = Ok
     *  2 = Has error
     * @var int
     */
    public $status  = 1;

    function writeLog($line){
        $this->log[] = $line;
    }

    function getLog(){
        $return = '';
        foreach ($this->log AS $singleLine){
            $return .= $singleLine . "<br/>\r\n";
        }

        return $return;
    }

}