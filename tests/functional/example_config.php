<?php
/**
 * @author Christoph Bessei
 * @version
 */


//rename this file to config.php after you set all variables


require_once( __DIR__ . '/../../lib/sauce/vendor/autoload.php' );

//Only set this if you use https://saucelabs.com as your selenium server
define( 'SAUCE_USERNAME', 'My_Sauce_Username' );
define( 'SAUCE_ACCESS_KEY', 'My_Sauce_Access_Key' );
//Do you use yor own selenium server?
//This can be overwritten by setupSpecificBrowser(array('local' => false)); in your test
define( 'OWN_SELENIUM_SERVER', false );

/**
 * Class SauceWrapper
 * Small wrapper class for Sausage from SauceLabs, enables to set the 'test local' variable as a global config setting
 */
class SauceWrapper extends Sauce\Sausage\WebDriverTestCase {


	//Set the selenium server (sauce labs or local server)
	public function __construct() {

		//Setup WordPress
		$wp = new WP_UnitTestCase();
		$wp->setUp();


		if ( OWN_SELENIUM_SERVER ) {
			parent::setupSpecificBrowser( array( 'local' => OWN_SELENIUM_SERVER ) );
		}
	}


	public function setupSpecificBrowser( $params ) {
		if ( OWN_SELENIUM_SERVER && ! isset( $params['local'] ) ) {
			$params = $params + array( 'local' => OWN_SELENIUM_SERVER );
		}
		parent::setupSpecificBrowser( $params );
	}
}



