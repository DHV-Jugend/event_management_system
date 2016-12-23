<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Initialisation
{
    public static function initiate_plugin()
    {
        //TODO Add Shortcode For "Register here" Link

        self::addShortCode();
        self::add_action();
        self::add_filter();

//        $role = get_role( 'eventleiter' );
//        $role->add_cap( 'upload_files' );
    }


    private static function add_filter()
    {
        if (is_admin()) {
            add_filter('manage_pages_columns', array('Ems_Initialisation', 'add_custom_column'));
            add_filter('manage_posts_columns', array('Ems_Initialisation', 'add_custom_column'));
        }

    }

    protected static function addShortCode()
    {
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'teilnehmerlisten', array(
            'Ems_Participant_List_Controller',
            'get_participant_lists'
        ));
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_list', array(
            'Ems_Event_List_Controller',
            'get_event_list'
        ));
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_report_list', array(
            'Ems_Event_Report_Controller',
            'process_event_report_list'
        ));
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_statistic', array(
            'Ems_Event_Statistic_Controller',
            'get_event_statistic'
        ));

        Ems_Event_Shortcode_Icon_Legend_Controller::addShortcode();
        Ems_Event_Shortcode_Event_Header_Controller::addShortcode();
        Ems_Event_Shortcode_Event_Registration_Link_Controller::addShortcode();
    }

    private static function add_action()
    {
        //Register plugin settings
        add_action('admin_init', array('Ems_Option_Page_Controller', 'register_settings'));
        //Create plugin admin menu page
        add_action('admin_menu', array('Ems_Option_Page_Controller', 'create_menu'));

//		//Register plugin settings
//		add_action( 'admin_init', array( 'Ems_Participant_List_Controller', 'register_settings' ) );
//		//Create plugin admin menu page
//		add_action( 'admin_menu', array( 'Ems_Participant_List_Controller', 'create_menu' ) );

        //Redirect 'event' url parameter to 'ems_event' because event seems to be reserved from wordpress
        add_action('parse_request', array('Ems_Redirect', 'redirect_event_parameter'));

        add_action('add_meta_boxes', array('Ems_Dhv_Jugend', 'add_meta_box_to_event'), 10, 2);
        add_action('add_meta_boxes', array('Ems_Dhv_Jugend', 'add_meta_box_to_event_report'), 10, 2);

        add_action('save_post', array('Ems_Initialisation', 'save_post'));

        add_action('manage_pages_custom_column', array('Ems_Initialisation', 'manage_custom_column'), 10, 2);
        add_action('manage_posts_custom_column', array('Ems_Initialisation', 'manage_custom_column'), 10, 2);

        add_action('init', array('Ems_Initialisation', 'register_custom_post_types'));
        add_action('do_meta_boxes', array('Ems_Dhv_Jugend', 'remove_metabox_layout'));
        add_action('widgets_init', create_function('', 'return register_widget("Ems_Dhv_Jugend_Widget");'));
        add_action('admin_enqueue_scripts', array('Ems_Script_Enqueue', 'admin_enqueue_script'));
        add_action('wp_enqueue_scripts', array('Ems_Script_Enqueue', 'enqueue_script'));
    }


    /**
     * Calls the save_post function of the Ems_Post interface if $post_id belongs to a post which implements this interface
     *
     * @param int $post_id ID of postmanage_posts_custom_column
     *
     * @return int ID of post
     */
    public static function save_post($post_id)
    {
        $type = get_post_type($post_id);
        $class = str_replace(' ', '_', ucwords(str_replace('_', ' ', $type)));
        if (is_subclass_of($class, 'Ems_Post')) {
            /* @var $object Ems_Post */
            $object = new $class($post_id);
            $object->save_post();
        }

        return $post_id;
    }

    public static function manage_custom_column($column, $post_id)
    {
        /** @var Ems_Post $class */
        $class = Ems_Name_Conversion::convert_post_type_to_class_name(get_post_type());
        if (is_subclass_of($class, 'Ems_Post')) {
            /** @var Ems_Post $object */
            $object = new $class($post_id);
            _e($object->get_meta_value_printable($column));
        }
    }

    public static function add_custom_column($columns)
    {
        /** @var Ems_Post $class */
        $class = Ems_Name_Conversion::convert_post_type_to_class_name(get_post_type());
        if (is_subclass_of($class, 'Ems_Post')) {
            $custom_columns = $class::get_custom_columns();
            if (count($custom_columns) > 0) {
                //TODO Make column sortable
                //add_filter( 'manage_edit-post_sortable_columns', array( $class, ) );
            }
            $columns = array_merge($columns, $custom_columns);
        }

        return $columns;
    }

    public static function register_custom_post_types()
    {
        Ems_Event::register_post_type();
        Ems_Event_Daily_News::register_post_type();
        Ems_Event_Daily_news::register_post_type();
    }

    /**
     * Calls shortcode callback_header function in wp_head, useful for add styles,scripts, change title, etc.
     *
     *
     * <p>Checks if the current post (it checks the complete WP_Post object) contains a shortcode with the EMS_NAME_PREFIX
     * If a shortcode is found it takes the callback functionname adds _header and calls it (if it's callable)</p>
     *
     * <p><b>Example:</b></p>
     * <code>add_shortcode('EMS_NAME_PREFIX_test',array('Classname','functionname'));</code>
     * then the following is called:<br>
     * <code>call_user_func(array('Classname','functionname_header'));</code>
     *
     * <code>add_shortcode('EMS_NAME_PREFIX_test','functionname');</code>
     * then the following is called:
     * <code>call_user_func('functionname_header');</code>
     */
    public static function check_shortcode()
    {
        global $shortcode_tags;
        foreach ($shortcode_tags as $shortcode_tag => $callback) {
            if (0 === stripos($shortcode_tag, Ems_Conf::EMS_NAME_PREFIX) && has_shortcode(implode(' ', get_object_vars(get_post())), $shortcode_tag)) {
                switch (count($callback)) {
                    case 1:
                        $function = (string)$callback;
                        $function = $function . '_header';
                        if (is_callable($function)) {
                            call_user_func($function);
                        }
                        break;
                    case 2:
                        $class = $callback[0];
                        $function = $callback[1] . '_header';
                        $callback = array($class, $function);
                        if (is_callable($callback)) {
                            call_user_func($callback);
                        }
                }
            }
        }
    }
} 