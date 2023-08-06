<?php

namespace CrisaSoft\FabCMS;

class plugin
{

    /**
     * Scans and calls plugins within $data
     *
     * @param $data
     *
     * @return string
     */
    public function parsePlugin($data)
    {
        global $language;
        global $core;
        global $conf;
        global $module;
        global $template;

        $regex = '/\{\[([a-z|_\-]{1,50}):([-_a-z0-9]{1,50})\(([ \&\=\-\,_\|\:[:alnum:]\;]{1,})?\)\]\}/isu';

        if (preg_match($regex, $data)){
            $data = preg_replace_callback($regex,
                function ($matches){

                    return $this->checkPlugin($matches[1], $matches[2], $matches[3], $matches[0]);
                }, $data
            );
        }


        return $data;
    }

    public function pluginsChainPage($page)
    {
        global $db;
        global $core;
        global $user;
        global $debug;
        global $template;
        global $language;
        $query = "SELECT * FROM {$db->prefix}plugins_chain WHERE visible = 1;";

        $db->setQuery($query);

        if (!$db->executeQuery()) {
            $debug->write('error', 'Query error while attempt "plugins chain" ' . $query . 'PLUGIN');
            return $page;
        }

        if (!$db->numRows) {
            $debug->write('Info', 'No plugin chain detected', 'PLUGIN');
            return $page;
        }

        $dbData = $db->getResultAsObject();

        while ($row = mysqli_fetch_array($dbData)) {

            $debug->write('info', 'Processing plugins chain: ' . $row['ID'] . '. Spot: ' . $row['spot']);

            // Spot exists?
            $spotTag = '<!--spot:' . $row['spot'] . '-->';
            if (false === strpos($page, $spotTag)) {
                $debug->write('Info', 'Warning, spot does not exist: ' . $row['ID'] . '. spot: ' . $row['spot']);
                continue;
            }

            switch ($row['type']) {
                case 'plugin':
                    // Obtain module and action (foo:bar, foo is the module, bar is the action)
                    $arrayMA = explode(':', $row['target']);
                    $debug->write('info', 'Processing plugins chain. Calling: ' . htmlentities($arrayMA[0]) . ', action: ' . htmlentities($arrayMA[1]));

                    if (empty($row['data'])) {
                        $row['data'] = 'null';
                    }

                    $page = str_replace($spotTag, $this->checkPlugin($core->in($arrayMA[0]), $core->in($arrayMA[1]), $core->in($row['data'])) . $spotTag, $page);
                    break;
                default:
                    $debug->write('Warning', 'Plugins chain has an unknown type' . $row['ID'] . '. Type: ' . $row['type']);
                    break;
            }
        }


        return $page;
    }

    /**
     *
     * @param $module
     * @param $pluginName
     * @param $data
     * @param $wholeString
     *
     * @return string
     */
    protected function checkPlugin($module, $pluginName, $data, $wholeString = null)
    {
        global $conf;
        global $debug;
        global $core;
        global $debug;
        global $language;
        global $template;
        global $core;
        global $relog;

        if ($core->adminLoaded == true && (int) $data['parseInAdmin'] == 0)
            return $wholeString;

        // Security check
        $module = str_replace('.', '', $module);
        $pluginName = str_replace('.', '', $pluginName);

        $debug->write('info', 'Check plugin called. Module: ' . htmlentities($module) . ', plugin name (action):' . htmlentities($pluginName));

        // Processa i dati
        $dataTemp = explode('||', $data);

        $pluginDataArray = array();
        foreach ($dataTemp as $fragment) {
            $fragmentArray = explode('==', $fragment);
            $pluginDataArray[$fragmentArray[0]] = $core->in($fragmentArray[1]);
        }
        $pluginDataArray['wholeString'] = $wholeString; //Add the full text for whole string.

        // Path
        $filePlugin = $conf['path']['baseDir'] . '/modules/' . $module . '/plugins/plg_' . $pluginName . '.php';

        // Try to load additional language
        $language->loadLang($module);

        // Check if file exists
        if (!file_exists($filePlugin)) {
            return '<div style="border: 1px solid #ff0000; padding: 2px; background-color: #ffff00;">ERROR PLG_01 ' . $filePlugin . '</div>';
        }

        // Include il plugin
        include_once $filePlugin;

        // Controlla se esiste la funzione, utile in caso di plugin malformato
        if (!function_exists('plugin_' . $pluginName)) {
            return '<div style="border: 1px solid #ff0000; padding: 2px; background-color: #ffff00;">ERROR PLG_02 (Function not found)</div>' . htmlentities($pluginName);
        }

        return call_user_func('plugin_' . $pluginName, $pluginDataArray);
    }
}