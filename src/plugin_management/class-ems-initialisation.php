<?php
use BIT\EMS\Controller\Shortcode\EventHeaderController;
use BIT\EMS\Controller\Shortcode\EventIconLegendController;
use BIT\EMS\Controller\Shortcode\EventListController;
use BIT\EMS\Controller\Shortcode\EventRegistrationLinkController;
use BIT\EMS\Controller\Shortcode\EventStatisticController;
use BIT\EMS\Controller\Shortcode\ParticipantListController;
use BIT\EMS\Schedule\CleanTempFilesSchedule;

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Initialisation
{

    /**
     *
     */
    public static function initPlugin()
    {
        static::registerShortcodes();
        static::addAction();
        static::addFilter();
        static::registerScheduler();
    }


    /**
     *
     */
    protected static function addFilter()
    {
        if (is_admin()) {
            add_filter('manage_pages_columns', array('Ems_Initialisation', 'add_custom_column'));
            add_filter('manage_posts_columns', array('Ems_Initialisation', 'add_custom_column'));
        }
    }

    /**
     * Register event management shortcodes
     */
    protected static function registerShortcodes()
    {
        (new ParticipantListController())->register();
        (new EventStatisticController())->register();
        (new EventListController())->register();
        (new EventHeaderController())->register();
        (new EventRegistrationLinkController())->register();
        (new EventIconLegendController())->register();
    }

    protected static function addAction()
    {
        //Register plugin settings
        add_action('admin_init', array('Ems_Option_Page_Controller', 'register_settings'));
        //Create plugin admin menu page
        add_action('admin_menu', array('Ems_Option_Page_Controller', 'create_menu'));

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
    }

    /**
     *
     */
    protected static function registerScheduler()
    {
        (new CleanTempFilesSchedule())->register();
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

    /**
     * @param $columns
     * @return array
     */
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
    }
}