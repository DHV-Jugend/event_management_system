<?php

namespace BIT\EMS\Controller;

use BIT\EMS\Service\PermissionService;
use Event_Management_System;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class AbstractBaseController
{
    /**
     * @var \BIT\EMS\Service\PermissionService
     */
    protected $permissionService;

    public function __construct()
    {
        $this->permissionService = new PermissionService();
    }

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
        return Event_Management_System::getAssetsBaseUrl() . "css/" . $fileName . ".css";
    }

    /**
     * @param string $fileName file name without file extension
     * @return string
     */
    protected function getJsUrl($fileName)
    {
        return Event_Management_System::getAssetsBaseUrl() . "js/" . $fileName . ".js";
    }
}