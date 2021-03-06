<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\PluginManagement;


use BIT\EMS\Controller\Shortcode\EventHeaderController;
use BIT\EMS\Controller\Shortcode\EventIconLegendController;
use BIT\EMS\Controller\Shortcode\EventListController;
use BIT\EMS\Controller\Shortcode\EventRegistrationFormController;
use BIT\EMS\Controller\Shortcode\EventRegistrationLinkController;
use BIT\EMS\Controller\Shortcode\EventStatisticController;
use BIT\EMS\Controller\Shortcode\ParticipantListController;
use BIT\EMS\Controller\Shortcode\UserEventRegistrationListController;
use BIT\EMS\Domain\Model\Event\EventMetaBox;
use BIT\EMS\Migration\Migration;
use BIT\EMS\Model\AbstractPost;
use BIT\EMS\Schedule\CleanTempFilesSchedule;
use BIT\EMS\Settings\Settings;
use Ems_Event;
use Ems_Event_Daily_news;
use Ems_Name_Conversion;

class Initialisation
{
    public static function run()
    {
        static::executeMigrations();
        static::registerShortcodes();
        static::addAction();
        static::addFilter();
        static::registerScheduler();
        static::registerSettings();
    }


    protected static function executeMigrations()
    {
        if (is_admin() && !wp_doing_ajax()) {
            Migration::run();
        }
    }

    /**
     * Register backend settings page / options page
     */
    protected static function registerSettings()
    {
        if (is_admin()) {
            Settings::register();
        }
    }

    /**
     *
     */
    protected static function addFilter()
    {
        if (is_admin()) {
            add_filter('manage_pages_columns', [static::class, 'add_custom_column']);
            add_filter('manage_posts_columns', [static::class, 'add_custom_column']);
        }
    }

    /**
     * Register shortcodes
     */
    protected static function registerShortcodes()
    {
        (new ParticipantListController())->register();
        (new EventStatisticController())->register();
        (new EventListController())->register();
        (new EventHeaderController())->register();
        (new EventRegistrationLinkController())->register();
        (new EventIconLegendController())->register();
        (new UserEventRegistrationListController())->register();
        (new EventRegistrationFormController())->register();
    }

    /**
     *
     */
    protected static function addAction()
    {
        add_action(
            'plugins_loaded',
            function () {
                load_plugin_textdomain('event-management-system', false, 'event-management-system/languages');
            }
        );

        //Redirect 'event' url parameter to 'ems_event' because event seems to be reserved from wordpress
        add_action('parse_request', ['Ems_Redirect', 'redirect_event_parameter']);

        $eventMetaBox = new EventMetaBox();
        add_action('add_meta_boxes', [$eventMetaBox, 'registerLegacyMetaBox'], 10, 2);
        add_action( 'cmb2_admin_init', [$eventMetaBox,'register'] );

        add_action('save_post', [static::class, 'save_post']);

        add_action('manage_pages_custom_column', [static::class, 'manage_custom_column'], 10, 2);
        add_action('manage_posts_custom_column', [static::class, 'manage_custom_column'], 10, 2);

        add_action('init', [static::class, 'register_custom_post_types']);
        add_action('do_meta_boxes', ['Ems_Dhv_Jugend', 'remove_metabox_layout']);
        add_action('widgets_init', create_function('', 'return register_widget("Ems_Dhv_Jugend_Widget");'));
        add_action('admin_enqueue_scripts', ['Ems_Script_Enqueue', 'admin_enqueue_script']);

        (new ParticipantListController())->addAction();
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
        if (is_subclass_of($class, AbstractPost::class)) {
            /** @var $object AbstractPost */
            $object = new $class($post_id);
            $object->save_post();
        }

        return $post_id;
    }

    public static function manage_custom_column($column, $post_id)
    {
        /** @var AbstractPost $class */
        $class = Ems_Name_Conversion::convert_post_type_to_class_name(get_post_type());
        if (is_subclass_of($class, AbstractPost::class)) {
            /** @var AbstractPost $object */
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
        /** @var AbstractPost $class */
        $class = Ems_Name_Conversion::convert_post_type_to_class_name(get_post_type());
        if (is_subclass_of($class, AbstractPost::class)) {
            $custom_columns = $class::get_custom_columns();
            $columns = array_merge($columns, $custom_columns);
        }

        return $columns;
    }

    public static function register_custom_post_types()
    {
        Ems_Event::register_post_type();
    }
}