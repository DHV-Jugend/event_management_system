<?php
require_once( 'config.php' );


class User_Function_Test extends SauceWrapper {

	public function setUpPage() {
//		$this->url( get_site_url() );
	}

	/**
	 * Checks if a string ($haystack) contains another string ($needle)
	 *
	 * @param string $haystack string to search in
	 * @param string $needle   searched string
	 *
	 * @return bool true if $needle was found, otherwise false
	 */
	public function string_contains( $haystack, $needle ) {
		return ( strpos( $haystack, $needle ) !== FALSE );
	}


	public function do_login( $user ) {
		$current_url = $this->url();
		$this->url( get_site_url() );
		$driver     = $this;
		$login_link = function () use ( $driver ) {
			try {
				$driver->byLinkText( 'Anmelden' );
				return true;
			} catch ( Exception $e ) {
				return false;
			}
		};
		$this->spinAssert( "Couldn't find login link", $login_link );
		$this->byLinkText( 'Anmelden' )->click();
		$this->spinAssert( "Couldn't find login formular or submit button", array( $this, 'fill_form' ), array( $user ) );
		$this->url( $current_url );
	}

	private function split_keys( $toSend ) {
		$payload = array( "value" => preg_split( "//u", $toSend, - 1, PREG_SPLIT_NO_EMPTY ) );
		return $payload;
	}

	/**
	 * Generates a random user with a unique name
	 * @return array user array with the following fields (some of them are randomly set to an empty string):<br>
	 *               1.  user_id<br>
	 *               2.  user_login<br>
	 *               3.  user_email<br>
	 *               4.  fum_birthday<br>
	 *               5.  fum_street<br>
	 *               6.  fum_city<br>
	 *               7.  fum_postcode<br>
	 *               8.  fum_state<br>
	 *               9.  fum_phone_number<br>
	 *               10. fum_mobile_number<br>
	 *
	 */
	private function get_random_user() {
		$id   = uniqid();
		$user = array(
				'user_id'           => $id,
				'user_login'        => 'fakeuser_' . $id,
				'user_email'        => $id . '@trashmail.de',
				'fum_birthday'      => $this->get_random_date(),
				'fum_street'        => 'Teststraße' . $id,
				'fum_city'          => 'City' . $this->get_random_string(),
				'fum_postcode'      => rand( 1000, 99999 ),
				'fum_state'         => 'State' . $this->get_random_string(),
				'fum_phone_number'  => rand( 10000000, 999999999 ),
				'fum_mobile_number' => rand( 10000000, 999999999 ),
		);

		//Set random values to '' (excluded user_login and user_email)
		$number_of_empty_fields = rand( 0, count( $user ) );
		$keys                   = array_keys( $user );
		for ( $i = 0; $i <= $number_of_empty_fields; $i ++ ) {
			$selected_key = $keys[rand( 0, count( $keys ) - 1 )];
			if ( $selected_key == 'user_login' || $selected_key == 'user_email' || $selected_key == 'user_id' ) {
				continue;
			}
			$user[$selected_key] = '';
		}
		return $user;
	}

	/**
	 * Generates a random string with length $length
	 * DON'T use this as a password generator! It does not create safe passwords, because it uses each character only once!
	 *
	 * @param int $length length of the generated string
	 *
	 * @return string random string
	 */
	private function get_random_string( $length = 6 ) {
		return substr( str_shuffle( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ), 0, $length );
	}

	/**
	 * Returns a random date between $start and $end
	 *
	 * @param int|null    $start  timestamp of start date
	 * @param int|null    $end    timestamp of end date
	 * @param string|null $format format of the returned date, must be a string which is compatible with date()
	 *
	 * @return string formatted date
	 */
	private function get_random_date( $start = NULL, $end = NULL, $format = NULL ) {

		if ( NULL == $format ) {
			$format = 'j.n.Y';
		}
		if ( NULL === $start ) {
			//01.01.1980
			$start = 315532800;
		}
		if ( NULL === $end ) {
			//01.01.1990
			$end = 631152000;
		}
		$timestamp = rand( $start, $end );

		return date( $format, $timestamp );
	}

