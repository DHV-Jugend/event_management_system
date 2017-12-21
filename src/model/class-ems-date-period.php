<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Date_Period {
	private $start;
	private $end;

	public function __construct( \DateTime $start, \DateTime $end ) {
		$this->start = $start;
		$this->end   = $end;
	}

	/**
	 * Checks if an DatePeriod contains the DateTime
	 *
	 * @param DateTime $date               the searched DateTime
	 * @param bool     $ignore_time        ignores the time of the DateTime objects (start date begins at 00:00:00 and end date ends at 23:59:59)
	 * @param bool     $exclude_start_date exclude the start date, has only an effect if $ignore_time is true
	 * @param bool     $exclude_end_date   exclude the end date, has only an effect if $ignore_time is true
	 *
	 * @return bool
	 */
	public function contains( \DateTime $date, $ignore_time = true, $exclude_start_date = false, $exclude_end_date = false ) {
		//work with clones of $start and $end
		$start = clone $this->start;
		$end   = clone $this->end;

		if ( $ignore_time ) {
			$start->setTime( 0, 0, 0 );
			$end->setTime( 23, 59, 59 );
			if ( $exclude_start_date ) {
				$start = $start->add( DateInterval::createfromdatestring( '+1 day' ) );
			}

			if ( $exclude_end_date ) {
				$end = $end->sub( DateInterval::createfromdatestring( '+1 day' ) );
			}
		}
		return ( $start <= $date && $end >= $date );
	}

	/**
	 * @return DateTime
	 */
	public function get_end_date() {
		return $this->end;
	}

	/**
	 * @return DateTime
	 */
	public function get_start_date() {
		return $this->start;
	}

}