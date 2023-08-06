<?php
/**
 * Interface iFabTemplate
 */

interface iFabTemplate
{
    public function getTabs  (string $ID, array $tabsName, array $tabsContent, array $tabsConfig) : string;
    public function getPanel (string $header, string $content, string $type, bool $collapsible = false, bool $startCollapsed = false) :string;
    public function getCustomBox (array $options) :string;
    public function simpleBlock (string $title, string $content) :string;
    public function buildMenu(bool $adminSide) :string;
}