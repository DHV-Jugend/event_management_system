<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Daily_news extends \BIT\EMS\Model\AbstractPost {
	protected static $post_type = 'ems_event_daily_news';
	protected static $capability_type = array( 'ems_event_daily_news', 'ems_event_daily_news' );
	private static $connected_event_meta_key = 'ems_connected_event';

	private $connected_event;

	public function __construct( $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		$this->post            = $post;
		$this->connected_event = Ems_Event::get_event_by_id( $this->get_meta_value( self::$connected_event_meta_key ) );

	}


	public function save_post() {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $this->ID;
		}


		// Check the user's permissions.
		if ( ( isset( $_REQUEST['post_type'] ) && self::get_post_type() == $_REQUEST['post_type'] ) ) {
			if ( ! current_user_can( self::get_edit_capability(), $this->ID ) ) {
				return $this->ID;
			}
		}


		//Save form options first, then date stuff
		if ( isset( $_REQUEST[ self::get_connected_event_meta_key() . '_nonce' ] ) && wp_verify_nonce( $_REQUEST[ self::get_connected_event_meta_key() . '_nonce' ], self::get_connected_event_meta_key() ) ) {
			/* OK, its safe for us to save the data now. */
			$ID = preg_replace( "/[^0-9]/", "", $_REQUEST[ self::get_connected_event_meta_key() ] );
			if ( "0" !== $ID ) {
				update_post_meta( $this->ID, self::get_connected_event_meta_key(), $ID );
			} else {
				update_post_meta( $this->ID, self::get_connected_event_meta_key(), false );
			}

		}
	}

	public function get_meta_value( $name ) {
		return get_post_meta( $this->ID, $name, true );
	}

	/**
	 * Returns a meta value in a "nice" format. e.g. not the post ID but the post title, not an array but a string etc.
	 *
	 * @param string $name name of the meta value
	 *
	 * @return string print friendly string
	 */
	public function get_meta_value_printable( $name ) {
		if ( ! $this->event instanceof Ems_Event ) {
			$this->event = Ems_Event::get_event_by_id( $this->get_meta_value( self::$connected_event_meta_key ) );
		}

		if ( $name == "ems_connected_event" ) {
			return $this->event->post_title;
		}

		if ( $name == "ems_news_number_of_connected_event" ) {
			//Get all event_reports that are connect to the same event
			$posts = get_posts( array( 'post_type'  => self::get_post_type(),
			                           'meta_key'   => self::$connected_event_meta_key,
			                           'meta_value' => $this->connected_event->ID,
			                           'order'      => 'ASC'
			) );
			foreach ( $posts as $key => $post ) {
				if ( $post->ID == $this->ID ) {
					return "#" . ( $key + 1 );
				}
			}

		}
	}

	public function update( Fum_Observable $o ) {
		// TODO: Implement update() method.
	}

	/**
	 * @return string
	 */
	public static function get_connected_event_meta_key() {
		return self::$connected_event_meta_key;
	}

	public static function register_post_type() {
//		register_post_type( self::get_post_type(),
//			array(
//				'labels'             => array( 'name'          => __( 'Tagesberichte' ),
//				                               'singular_name' => __( 'Tagesbericht' )
//				),
//				'public'             => true,
//				'publicly_queryable' => true,
//				'show_ui'            => true,
//				'post_type'          => self::get_post_type(),
//				'show_in_menu'       => true,
//				'menu_icon'          => 'dashicons-admin-page',
//				'query_var'          => true,
//				'rewrite'            => true,
//				'capability_type'    => self::get_capability_type(),
//				'has_archive'        => false,
//				'hierarchical'       => false,
//				'supports'           => array( 'title', 'editor', 'custom_fields' ),
//			)
//		);
	}

	public static function get_custom_columns() {
		return array(
			self::get_connected_event_meta_key() => __( 'zugehöriges Event' ),
			'ems_news_number_of_connected_event' => __( 'Bericht über das Event' ),
		);
	}

	public static function get_event_daily_news() {
		return get_posts( array( 'post_type' => self::get_post_type(), 'post_per_page' => - 1 ) );
	}
}