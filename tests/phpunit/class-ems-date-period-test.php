<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Date_Period_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	public function test_contains() {
		$period = new Ems_Date_Period( new DateTime( '@86435' ), new DateTime( '@100000' ) );

		// 1.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@86399' ) ) );
		// 2.1.1970 - 00:00:00
		$this->assertTrue( $period->contains( new DateTime( '@86400' ) ) );
		//2.1.1970 - 01:00:00
		$this->assertTrue( $period->contains( new DateTime( '@90000' ) ) );
		//2.1.1970 - 03:46:40
		$this->assertTrue( $period->contains( new DateTime( '@100000' ) ) );
		//2.1.1970 - 23:59:59
		$this->assertTrue( $period->contains( new DateTime( '@172799' ) ) );
		//3.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@172800' ) ) );
	}

	public function test_contains_with_time() {
		$period = new Ems_Date_Period( new DateTime( '@86435' ), new DateTime( '@100000' ) );

		// 1.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@86399' ), false ) );
		// 2.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@86400' ), false ) );
		// 2.1.1970 - 00:00:35
		$this->assertTrue( $period->contains( new DateTime( '@86435' ), false ) );
		//2.1.1970 - 01:00:00
		$this->assertTrue( $period->contains( new DateTime( '@90000' ), false ) );
		//2.1.1970 - 03:46:40
		$this->assertTrue( $period->contains( new DateTime( '@100000' ), false ) );
		//2.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@172799' ), false ) );
		//3.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@172800' ), false ) );
	}

	public function test_contains_exclude_start_date() {
		//01.01.1970 - 15:16:40 to 03.01.1970 - 00:03:20
		$period = new Ems_Date_Period( new DateTime( '@55000' ), new DateTime( '@173000' ) );
		// 1.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@0' ), true, true ) );
		// 1.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@86399' ), true, true ) );
		// 2.1.1970 - 00:00:00
		$this->assertTrue( $period->contains( new DateTime( '@86400' ), true, true ) );
		//2.1.1970 - 01:00:00
		$this->assertTrue( $period->contains( new DateTime( '@90000' ), true, true ) );
		//2.1.1970 - 03:46:40
		$this->assertTrue( $period->contains( new DateTime( '@100000' ), true, true ) );
		//2.1.1970 - 23:59:59
		$this->assertTrue( $period->contains( new DateTime( '@172799' ), true, true ) );
		//3.1.1970 - 00:00:00
		$this->assertTrue( $period->contains( new DateTime( '@172800' ), true, true ) );
		//03.01.1970 - 00:03:20
		$this->assertTrue( $period->contains( new DateTime( '@173000' ), true, true ) );
		//03.01.1970 - 00:03:21
		$this->assertTrue( $period->contains( new DateTime( '@173001' ), true, true ) );
		//04.01.1970 - 00:13:20
		$this->assertFalse( $period->contains( new DateTime( '@260000' ), true, true ) );
	}


	public function test_contains_exclude_end_date() {
		//02.01.1970 - 00:00:35 to 03.01.1970 - 00:03:20
		$period = new Ems_Date_Period( new DateTime( '@86435' ), new DateTime( '@173000' ) );

		// 1.1.1970 -00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@0' ), true, false, true ) );
		// 1.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@86399' ), true, false, true ) );
		// 2.1.1970 - 00:00:00
		$this->assertTrue( $period->contains( new DateTime( '@86400' ), true, false, true ) );
		//2.1.1970 - 01:00:00
		$this->assertTrue( $period->contains( new DateTime( '@90000' ), true, false, true ) );
		//2.1.1970 - 03:46:40
		$this->assertTrue( $period->contains( new DateTime( '@100000' ), true, false, true ) );
		//2.1.1970 - 23:59:59
		$this->assertTrue( $period->contains( new DateTime( '@172799' ), true, false, true ) );
		//3.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@172800' ), true, false, true ) );
		//03.01.1970 - 00:03:20
		$this->assertFalse( $period->contains( new DateTime( '@172800' ), true, 173000, true ) );

	}

	public function test_contains_exclude_start_and_end_date() {
		//01.01.1970 - 15:16:40 to 03.01.1970 - 00:03:20
		$period = new Ems_Date_Period( new DateTime( '@55000' ), new DateTime( '@173000' ) );
		// 1.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@0' ), true, true, true ) );
		// 1.1.1970 - 23:59:59
		$this->assertFalse( $period->contains( new DateTime( '@86399' ), true, true, true ) );
		// 2.1.1970 - 00:00:00
		$this->assertTrue( $period->contains( new DateTime( '@86400' ), true, true, true ) );
		//2.1.1970 - 01:00:00
		$this->assertTrue( $period->contains( new DateTime( '@90000' ), true, true, true ) );
		//2.1.1970 - 03:46:40
		$this->assertTrue( $period->contains( new DateTime( '@100000' ), true, true, true ) );
		//2.1.1970 - 23:59:59
		$this->assertTrue( $period->contains( new DateTime( '@172799' ), true, true, true ) );
		//3.1.1970 - 00:00:00
		$this->assertFalse( $period->contains( new DateTime( '@172800' ), true, true, true ) );
	}


}