	public function fill_form( $user ) {
		foreach ( $user as $key => $value ) {
			try {
				$field = $this->byId( $key );
				$field->click();
				$field->clear();
				$field->value( ( $this->split_keys( $value ) ) );
			} catch ( Exception $e ) {

			}
		}
		try {
			$this->byId( 'fum_submit' )->click();
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Tests the registration of an user
	 * 1. Register
	 * 2. Search activation mail
	 * 3. open activation link
	 * 4. login
	 * 5. check if entered user data is shown in profile
	 * @throws PHPUnit_Extensions_Selenium2TestCase_Exception
	 */
	public function test_user_registration() {
		$user = $this->get_random_user();
		$this->spinAssert( "Couldn't find 'DHV-Jugend' in title on " . get_site_url(), array( $this, 'string_contains' ), array( $this->title(), 'DHV-Jugend' ), 3 );
		$this->byLinkText( "Registrieren" )->click();
		$this->assertContains( "Registrieren", $this->title() );
		$this->fill_form( $user );

		$this->assertContains( "Registrierung erfolgreich", $this->byTag( "html" )->text() );

		//Get activation key and password from trashmail.de
		$trashmail_url = "http://www.trashmail.de/inbox-api.php?name=" . $user['user_id'];
		$content       = '';
		$mail_url      = '';
		$i             = 0;
		while ( $i < 10 && 0 === sleep( 10 ) ) {
			$content = file_get_contents( $trashmail_url );
			preg_match( '#<id>(\d+)</id>#i', $content, $matches );
			if ( count( $matches ) < 2 ) {
				$i ++;
				continue;
			}
			$mail_url = "http://www.trashmail.de/mail-api.php?name=" . $user['user_id'] . "&id=" . $matches[1];
			$content  = file_get_contents( $mail_url );
			break;
		}

		if ( empty( $mail_url ) && empty( $content ) ) {
			throw new PHPUnit_Extensions_Selenium2TestCase_Exception( "Couldn't find feed on " . "http://www.trashmail.de/inbox-api.php?name=" . $user['user_id'] . "\n" );
		}


		$password_pattern = '#Dein\s+Passwort:\s+(.*)#i';
		if ( 0 === preg_match( $password_pattern, $content, $matches ) ) {
			throw new PHPUnit_Extensions_Selenium2TestCase_Exception( "Couldn't find password in: \n" . $content . "\n FROM:\n" . $mail_url );
		}

		$user['user_password'] = $matches[1];
		$link_pattern          = '#http[^?]*\?fum_user_activation_key=(.*)#i';
		if ( 0 === preg_match( $link_pattern, $content, $matches ) ) {
			throw new PHPUnit_Extensions_Selenium2TestCase_Exception( "Couldn't find activation link in: \n" . $content . "\n FROM:\n" . $mail_url );

		}
		$link = $matches[0];
		$this->url( $link );
		$this->assertContains( "Die Aktivierung deines Accounts war erfolgreich", $this->byTag( 'html' )->text() );

		$this->url( get_site_url() );
		$driver       = $this;
		$spin_profile = function () use ( $driver, $user ) {
			try {
				$driver->byLinkText( "Profil editieren" )->click();
				$driver->byId( 'fum_birthday' )->click();
				return true;
			} catch ( Exception $e ) {
				$driver->refresh();
				try {
					$driver->byLinkText( 'Anmelden' )->click();
					$driver->fill_form( $user );
				} catch ( Exception $e ) {

				}
				return false;
			}
		};

		$this->spinAssert( "Couldn't find 'Profil editieren' or birthday field", $spin_profile );
		foreach ( $user as $key => $value ) {
			try {
				$element = $this->byId( $key );
				$this->assertTrue( $element->value() === $value );
			} catch ( Exception $e ) {
				//Catches Exception for e.g. user_id und user_name which are not shown on the profil edit page
			}
		}
	}

	public function test_login() {
		$this->do_login( parent::$admin_user );
		$this->byLinkText( 'Profil editieren' )->click();
		$this->assertContains( 'Profil', $this->title() );
	}

	public function test_event_registration() {
		$this->do_login( parent::$admin_user );

		$events = Ems_Event::get_events();
		//Check if there is at least one event
		$this->assertGreaterThan( 1, count( $events ) );
		$selected_event = $events[0];

		$event_registration_post_id = get_option( Fum_Conf::$fum_event_registration_page );
		$this->url( get_permalink( $event_registration_post_id ) );

		$this->assertContains( get_the_title( $event_registration_post_id ), $this->title() );

		//Check if select event form is loaded
		$driver                  = $this;
		$spin_assert_form_loaded = function () use ( $driver ) {
			try {
				$driver->byName( 'ems_event' );
				return true;
			} catch ( Exception $e ) {
				return false;
			}
		};
		$this->spinAssert( "Couldn't find event selection ('ems_event') formular", $spin_assert_form_loaded );
		$select         = $this->select( $this->byName( 'ems_event' ) );
		$selected_value = $select->selectedValue();
		//Avoid that we select the already selected element because this is no challenge
		if ( $selected_value == 'ID_' . $selected_event->get_post()->ID ) {
			$selected_event = $events[1];
		}
		$select->selectOptionByValue( 'ID_' . $selected_event->get_post()->ID );
		//Selection should force load via javascript, so check if the new page was loaded
		$driver            = $this;
		$search_fum_street = function () use ( $driver ) {
			try {
				$driver->byId( 'fum_street' );
				return true;
			} catch ( Exception $e ) {
				return false;
			}
		};
		$this->spinAssert( "Couldn't find 'fum_street' on page", $search_fum_street );

	}
}

?>