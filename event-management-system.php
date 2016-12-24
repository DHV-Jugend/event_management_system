<?php
/**
 * Plugin Name: Event Management System
 * Plugin URI: https://github.com/SchwarzwaldFalke/event_management_system
 * Description: Plugin which allows management (create, edit, delete, event registration, participant list, Task reminder, automated news, etc.
 * Version: 0.09
 * Author: Christoph Bessei
 * Author URI: https://www.schwarzwald-falke.de
 * License: GPL v2
 */


//Load plugin
require_once("src/event-management-system.php");
new Event_Management_System(plugin_dir_path(__FILE__), plugin_dir_url(__FILE__));

//Have to be called from main plugin file? Couldn't get it working in other places
register_activation_hook(__FILE__, array('Ems_Activation', 'activate_plugin'));
register_deactivation_hook(__FILE__, array('Ems_Deactivation', 'deactivate_plugin'));
register_uninstall_hook(__FILE__, array('Ems_Uninstallation', 'uninstall_plugin'));