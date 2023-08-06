<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2015
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace Crisasoft\FabCMS;

class core
{
    /**
     * @var loaded
     * Simple security variable, used by modules to determine if they are called directly
     * Usage: if (!$core-loaded) die();
     */
    public $loaded;
    public $adminLoaded;

    public $router;

    public $jsVar = [];
    public $multiLang;

    public $config = [];

    /**
     * @var Language code, such 'it', 'en', 'de'
     */
    public $shortCodeLang;

    public function __get($variable)
    {
        return $this->$variable;
    }

    public function __set($variable, $value)
    {
        $this->$variable = $value;
    }

    public function adminBootCheck()
    {
        global $user;

        if ($user->isAdmin === false || $this->adminLoaded === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Try to load a "core" class (/lib/fooclass/) or a module class (/modules/foobar/lib/)
     *
     * @param      $class
     * @param null $module
     *
     * @return object
     */
    public function classLoader($class, $module = null)
    {
        global $conf;
        global $debug;

        // Check if class file exists
        $fileClass = $conf['path']['baseDir'] . (!empty($module) ? 'modules/' . $module . '/' : '') . 'lib/class_' . $class . '.php';
        if (!file_exists($fileClass)) {
            $debug->write('warning', 'Unable to load class ' . $class . ' because ' . $fileClass . ' was not found', 'CORE');

            return false;
        }
        require_once $fileClass;

        $debug->write('info', 'Class file ' . $class . ' loaded', 'CORE');

        if (!class_exists($class)) {
            $debug->write('warning', 'No class found inside the file, please debug your code. FabCMS searched for:' . $class, 'CORE');

            return false;
        }

        $debug->write('info', $class . ' loaded and returned as object', 'CORE');

        return new $class;
    }

    /**
     * @return string
     * If the website is in multilang mode this function gives the initial language path, such 'it/', 'en/', 'de/'
     */
    public function multiLangPath()
    {
        global $debug;
        if (!isset($this->multiLang)) {
            $debug->write('info', 'Skipping multilanguage request, because configuration directive of multilang is set to false', 'CORE');
        } else {
            $debug->write('info', 'Ack for multilanguage request', 'CORE');

            return $this->multiLang . '/';
        }
    }

    /**
     * Handles the output for *anything* passed to the buffer. This should be the core function to prevent
     * any XSS attack.
     *
     * @param $text
     *
     * @return mixed
     */
    public function out($text)
    {
        //Stripping out any <script, <div, <link
        $regex = '/<(script|div|link|applet|embedded|base|frame|iframe|form|object|link)/i';
        $text = preg_replace($regex, '&lt;//1', $text);

        return $text;
    }

    /**
     * Provides a nice URI, usable for permalinks or trackbacks.
     * Just for example "HI!!! This is me. Ã§heers!" becomes "hi_this_is_me_cheers"
     *
     * @param $URI
     *
     * @return string
     */
    public function getTrackback($URI)
    {
        global $conf;
        global $memcache;

        if ($conf['memcache']['enabled']  === true)
            $return = $memcache->get('coreGetTracbkack-' . $URI);

        if (empty($return)) {
            $normalization = [
                'à'  => 'a',
                'è'  => 'e',
                'ì'  => 'i',
                'ò'  => 'o',
                'ù'  => 'u',
                'é'  => 'e',
                'Å ' => 'S',
                'Å ' => 'S',
                'Å¡' => 's',
                'Ã�' => 'Dj',
                'Å½' => 'Z',
                'Å¾' => 'z',
                'Ã€' => 'A',
                'Ã�' => 'A',
                'Ã‚' => 'A',
                'Ãƒ' => 'A',
                'Ã„' => 'A',
                'Ã…' => 'A',
                'Ã†' => 'A',
                'Ã‡' => 'C',
                'Ãˆ' => 'E',
                'Ã‰' => 'E',
                'ÃŠ' => 'E',
                'Ã‹' => 'E',
                'ÃŒ' => 'I',
                'Ã�' => 'I',
                'ÃŽ' => 'I',
                'Ã�' => 'I',
                'Ã‘' => 'N',
                'Ã’' => 'O',
                'Ã“' => 'O',
                'Ã”' => 'O',
                'Ã•' => 'O',
                'Ã–' => 'O',
                'Ã˜' => 'O',
                'Ã™' => 'U',
                'Ãš' => 'U',
                'Ã›' => 'U',
                'Ãœ' => 'U',
                'Ã�' => 'Y',
                'Ãž' => 'B',
                'ÃŸ' => 'Ss',
                'Ã ' => 'a',
                'Ã¡' => 'a',
                'Ã¢' => 'a',
                'Ã£' => 'a',
                'Ã¤' => 'a',
                'Ã¥' => 'a',
                'Ã¦' => 'a',
                'Ã§' => 'c',
                'Ã¨' => 'e',
                'Ã©' => 'e',
                'Ãª' => 'e',
                'Ã«' => 'e',
                'Ã¬' => 'i',
                'Ã­' => 'i',
                'Ã®' => 'i',
                'Ã¯' => 'i',
                'Ã°' => 'o',
                'Ã±' => 'n',
                'Ã²' => 'o',
                'Ã³' => 'o',
                'Ã´' => 'o',
                'Ãµ' => 'o',
                'Ã¶' => 'o',
                'Ã¸' => 'o',
                'Ã¹' => 'u',
                'Ãº' => 'u',
                'Ã»' => 'u',
                'Ã½' => 'y',
                'Ã¾' => 'b',
                'Ã¿' => 'y',
                'Æ’' => 'f',
            ];

            $return = strtr($URI, $normalization);

            // $URI = iconv('UTF-8', 'ASCII//TRANSLIT', $URI);
            // echo mb_detect_encoding($URI);
            $return = preg_replace('/[^a-z0-9\/_| -\:\(\)]/i', '', $return);
            $return = strtolower(trim($return, '-'));
            $return = preg_replace('/[\/_| \'\?\,\;\!\"]+/', '-', $return);

            $regex = "/(^-*)(.*[^-])_*$/";
            $return = preg_replace($regex, "$2", $return);

            if ($conf['memcache']['enabled'] === true)
                $memcache->set('coreGetTracbkack-' . $URI, $return, 0);

        }


        return strtolower($return);
    }

    /**
     * Returns an European formatted date from a Sql date.
     * For example 1983-06-15 -> 15-06-1983
     *
     * @param        $date
     * @param string $separator
     *
     * @return string
     */
    function convertDateFromSqlToEuropean($date, $separator = '-')
    {
        $arrayTemp = explode($separator, $date);
        $arrayTemp = array_reverse($arrayTemp);

        return $arrayTemp[0] . '-' . $arrayTemp[1] . '-' . $arrayTemp[2];
    }

    /**
     * Returns an SQL formatted date from a European date.
     * For example 15-06-1983 -> 1983-06-15
     *
     * @param        $date
     * @param string $separator
     *
     * @return string
     */
    function convertDateFromEuropeanToSql($date, $separator = '-')
    {
        $arrayTemp = explode($separator, $date);
        $arrayTemp = array_reverse($arrayTemp);

        return $arrayTemp[0] . '-' . $arrayTemp[1] . '-' . $arrayTemp[2];
    }

    function reCaptchaGetCode()
    {
        global $conf;

        return '<div class="g-recaptcha" data-sitekey="' . $this->getConfig('core', 'recaptchaPublic') . '"></div>';
    }

    function getConfig(string $module, string $param, string $type = 'value')
    {
        if (!is_array($this->config[$module][$param]))
            return false;

        switch ($type) {
            case 'value':
                return $this->config[$module][$param]['value'];
                break;
            case 'extended_value':
            default:
                return $this->config[$module][$param]['extended_value'];
                break;
        }

    }

    function reCaptchaValidateCode(string $method = "file_get_contents", $postResponse = 'g-recaptcha-response'): bool
    {
        global $core;
        global $conf;

        switch ($method) {
            case 'curl':
                $captcha_response = htmlspecialchars($_POST[$postResponse]);
                $curl = curl_init();

                $captcha_verify_url = "https://www.google.com/recaptcha/api/siteverify";

                curl_setopt($curl, CURLOPT_URL, $captcha_verify_url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, "secret=" . $core->getConfig('core', 'recaptchaPrivate') . "&response=" . $captcha_response);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $captcha_output = curl_exec($curl);
                curl_close($curl);
                $decoded_captcha = json_decode($captcha_output);
                $captcha_status = $decoded_captcha->success; // store validation result to a variable.

                if ($captcha_status === false || is_null($captcha_status)) {
                    return false;
                } else {
                    return true;
                }
                break;
            case 'file_get_contents':
            default:
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $privatekey = $core->getConfig('core', 'recaptchaPrivate');
                $response = file_get_contents($url . "?secret=" . $privatekey . "&response=" . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
                $data = json_decode($response);

                if (!isset($data->success) OR $data->success != true) {
                    return false;
                } else {
                    return true;
                }
        }

    }

    function getDirectorySize($directory)
    {
        if (!is_dir($directory))
            return -1;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        $totalSize = 0;
        foreach ($iterator as $singleFile) {
            $totalSize += $singleFile->getSize();
        }

        return $totalSize;
    }

    function loadConfig(): void
    {
        global $db;
        $query = "SELECT * 
                   FROM {$db->prefix}config 
                   WHERE lang = '{$this->shortCodeLang}' 
                         OR lang IS NULL
                         OR lang = ''";

        if (!$result = $db->query($query))
            die ("Unable to load the config." . $db->lastError);


        if (!$db->affected_rows)
            die ("Configuration is missing");

        while ($row = mysqli_fetch_array($result)) {
            $this->config[$row['module']][$row['param']]['value'] = $row['value'];
            $this->config[$row['module']][$row['param']]['extended_value'] = $row['extended_value'];
        }
    }

    function addConfig(array $values): bool
    {
        global $db;

        $module         = $this->in($values['module']);
        $param          = $this->in($values['param']);
        $value          = $this->in($values['value']);
        $extended_value = $this->in($values['extended_value']);
        $lang           = $this->in($values['lang']);

        $query = "INSERT INTO {$db->prefix}config 
                 (
                  module, 
                  param, 
                  value,
                  lang,
                  extended_value
                 )
                 VALUES
                 (
                  '$module',
                  '$param',
                  '$value',
                  '$lang',
                  '$extended_value'
                 );";


        if (!$db->query($query)) {
            echo $query;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Process the data, usually right before being sent to DB.
     *
     * @param      $text
     * @param bool $htmlEscape Set true to escape HTML tags
     *
     * @return string
     */
    public function in($text, $htmlEscape = false)
    {
        global $db;
        if ($htmlEscape === true) {
            return $db->real_escape_string(htmlentities($text));
        } else {
            return $db->real_escape_string($text);
        }
    }

    function deleteConfig(string $module)
    {
        global $db;
        $module = $this->in($module, $lang = '');

        $query = "DELETE 
                  FROM {$db->prefix}config 
                  WHERE module = '$module';";

        if (!empty($lang))
            $query .= ' AND lang = \'' . $this->in($lang, true) . ' \'';


        if (!$db->query($query))
            return -1;

        return true;
    }

    /**
     * Get a config value or extended value directly from DB
     *
     * @param string $module
     * @param string $param
     * @param        $lang
     * @param string $type
     *
     * @return mixed
     */
    function getDbConfig(string $module, string $param, $lang, string $type = 'value')
    {
        global $db;

        $module = $this->in($module, true);
        $param = $this->in($param, true);
        $lang = $this->in($lang, true);

        $query = 'SELECT value, 
                         extended_value
                  FROM ' . $db->prefix . 'config
                  WHERE module = \'' . $module . '\'
                    AND param = \'' . $param . '\'
                    AND lang = \'' . $lang . '\'
                  LIMIT 1
                  ';

        if (!$result = $db->query($query)) {
            die($query);
        }

        $row = mysqli_fetch_assoc($result);

        switch (strtolower($type)) {
            case 'extended_value':
                return $row['extended_value'];
                break;
            default:
            case 'value':
                return $row['value'];
                break;

        }
    }

    function getDateTime(?string $date): string
    {
        $dateFormat = $this->getConfig('core', 'dateTimeFormat');

        if (empty($dateFormat))
            $dateFormat = 'd-m-Y h:i:s';

        return date($dateFormat, strtotime($date));

    }

    function getDate(?string $date): string
    {
        if (empty($date))
            return '';

        $dateFormat = $this->getConfig('core', 'dateFormat');

        if (empty($dateFormat))
            $dateFormat = 'd-m-Y';

        return date($dateFormat, strtotime($date));
    }
}

