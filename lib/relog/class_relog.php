<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 14/12/2017
 * Time: 12:58
 */

Namespace CrisaSoft\FabCMS;

class relog
{
    /**
     * Write an entry inside the FabCMS logger.
     *
     * @param array $dataArray (module, operation, details, type)
     */
    public function write(array $dataArray)
    {
        global $db;
        global $core;
        global $user;
        global $conf;
        global $conf;
        /*
         *
         * [TYPE]
         * 0 = debug / info;
         * 1 = log;
         * 2 = warning;
         * 3 = error;
         * 4 = critical;
         *
         */

        if ($conf['relogDebug'] !== true && ($dataArray['type'] === 0 || is_null($dataArray['type'])))
            return;

        if (!isset($dataArray['module']))
            return;

        if (!isset($dataArray['operation']))
            return;

        if (!isset($dataArray['details']))
            $dataArray['details'] = '';

        if (!isset($dataArray['type']))
            $dataArray['type'] = 0;

        if (!isset($user->logged) || $user->logged === false)
            $user->ID = 0;

        $query = 'INSERT INTO ' . $db->prefix . 'relog
                  (
                    date,
                    module,
                    type,
                    operation,
                    details,
                    user_ID,
                    IP,
                    page,
                    referer
                  ) 
                  VALUES
                  (
                    NOW(),
                    \'' . $core->in($dataArray['module'], true) . '\',   
                    \'' . $dataArray['type'] . '\',   
                    \'' . $core->in($dataArray['operation'], true) . '\',   
                    \'' . $core->in($dataArray['details'], true) . '\',   
                    ' . $user->ID . ',
                    \'' . $_SERVER['REMOTE_ADDR'] . '\',   
                    \'' . $core->in($_SERVER['REQUEST_URI'], true) . '\',
                    \'' . $core->in( $_SERVER['HTTP_REFERER']) . '\'   
                  )
                  ';

        $db->query($query);

    }

}
