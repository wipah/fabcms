<?php

if (!$core->loaded)
    die('Direct call detected. Parser.');
/*
* Search and process the inline widget. The syntax to build this widget is very simple
*

^^ First cell |-| Second cell |-| Third cell ^^

*/
$theRegex = '#\^\^(.*)?\^\^#mis';

$content = preg_replace_callback($theRegex,
    function ($theFirstMatch)
    {
        $fragments = explode('|-|', $theFirstMatch[1]);

        $return = '<div class="row"> <!--opening inline-->' . PHP_EOL;
        $i = 0;

        foreach ($fragments as $singleFragment) {

            $i++;
            $return .= '    <div class="col-sm">' . $singleFragment . '</div>'  . PHP_EOL;

            if ($i === 12) {
                $i = 0;
                $opening = true;
                $return .=  '    </div>
                             <div class="row"><!--opening middle-->'  . PHP_EOL;
            }
        }

        if ($opening === true)
            $return .=  '</div><!--closing middle-->'  . PHP_EOL;

        $return .= '</div><!--closing inline-->'  . PHP_EOL;

        return $return;
    }, $content);