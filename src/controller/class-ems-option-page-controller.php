<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Option_Page_Controller
{

    public static $parent_slug = 'ems';
    /** @var  Fum_Option_Page[] $pages */
    public static $pages;

    public static function create_menu()
    {

        //Load jquery, datepicker and register styles
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-datepicker');
        $path = Event_Management_System::getAssetsBaseUrl() . 'css/ems_datepicker_ui.css';
        wp_register_style('ems_smoothness_jquery_css', $path);

        wp_enqueue_style('ems_smoothness_jquery_css');

        wp_enqueue_script(
            'ems_datepicker_period',
            Event_Management_System::getAssetsBaseUrl() . "js/ems_datepicker_period.js",
            ['jquery-ui-datepicker']
        );
        $localized = Ems_Javascript_Helper::get_localized_datepicker_options();
        wp_localize_script('ems_datepicker_period', 'objectL10n', $localized);

        /** @var Fum_Option_Page[] $pages */
        $pages = [];

        //Add General Settings Page
        $page = new Fum_Option_Page('ems_general_settings_page', 'Allgemeine Einstellungen');
        $page->addObserver(Fum_Option_Page_View::get_instance());

        //Add General Settings Fum_Option Group
        $option_group = new Fum_Option_Group('Fum_Option_Group');
        $options = [];

        //Add start date range
        $name = 'ems_start_date_period';
        $title = 'Wählen den Zeitraum aus in dem ein Event starten muss, um angezeigt zu werden<br> Von:';
        $description = '';
        $option = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'text');
        $option->set_class('datepicker_period_start');
        $options[] = $option;

        //Add end date range
        $name = 'ems_end_date_period';
        $title = 'Wählen den Zeitraum aus in dem ein Event starten muss, um angezeigt zu werden<br> Von:';
        $description = '';
        $option = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'text');
        $option->set_class('datepicker_period_end');
        $options[] = $option;

        //Add
        $name = 'ems_allow_event_management_past_events';
        $title = 'Dürfen Benutzer sich bei Events die in der Vergangenheit liegen (bereits angefangen haben) an- und abmelden?';
        $description = '';
        $option = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'checkbox');
        $options[] = $option;

        //
        $name = \Ems_Conf::PREFIX . 'event_list_upload_remote_server_host';
        $title = 'Event list upload server ';
        $description = '';

        //Add option to option_group
        $options[] = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'text');

        //Create SMTP username text field
        $name = \Ems_Conf::PREFIX . 'event_list_upload_remote_server_username';
        $title = 'Event list upload username';
        $description = '';

        //Add option to option_group
        $options[] = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'text');

        //Create SMTP password text field
        $name = \Ems_Conf::PREFIX . 'event_list_upload_remote_server_password';
        $title = 'Event list upload password';
        $description = '';

        $options[] = new Fum_Option($name, $title, $description, get_option($name), $option_group, 'password');

        //Add created options to $option_group and register $option_group
        $option_group->set_options($options);

        //Add all option groups to page
        $page->add_option_group($option_group);


        //Add page to page array
        $pages[] = $page;


        self::$pages = $pages;

        //Add main menu
        add_menu_page(
            'Event Management System',
            'Event Management System',
            'manage_options',
            self::$parent_slug,
            [$page, 'notifyObservers']
        );
        //Add first submenu to avoid duplicate entries: http://wordpress.org/support/topic/top-level-menu-duplicated-as-submenu-in-admin-section-plugin
        add_submenu_page(
            self::$parent_slug,
            $pages[0]->get_title(),
            self::$pages[0]->get_title(),
            'manage_options',
            self::$parent_slug
        );
        //remove first submenu because we used this already
        unset($pages[0]);

        foreach ($pages as $page) {

            add_submenu_page(
                self::$parent_slug,
                $page->get_title(),
                $page->get_title(),
                'manage_options',
                $page->get_name(),
                [$page, 'notifyObservers']
            );
        }
    }


    public static function register_settings()
    {
        $pages = self::$pages;
        for ($i = 0; $i < count($pages); $i++) {
            $page = $pages[$i];
            foreach ($page->get_option_groups() as $option_group) {
                foreach ($option_group->get_options() as $option) {
                    register_setting($option_group->get_name(), $option->get_name());
                }
            }
        }
    }
}