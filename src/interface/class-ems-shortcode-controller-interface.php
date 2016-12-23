<?php

/**
 * @author Christoph Bessei
 * @version
 */
interface Ems_Shortcode_Controller_Interface
{
    /**
     * @return void
     */
    public static function addShortcode();

    /**
     * @param $atts
     * @return string
     */
    public static function replaceShortcode($atts);
}