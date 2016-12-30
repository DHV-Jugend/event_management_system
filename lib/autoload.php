<?php
/**
 * @author Christoph Bessei
 * @version
 */


/* Simple autoload for generic classes/files of external libs (non composer packages) */

function autoload($class_name)
{
    //Because of sucking wordpress name conventions class name != file name, convert it manually
    $class_name = 'class-' . strtolower(str_replace('_', '-', $class_name) . '.php');
    if (file_exists(Event_Management_System::get_plugin_path() . $class_name)) {
        require_once(Event_Management_System::get_plugin_path() . $class_name);

        return;
    }

    $src_directories = ['lib/'];

    foreach ($src_directories as $dir) {
        $dir = trailingslashit($dir);
        $path = Event_Management_System::get_plugin_path() . $dir . $class_name;
        if (file_exists($path)) {
            require_once($path);

            return;
        }
    }
}