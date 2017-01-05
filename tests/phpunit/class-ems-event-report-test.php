<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Report_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function remove_all_event_reports_test() {
		//Add event report if there are currently no reports
		if ( 0 >= Ems_Event_Daily_News::get_event_reports() ) {

		}
		Ems_Event_Daily_News::remove_all_event_reports();
	}
}
 