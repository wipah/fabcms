<?php
/*
 * Search and translate any "quick-html" meta-tag inside the field. With this meta-language, FabCMS, can
 * handle complex HTML (even nested!) using both HTML and RAW Editor. Code checking is beyond the scope.
 *
 * The usage is very simple:
 * [@div style="border:1px solid grey"@]
 *    This [@b@]is[@/b@] the content
 * [@/div]
 *
 * Becomes:
 *
 * <div style="border:1px solid grey">
 *    This <b>is</b> the content
 * </div>
 */
$theRegex = '#\[\@([\/]?)([a-z0-9]+)(.*?)\@\](<br \/>)?#mis';
$content = preg_replace_callback($theRegex,
    function ($matches){
        if ($matches[1] == '/'){                         // Is a closing tag?
            return '</' . $matches[2] . '>';
        }else{
            return '<' . $matches[2] . $matches[3]. '>'; // Is an opening tag?
        }
    }, $content);