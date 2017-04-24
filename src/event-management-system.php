<?php

class Event_Management_System
{

    /**
     * @var string
     */
    protected static $plugin_path;

    /**
     * @var string
     */
    protected static $plugin_url;

    /**
     * @var array
     */
    protected static $src_directories = [
        'src/',
        'src/controller',
        'src/model',
        'src/view',
        'src/utility',
        'src/plugin_management',
        '../../../wp-includes',
        'src/abstract',
        'src/interface',
    ];

    public function __construct($plugin_path = null, $plugin_url = null)
    {
        if (!is_null($plugin_path)) {
            Event_Management_System::$plugin_path = $plugin_path;
        }
        if (!is_null($plugin_url)) {
            Event_Management_System::$plugin_url = $plugin_url;
        }

        // Check if frontend_user_management is loaded
        if (class_exists("Frontend_User_Management")) {
            // Include composer autoload
            require_once(static::getPluginPath() . 'vendor/autoload.php');

            // TODO Use always composer autoload
            spl_autoload_register(['Event_Management_System', 'autoload']);

            Ems_Initialisation::initPlugin();
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
                    echo '<div class="error"><p>Could not load "Event management system" since "Frontend user management" is not active.</p></div>';
                });
            }
        }
    }

    public static function autoload($class_name)
    {
        //Because of sucking wordpress name conventions class name != file name, convert it manually
        $class_name = 'class-' . strtolower(str_replace('_', '-', $class_name) . '.php');
        if (file_exists(Event_Management_System::getPluginPath() . $class_name)) {
            require_once(Event_Management_System::getPluginPath() . $class_name);

            return;
        }

        foreach (self::$src_directories as $dir) {
            $dir = trailingslashit($dir);
            $path = Event_Management_System::getPluginPath() . $dir . $class_name;
            if (file_exists($path)) {
                require_once($path);

                return;
            }
        }
    }

    /**
     * @return string
     */
    public static function getPluginPath()
    {
        return static::$plugin_path;
    }

    /**
     * @return string
     */
    public static function getPluginUrl()
    {
        return static::$plugin_url;
    }

    /**
     *
     * @return string
     */
    public static function getAssetsBaseUrl()
    {
        return static::getPluginUrl() . "assets/dist/";
    }

    /**
     * @return string
     */
    public static function getAssetsBasePath()
    {
        return static::getPluginPath() . "assets/dist/";
    }
}
