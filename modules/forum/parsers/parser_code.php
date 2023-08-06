<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 21/09/2018
 * Time: 12:01
 */

function forum_parser_code($content) {
    $theRegex = '#\[code\W?lang\=(.*?)\](.*?)\[\/code\]#mius';

    $content = preg_replace_callback($theRegex,
        function ($theFirstMatch){
            global $template;
            global $module;
            global $fabForum;


            if (!isset($fabForum->config['highlightCalled'])) {
                $fabForum->config['highlightCalled'] = true;
                $module->addCSSLink('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/androidstudio.min.css', false, 'all', true);
                $module->addJsFile('https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js');
                $module->addScript('hljs.initHighlightingOnLoad();');
            }

            return '<pre>
                    <code class="' . $theFirstMatch[1] . '">' . $theFirstMatch[2] . '</code>
                </pre>';

        }, $content);

    return $content;
}
