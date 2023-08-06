<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 26/12/2016
 * Time: 15:08
 */

$theRegex = '#\[code\W?lang\=(.*?)\](.*?)\[\/code\]#mius';
$content = preg_replace_callback($theRegex,
    function ($theFirstMatch){
        global $template;
        global $module;

        if (!isset($this->config['highlightCalled'])) {
            $this->config['highlightCalled'] = true;
            $module->addCSSLink('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/idea.min.css', false, 'all', true);
            $module->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/highlight.min.js');
            $module->addScript('hljs.initHighlightingOnLoad();');
        }

        return '<pre><code class="' . $theFirstMatch[1] . '">' . $theFirstMatch[2] . '</code></pre>';

    }, $content);