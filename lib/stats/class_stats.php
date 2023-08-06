<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 23/03/2018
 * Time: 12:04
 */

namespace CrisaSoft\FabCMS;

class stats
{

    function get($filter)
    {
        if (isset($filter['module'])) {
            if (is_array($filter['module'])) {

            } else {

            }
        }

    }

    function write($data)
    {
        global $db;
        global $user;
        global $core;
        global $mobileDetect;

        
        if ( isset($_SERVER['HTTP_USER_AGENT']) && preg_match('#bot|crawl|spider|mediapartner|slurp|teoma|yandex|yeti|mediapartners#i', $_SERVER['HTTP_USER_AGENT'])){
            $bot = $core->in($_SERVER['HTTP_USER_AGENT']);
            $is_bot = 1;
        } else {
            $is_bot = 0;
        }

        $query = 'INSERT INTO ' . $db->prefix . 'stats 
        (
            user_ID,
            date,
            module,
            submodule,
            IP,
            uri,
            IDX,
            refer,
            agent,
            is_mobile,
            is_bot,
            bot
        )
        
        VALUES
        
        (
            ' . ($user->logged === true ? $user->ID : 'null') . ',
            NOW(),
            \'' . ($core->in($data['module'])) . '\',
            \'' . ($core->in($data['submodule'])) . '\',
            \'' . ($core->in($_SERVER['REMOTE_ADDR'], true)) . '\',
            \'' . ($core->in($_SERVER['REQUEST_URI'], true)) . '\',
            \'' . ((int)$data['IDX']) . '\',
            \'' . ($core->in($_SERVER['HTTP_REFERER'], true)) . '\',
            \'' . ($core->in($_SERVER['HTTP_USER_AGENT'], true)) . '\',
            \'' . ($mobileDetect->isMobile() === true || $mobileDetect->isTablet() ? '1' : '0') . '\',
            '. $is_bot .',
            \'' . $bot . '\'
        )';

        $db->setQuery($query);

        if ($db->executeQuery('insert')) {
            return true;
        } else {
            return false;
        }
    }

}