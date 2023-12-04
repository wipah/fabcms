<?php

class db extends mysqli {
    var $prefix = 'fabcms_';

    public function __construct(?string $hostname = null, ?string $username = null, ?string $password = null, ?string $database = null, ?int $port = null, ?string $socket = null)
    {
     try {
         parent::__construct($hostname, $username, $password, $database, $port, $socket);
     } catch (Error $e){
         echo 'MySQL DOWN';
     }
    }

    public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT): mysqli_result|bool
    {
        global $relog;
        global $module;

        try {
            return parent::query($query, $result_mode);
        } catch (mysqli_sql_exception $e) {

            $trace = $e->getTrace();
            echo '<div style="border: 1px solid red; padding: 4px;">
                    <div style="text-align: center; padding:4px; border-bottom: 1px solid red;">' . $e->getMessage() . '</div>
                    <strong>File</strong>: ' . $trace[1]['file'] . ' <br/>
                    <strong>Line</strong>: ' . $trace[1]['line']  . ' <br/>
                    <pre>'. $trace[0]['args'][0]. '</pre>
                  </div>   ';

            $relog->write(['type'      => '4',
                            'module'    => $module->name,
                            'operation' => 'query_error',
                            'details'   => $e->getMessage() . '  ' . $query,
            ]);

            return false;
        }

    }

}