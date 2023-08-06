<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 07/08/2018
 * Time: 15:15
 */

/**
 * Interface FabCmsWiki
 */
interface FabCmsWiki
{
    public function getWikiPage(array $options) :string;
}