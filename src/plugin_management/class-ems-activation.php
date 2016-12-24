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
        Fum_Activation::activate_plugin();


        $admin_role = get_role('administrator');
        //TODO Because performance is not important here, maybe it would be nice if we just include all classes from the autoloader and the call get_admin_capabilities on each child of Ems_Post
        //This avoids the explicit call of each class
        $caps = array_merge(
            Ems_Event::get_admin_capabilities(),
            //TODO Implement events daily news
//            Ems_Event_Daily_News::get_admin_capabilities(),
//            Ems_Event_Daily_news::get_admin_capabilities(),
            [
                'edit_post' => true,
                'read_post' => true,
                'delete_post' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'read_private_posts' => true,
                'read' => true,
            ]
        );
        foreach ($caps as $cap => $value) {
            $admin_role->add_cap($cap);
        }

        remove_role('eventleiter');
        add_role('eventleiter', 'Eventleiter', $caps);


        $post_id = Fum_Post::add_post('Event Statistiken', 'Event Statistiken', '[' . Ems_Conf::EMS_NAME_PREFIX . 'event_statistic]');
        update_option(Ems_Conf::EMS_NAME_PREFIX . 'event_statistic_page', $post_id);

        $post_id = Fum_Post::add_post('Eventverwaltung', 'Eventverwaltung', '[' . Ems_Conf::EMS_NAME_PREFIX . 'eventverwaltung]');
        update_option(Ems_Conf::EMS_NAME_PREFIX . 'eventmanagement_page', $post_id);

        $post_id = Fum_Post::add_post('Teilnehmerlisten', 'Teilnehmerlisten', '[' . Ems_Conf::EMS_NAME_PREFIX . 'teilnehmerlisten]');
        update_option(Ems_Conf::EMS_NAME_PREFIX . 'partcipant_list_page', $post_id);

    }

} 