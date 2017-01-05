<?php

/**
 * Ems_Event_Report represents all news connected to an specific event
 * It's used to summarize all Ems_Event_Daily_news of an event and is not editable for users (neither in backend nor in frontend)
 * If a new event is created also a new Ems_Event_Report is created, connected to the event and with the same title
 * the_content() is the summary of all connected Ems_Event_Daily_news
 *
 * @author  Christoph Bessei
 * @version 0.04
 */
class Ems_Event_Report extends \BIT\EMS\Model\AbstractPost {
	protected static $post_type = 'ems_event_report';
	protected static $capability_type = array( 'ems_event_report', 'ems_event_reports' );
	private static $connected_event_meta_key = 'ems_connected_event';

	/**
	 * Array of connected daily news
	 * @var Ems_Event_Report[]
	 */
	private $connected_daily_news;
	/**
	 * The event connected to the report
	 * @var Ems_Event
	 */
	private $connected_event;

	public function __construct( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		$this->post            = $post;
		$this->connected_event = Ems_Event::get_event_by_id( $this->get_meta_value( self::$connected_event_meta_key ) );
	}

	/**
	 * Factory method
	 *
	 * @param int|WP_Post   $post  ID of a post or a WP_Post object
	 * @param int|Ems_Event $event ID of a event or a Ems_Event object
	 *
	 * @return Ems_Event_Report
	 */
	public static function create_instance_with_event( $post, $event ) {
		$event_report        = new self( $post );
		$event_report->event = new Ems_Event( $event );

		return $event_report;
	}

	/**
	 * Factory method
	 *
	 * @param int|WP_Post                   $post       ID of a post or a WP_Post object
	 * @param int|Ems_Event                 $event      ID of a event or a Ems_Event object
	 * @param int[]| Ems_Event_Daily_News[] $daily_news array of IDs or Ems_Event_Daily_News objects
	 *
	 * @return Ems_Event_Report
	 */
	public static function create_instance_with_event_and_daily_news( $post, $event, $daily_news ) {
		$event_report                       = self::create_instance_with_event( $post, $event );
		$event_report->connected_daily_news = array();
		foreach ( $daily_news as $daily_news_single ) {
			$event_report->connected_daily_news[] = new Ems_Event_Daily_news( $daily_news_single );
		}

		return $event_report;
	}

	public function update( Fum_Observable $o ) {

	}

	public function get_connected_event_start_date() {
		return $this->connected_event->get_start_date_time();
	}

	public function get_connected_event_end_date() {
		return $this->connected_event->get_end_date_time();
	}

	public function get_connected_event_title() {
		return $this->connected_event->post_title;
	}

	public function get_connected_event_name() {
		return $this->connected_event->post_name;
	}

	public function save_post() {
	}

	public function get_meta_value( $name ) {
		if ( $this->post instanceof WP_Post ) {
			return get_post_meta( $this->ID, $name, true );
		}

		return null;
	}

	/**
	 * Returns a meta value in a "nice" format. e.g. not the post ID but the post title, not an array but a string etc.
	 *
	 * @param string $name name of the meta value
	 *
	 * @return string print friendly string
	 */
	public function get_meta_value_printable( $name ) {

	}

	public function get_connected_event() {
		return $this->connected_event;
	}


	public static function get_custom_columns() {
	}


	public static function register_post_type() {
		/* Ems_Event_Report is a "hidden" post, it does not show up in menu or search but is queryable */
		register_post_type( self::get_post_type(),
			array(
				'labels'              => array(
					'name'          => __( 'Event Reports' ),
					'singular_name' => __( 'Event Report' )
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_nav_menus'   => false,
				'post_type'           => self::get_post_type(),
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-admin-page',
				'query_var'           => true,
				'rewrite'             => true,
				'capability_type'     => self::get_capability_type(),
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'custom_fields' ),
			)
		);
	}

	public static function get_event_reports( $limit = - 1, $sort = true, $reverse_order = false, callable $user_sort_callback = null, array $user_args = array(), Ems_Date_Period $start_period = null, Ems_Date_Period $end_period = null ) {
		$event_daily_news = Ems_Event_Daily_news::get_event_daily_news();
		$event_reports    = get_posts( array( 'post_type' => self::get_post_type(), 'post_per_page' => - 1 ) );

		//TODO Implement get_event_reports()
		return $event_reports;
	}

	public static function get_event_report_list_by_event( Ems_Event $event ) {
		//TODO Implement get_event_report_by_event( Ems_Event $event )
	}

	public static function remove_all_event_reports() {
		$reports = get_posts( array( 'post_type' => self::get_post_type(), 'post_per_page' => - 1 ) );
		foreach ( $reports as $report ) {
			if ( false === wp_delete_post( $report->ID ) ) {
				throw new Exception( 'Could not delete event report with ID: ' . $report->ID . ' from database' );
			}
		}
	}

	/**
	 * @return string
	 */
	public static function get_connected_event_report_meta_key() {
		return self::$connected_event_meta_key;
	}
} 