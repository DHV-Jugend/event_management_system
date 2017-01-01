<?php

namespace BIT\EMS\Controller;

use Event_Management_System;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class AbstractBaseController
{
    /**
     * Add wp_enqueue_scripts a
     */
    public function enqueueAssets()
    {
        $this->addCss();
        $this->addJs();

    }

    /**
     * Called from wp_enqueue_scripts action
     */
    protected function addCss()
    {

    }

    /**
     * Called from wp_enqueue_scripts action
     */
    protected function addJs()
    {

    }

    /**
     * @param string $fileName file name without file extension
     * @return string
     */
    protected function getCssUrl($fileName)
    {
        return $this->getAssetsUrl() . "/css/" . $fileName . ".css";
    }

    /**
     * @param string $fileName file name without file extension
     * @return string
     */
    protected function getJsUrl($fileName)
    {
        return $this->getAssetsUrl() . "/js/" . $fileName . ".js";
    }

    /**
     * @return string
     */
    protected function getAssetsUrl()
    {
        return Event_Management_System::get_plugin_url() . "assets/";
    }
}