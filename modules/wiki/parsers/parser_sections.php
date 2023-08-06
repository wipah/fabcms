<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 08/09/2017
 * Time: 09:27
 */

$regex = '#===section:([0-9a-z\-\.\|\s]{1,50})?===(.*)?===endsection===#mis';
$content = preg_replace_callback($regex,
    function ($matches){
        return '<!--section-' . $matches[1] . '--><div class="' . $matches[1] .'">' . $matches[2] . '</div><!--endsection-' . $matches[1] . '-->';
    },$content);