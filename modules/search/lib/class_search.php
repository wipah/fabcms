<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 20/05/2015
 * Time: 20:18
 */
namespace CrisaSoft\FabCMS;

/**
 * Class search
 * @package CrisaSoft\FabCMS
 */
class search
{
    public $results = array();

    private $currentModule;
    private $totalResults = 0;

    /**
     * Search the <strong>phrase</strong>
     *
     * @param      $phrase
     * @param null $excludedModules
     * @param null $options
     */
    public function search($phrase, $excludedModules = null, $options = null)
    {
        global $conf;
        global $core;
        global $debug;
        global $db;
        global $URI;
        global $template;
        global $module;
        global $language;

        $scanPath = $conf['path']['baseDir'] . ($core->adminLoaded == true ? 'admin/' : '') . 'modules';

        foreach (glob($scanPath . '/*', GLOB_ONLYDIR) as $path) {

            $debug->write('info', 'Searching. Path is ' . $scanPath, 'search');
            $searchFilePath = $path . '/search_helper.php';

            if (file_exists($searchFilePath))
                require_once $searchFilePath;

        }
    }

    /**
     * Store the search event inside the %_search_logs table
     *
     * @param $phrase
     * @param $method
     * @param $interface
     */
    public function log($phrase, $method, $interface)
    {
        global $db;
        global $user;
        global $debug;

        $query = 'INSERT into ' . $db->prefix . 'search_logs
                    (date,
                     phrase,
                     method,
                     IP,
                     user_ID,
                     from_page,
                     results,
                     interface
                    )
                  VALUES
                    (
                    \'' . date("Y-m-d H:i:s") . '\',
                    \'' . $phrase . '\',
                    \'' . $method . '\',
                    \'' . $_SERVER['REMOTE_ADDR'] . '\',
                    ' . ($user->ID ?? 0 ) . ',
                    \'' . $_SERVER['HTTP_REFERER'] . '\',
                    \'' . $this->totalResults . '\',
                    \'' . $interface . '\'
                    )';


        if (!$db->query($query)) {
            $debug->write('error', 'Query error while inserting the search in the %_search_logs database.', 'search');
        }
    }

    /**
     * Gets the code used to build a graph.
     *
     * @param $params
     */
    public function getGraphSearches($params)
    {

    }
}
