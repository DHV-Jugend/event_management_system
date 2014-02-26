<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Script_Enqueue {
	/**
	 * Enqueue script on frontend page if needed
	 */
	public static function enqueue_script() {

	}

	/**
	 * Enqueue script on admin page if needed
	 */
	public static function admin_enqueue_script( $hook_suffix ) {
		$post_type = get_post_type();
		//Check if page is event editor
		if ( Ems_Event::get_event_post_type() === get_post_type() ) {
			//Load jquery, datepicker and register styles
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			$path = Event_Management_System::get_plugin_url() . 'css/jquery.ui.datepicker.min.css';
			wp_register_style( 'ems_smoothness_jquery_css', $path );

			wp_enqueue_style( 'ems_smoothness_jquery_css' );

			wp_enqueue_script( 'ems_datepicker_period', Event_Management_System::get_plugin_url() . "js/datepicker_period.js", array( 'jquery-ui-datepicker' ) );
			$localized = Ems_Javascript_Helper::get_localized_datepicker_options();
			wp_localize_script( 'ems_datepicker_period', 'objectL10n', $localized );
		}
	}
} 