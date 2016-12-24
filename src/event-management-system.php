<?php

class Event_Management_System
{

    private static $plugin_path = null;
    private static $plugin_url = null;
    private static $src_directories = array(
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
        //TODO Use composer autoload
        spl_autoload_register(array($this, 'autoload'));


        Event_Management_System::$plugin_path = plugin_dir_path(__FILE__);
        Event_Management_System::$plugin_url = plugin_dir_url(__FILE__);

        Ems_Initialisation::initiate_plugin();
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

    public static function get_plugin_path()
    {
        return self::$plugin_path;
    }

    /**
     * @return null
     */
    public static function get_plugin_url()
    {
        return self::$plugin_url;
    }

}

new Event_Management_System(); //start plugin

