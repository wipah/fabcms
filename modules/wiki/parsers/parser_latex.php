<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 23/02/2017
 * Time: 20:49
 */

$regex = '/\$\$(.*?)\$\$/miu';
if (preg_match($regex, $content)) {

    if ($this->config['parserLatexCalled'] == true)
        return;

    $this->config['parserLatexCalled'] = true;

    $theScript ='
<script type="text/x-mathjax-config">
MathJax.Hub.Config({tex2jax: {inlineMath: [[\'{eq}\',\'{/eq}\'], [\'\\(\',\'\\)\']]}});
</script>

<script type="text/javascript" async
    src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_CHTML">
</script>';

    $module->addScript($theScript);

}