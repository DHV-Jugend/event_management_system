<?php
/**
 * Plugin Name: Event Management System
 * Plugin URI: https://github.com/SchwarzwaldFalke/event_management_system
 * Description: Manage your events with WordPress (create, edit, delete, get list of participants, show some event statistics,...)
 * Version: 0.1.2
 * Text Domain: event-management-system
 * Domain Path: /languages
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPLv2
 */

// Include composer autoload
require_once(__DIR__ . '/vendor/autoload.php');

//Load plugin
require_once(__DIR__ . "/src/event-management-system.php");
new Event_Management_System(plugin_dir_path(__FILE__), plugin_dir_url(__FILE__));

//Have to be called from main plugin file? Couldn't get it working in other places
register_activation_hook(__FILE__, ['Ems_Activation', 'activate_plugin']);
register_deactivation_hook(__FILE__, ['Ems_Deactivation', 'deactivate_plugin']);
register_uninstall_hook(__FILE__, ['Ems_Uninstallation', 'uninstall_plugin']);