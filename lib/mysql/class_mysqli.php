<?php

namespace CrisaSoft\FabCMS;

class dbi extends \mysqli
{
    public $username;
    public $password;
    public $hostname;
    public $dbname;
    public $prefix;
    public $port = 3306;

    public $queryData;
    public $numRows;

    public $lastInsertID;
    public $lastError;

    public $queryCount;

    public $linkID;
    public $result;

    public $debugQuery = [];
    public $debugTime = [];
    public $debugRows = [];
    public $debugStatus = [];
    public $debugBackTrace = [];

    public function __construct($host, $user, $pass, $db, $port = 3306)
    {
        $this->linkID = mysqli_connect($host, $user, $pass, $db, $port);

        if (mysqli_connect_error()) {
            die('Connect Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
        }
    }

    function setQuery($query)
    {
        $this->queryData = $query;
    }

    function executeQuery($type = 'select')
    {
        global $debug;
        global $conf;

        if ($conf['debug']['enabled'] === true) {
            $startTime = microtime(true);
            $this->debugQuery[] = $this->queryData;
        }

        if (!$this->result = mysqli_query($this->linkID, $this->queryData)) {
            $this->lastError = mysqli_error($this->linkID);

            // We have got an error. Debug time! :)
            $this->debugTime[] = round(microtime(true) - $startTime, 4);
            $this->debugRows[] = 0;
            $this->debugStatus[] = 'ERROR: ' . $this->lastError;

            $debug->write('warn', 'Query error in :' . $this->lastError, 'MYSQL');

            return false;
        }

        $this->queryCount++;

        switch (strtolower($type)) {
            case 'select' :
                $this->numRows = mysqli_num_rows($this->result);
                break;
            case 'insert' :
                $this->lastInsertID = mysqli_insert_id($this->linkID);
                $this->numRows = mysqli_affected_rows($this->linkID);
                break;
            default :
                @$this->lastInsertID = mysqli_insert_id($this->linkID);;
                $this->numRows = mysqli_affected_rows($this->linkID);
                break;
        }

        if ($conf['debug']['enabled'] === true) {
            $this->debugStatus[] = 'OK';
            $this->debugTime[] = round(microtime(true) - $startTime, 4);
            $this->debugRows[] = $this->numRows;

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
            $this->debugBackTrace[] = 'File: ' . $backtrace[0]['file'] . '<br/>' .
                'Line: ' . $backtrace[0]['line'] . '<br/>';
        }

        return $this->result;
    }

    function getResultAsArray($type = MYSQLI_BOTH)
    {
        return mysqli_fetch_array($this->result, $type);
    }

    /** Returns the result stored in $db->result;
     * @return MySQL result
     */
    function getResultAsObject()
    {
        return $this->result;
    }

    function buildExtendedQuery($term, $field, $separator = " ", $operator = "AND", $sqlcheck = false, $minChar = 3)
    {
        $term_array = explode($separator, $term);
        $query = "";

        foreach ($term_array as $word) {
            if (strlen($word) < $minChar)
                continue;

            if ($sqlcheck === true) {
                $word = mysqli_real_escape_string($this->linkID, $word);
            }
            $query .= " $field LIKE '%$word%' $operator \r";
        }

        $query = substr($query, 0, -5);

        return $query;
    }
}