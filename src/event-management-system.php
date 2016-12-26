<?php

class Event_Management_System
{

    protected static $plugin_path;
    protected static $plugin_url;
    protected static $src_directories = array(
        'controller',
        '../lib',
        'model',
        'view',
        'utility',
        'plugin_management',
        '../../../../wp-includes',
        'abstract',
        'interface',
    );

    public function __construct($plugin_path = null, $plugin_url = null)
    {
        // Check if frontend_user_management is loaded
        if (class_exists("Frontend_User_Management")) {

            // Include composer autoload
            require_once(__DIR__ . '../lib/vendor/autoload.php');

            // TODO Use always composer autoload
            spl_autoload_register(array($this, 'autoload'));

            Event_Management_System::$plugin_path = plugin_dir_path(__FILE__);
            Event_Management_System::$plugin_url = plugin_dir_url(__FILE__);

            Ems_Initialisation::initiate_plugin();
        } else {
            // Normally not loaded during plugin init
            if (!function_exists('is_plugin_active')) {
                require_once(ABSPATH . '/wp-admin/includes/plugin.php');
            }
            if (is_plugin_active('frontend-user-management/frontend-user-management.php')) {
                // Wait till plugin is loaded
                add_action('frontend_user_management_plugin_loaded', function () {
                    new Event_Management_System();
                });
            } else {
                // Deactivate ems if fum isn't loaded
                deactivate_plugins(realpath(plugin_dir_path(__FILE__) . "../" . basename(__FILE__)));
                // Show error message
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>Could not load "Event management system" since "Frontend user management is not active.</p></div>';
                });
            }
        }
    }

    public function autoload($class_name)
    {

        //Because of sucking wordpress name conventions class name != file name, convert it manually
        $class_name = 'class-' . strtolower(str_replace('_', '-', $class_name) . '.php');
        if (file_exists(Event_Management_System::$plugin_path . $class_name)) {
            require_once(Event_Management_System::$plugin_path . $class_name);

            return;
        }

        foreach (self::$src_directories as $dir) {
            $dir = trailingslashit($dir);
            $path = Event_Management_System::$plugin_path . $dir . $class_name;
            if (file_exists($path)) {
                require_once($path);

                return;
            }
        }
    }

    /**
     * @return string
     */
    public static function get_plugin_path()
    {
        return self::$plugin_path;
    }

    /**
     * @return string
     */
    public static function get_plugin_url()
    {
        return self::$plugin_url;
    }

}

new Event_Management_System(); //start plugin

