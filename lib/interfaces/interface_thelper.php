<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 19/06/14
 * Time: 2.02
 */

interface interface_thelper{
    /**
     * @param $ID     .
     * @param $config Array of configuration values
     *                'interval'    -> interval in ms;
     *                'width'       -> width of the widget
     *                'height'      -> height of the widget
     *                'header'      -> Header text
     *                'description' -> The description
     * @param $data Array of items
     *              'src'           -> the source of the image
     *              'alt'           -> alt text
     *
     * @return mixed
     */
    public function getCarousel ($ID, $config, $data);

    /**
     * @param $header  array The header of the panel.
     * @param $content array The content of the panel.
     * @param $type    array of panel: info, success, warning, danger, light, dark.
     * @param $collapsible True if box is collapsible.
     *
     * @return mixed
     */
    public function getPanel ($header, $content, $type, $collapsible);

    /**
     * @param $ID mixed The ID of the widget. Use -1 to generate a random ID each time the widget is called.
     * @param $tabs  array The heading of the tabse.
     * @param $contents array The contents of the tab.
     * @param $config array Configuration of the widget.
     *                      'tabType' => Accepted values are 'light' and 'normal';
     *                      'tabCustomStyle' => Custom style of the widget;
     *
     * @return mixed
     */
    public function getTabs ($ID, $tabs, $contents, $config);
}