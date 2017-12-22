<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Activation
{
    public static function activate_plugin()
    {
        //Setup frontend user management first (must be called here, because a second 'register_activation_hook' didn't work
        //TODO Still needed?
        Fum_Activation::activate_plugin();

        // Create tables
        (new\BIT\EMS\Log\EventRegistrationLog())->createTable();

        $admin_role = get_role('administrator');
        //TODO Because performance is not important here, maybe it would be nice if we just include all classes from the autoloader and the call get_admin_capabilities on each child of AbstractPost
        //This avoids the explicit call of each class
        $caps = array_merge(
            Ems_Event::get_admin_capabilities(),
            //TODO Implement event daily news?
            [
                'edit_post' => true,
                'read_post' => true,
                'delete_post' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'read_private_posts' => true,
                'read' => true,
                'upload_files' => true,
            ]
        );
        foreach ($caps as $cap => $value) {
            $admin_role->add_cap($cap);
        }

        remove_role(Ems_Conf::EVENT_MANAGER_ROLE);
        add_role(Ems_Conf::EVENT_MANAGER_ROLE, 'Eventleiter', $caps);
    }

} 