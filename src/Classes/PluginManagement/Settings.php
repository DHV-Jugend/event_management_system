<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\PluginManagement;

use BIT\WpSettings\InputFields\Checkbox;

class Settings {
	/**
	 * @var \BIT\EMS\PluginManagement\Settings
	 */
	protected static $settings;

	public static function register() {
		// Register only once
		if ( is_null( static::$settings ) ) {
			static::$settings = new Settings();
			add_action( 'admin_init', [ static::$settings, 'configure' ] );
			add_action( 'admin_menu', [ static::$settings, 'addOptionsPage' ] );
		}
	}


	public static function getOption( $option, $default = null ) {

	}

	public function showForm() {
	}

	public function addOptionsPage() {

	}

	public function configure() {
		// First, we register a section. This is necessary since all future options must belong to one.
		add_settings_section(
			'general_settings_section',         // ID used to identify this section and with which to register options
			'Sandbox Options',                  // Title to be displayed on the administration page
			[ $this, 'sectionCallback' ], // Callback used to render the description of the section
			'general'                           // Page on which to add this section of options
		);

		( new Checkbox( 'show_header', 'Header', 'general', 'general_settings_section' ) )->configure();
	}

	public function sectionCallback() {
		echo '<p>Select which areas of content you wish to display.</p>';
	}

	/* ------------------------------------------------------------------------ *
 * Field Callbacks
 * ------------------------------------------------------------------------ */

	/**
	 * This function renders the interface elements for toggling the visibility of the header element.
	 *
	 * It accepts an array of arguments and expects the first element in the array to be the description
	 * to be displayed next to the checkbox.
	 */
	public function sandbox_toggle_header_callback( $args ) {

		// Note the ID and the name attribute of the element match that of the ID in the call to add_settings_field
		$html = '<input type="checkbox" id="show_header" name="show_header" value="1" ' . checked( 1, get_option( 'show_header' ), false ) . '/>';

		// Here, we will take the first argument of the array and add it to a label next to the checkbox
		$html .= '<label for="show_header"> ' . $args[0] . '</label>';

		echo $html;

	} // end sandbox_toggle_header_callback
}