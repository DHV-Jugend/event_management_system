<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Uninstallation
{
    public static function uninstall_plugin()
    {
        $role = get_role('administrator');
        //TODO Because performance is not important here, maybe it would be nice if we just include all classes from the autoloader and the call get_admin_capabilities on each child of AbstractPost
        //This avoids 'dead' capabilities
        $caps = array_merge(Ems_Event::get_admin_capabilities(), Ems_Event_Daily_News::get_admin_capabilities());
        foreach ($caps as $key => $value) {
            $role->remove_cap($key);
        }
        remove_role('eventleiter');
    }
} 