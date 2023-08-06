<?php

class debug
{
    // @todo: we should move this propriety to $debug but first we should use namespaces to avoid conflict with $this->debug, that matches the module object.
    private $timingStart;
    private $moduleDebug = array();
    private $type = array();
    private $data = array();

    private $timing = array();
    private $backtrace = array();

    public function __construct(){
        $this->timingStart = microtime(true);
    }
    public function getDebugData($asHtml = true)
    {
        // Query debug
        $theReturn =
            '<span class="label label-info" id="debugDebugControl" onclick="toggleState(\'debug\');">Hide debug</span>'.
            '<span class="label label-info" id="debugInfoControl" onclick="toggleState(\'info\');">Hide info</span>'.
            '<span class="label label-warning" id="debugWarningControl" onclick="toggleState(\'warning\');">Hide warning</span>'.
            '<span class="label label-danger" id="debugErrorControl" onclick="toggleState(\'error\');">Hide error</span>'.
            '<script type="text/javascript">'.
            'showStateInfo = 0;'.
            'showStateDebug = 0;'.
            'showStateWarning = 0;'.
            'showStateError = 0;'.
            'function toggleState(theType){'.
            '    switch (theType) {'.
            '        case "info":'.
            '            if (showStateInfo === 0){'.
            '                $(\'.debug-info\').hide();'.
            '                $(\'#debugInfoControl\').html("Show info");' .
            '                showStateInfo = 1;'.
            '            } else {'.
            '                $(\'.debug-info\').show();'.
            '                showStateInfo = 0;'.
            '                $(\'#debugInfoControl\').html("Hide info");' .
            '            }'.
            '            break;'.
            '        case "debug":'.
            '            if (showStateDebug === 0){'.
            '                $(\'.debug-debug\').hide();'.
            '                $(\'#debugDebugControl\').html("Show debug");' .
            '                showStateDebug = 1;'.
            '            } else {'.
            '                $(\'.debug-debug\').show();'.
            '                showStateDebug = 0;'.
            '                $(\'#debugDebugControl\').html("Hide debug");' .
            '            }'.
            '            break;'.
            '        case "warning":'.
            '            if (showStateWarning === 0){'.
            '                $(\'.debug-warning\').hide();'.
            '                $(\'#debugWarningControl\').html("Show warning");' .
            '                showStateWarning = 1;'.
            '            } else {'.
            '                $(\'.debug-warning\').show();'.
            '                showStateWarning = 0;'.
            '                $(\'#debugWarningControl\').html("Hide warning");' .
            '            }'.
            '            break;'.
            '        case "error":'.
            '            if (showStateError === 0){'.
            '                $(\'.debug-error\').hide();'.
            '                $(\'#debugErrorControl\').html("Show error");' .
            '                showStateError = 1;'.
            '            } else {'.
            '                $(\'.debug-error\').show();'.
            '                showStateError = 0;'.
            '                $(\'#debugErrorControl\').html("Hide error");' .
            '            }'.
            '            break;'.
            '    }'.
            '}'.
            '</script>'.
            '<table class="table table-striped table-condensed">' .
            '  <tr>' .
            '     <th>ID</th>' .
            '     <th>Type</th>' .
            '     <th>Timing</th>' .
            '     <th>Module</th>' .
            '     <th>Info</th>' .
            '     <th>Backtrace</th>' .
            '  </tr>' .
            '  <tbody>';

        for ($i=0; $i < count($this->data); $i++ ){

            switch($this->type){
                case 'error':
                    $theType = 'label-danger';
                    break;
                case 'warning';
                    $theType = 'label-warning';
                    break;
                case 'info':
                case 'debug':
                default:
                    $theType = 'label-info';
            }

            $theTime = '<b>'. round($this->timing[$i] - $this->timingStart, 5) . 's</b>';
            if ($i > 0) // Computes how much time passed since the last debug log
                $theTime .= '<br/>' . round( ($this->timing[$i] - $this->timingStart) - ($this->timing[$i - 1] - $this->timingStart) , 5) . 's';

            $theReturn .=
                '<tr class="debug-' . $this->type[$i] . '"> ' .
                '    <td>' . $i . '</td> ' .
                '    <td><span class="label ' . $theType . '">' . $this->type[$i] . '</span></td> ' .
                '    <td>' . $theTime . '</td> ' .
                '    <td>' . $this->moduleDebug[$i] . '</td> ' .
                '    <td>' . $this->data[$i] . '</td> ' .
                '    <td>' . $this->backtrace[$i] . '</td> ' .
                '</tr>';
        }
        $theReturn .=
            '</tbody>' .
            '</table>';

        return $theReturn;
    }

    public function write($type, $msg, $module = null)
    {
        global $conf;

        if ($conf['debug']['enabled'] !== true) {
            return;
        }

        $this->timing[] = microtime(true);

        switch ($type) {
            case 'warn':
            case 'warning':
                $this->type[] = 'warning';
                break;
            case 'debug':
                $this->type[] = 'debug';
                break;
            case 'info':
            default:
                $this->type[] = 'info';
                break;
            case 'error':
                $this->type[] = 'error';
                break;
        }

        if (!isset($module)) {
            if (isset($this->module))
                $module = $this->module;
        } else {
            $module = 'NA';
        }
        $this->moduleDebug[] = $module;

        $msg = str_replace('"', '\\"', $msg);
        $msg = str_replace("\r\n", '', $msg);
        $this->data[] = $msg;

        $theBacktrace = debug_backtrace(true);
        $this->backtrace[] = 'File: ' . $theBacktrace[0]['file'] . '<br/>' .
                             'Line: ' . $theBacktrace[0]['line'];

    }
}