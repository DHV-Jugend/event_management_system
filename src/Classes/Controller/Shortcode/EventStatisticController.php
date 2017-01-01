<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\Utility\General;
use DateTime;
use Ems_Date_Period;
use Ems_Event;
use Ems_Event_Registration;
use Event_Management_System;
use Fum_User;
use Point;
use VerticalBarChart;
use XYDataSet;

class EventStatisticController extends AbstractShortcodeController
{
    protected $aliases = [\Ems_Conf::PREFIX . 'event_statistic'];

    public function printContent($atts = [], $content = null)
    {
        //If user has no access, redirect to home
        if (!current_user_can(Ems_Event::get_edit_capability())) {
            wp_redirect(home_url());
            exit;
        }
        require_once(Event_Management_System::get_plugin_path() . "lib/libchart/classes/libchart.php");
        $events = Ems_Event::get_events();
        if (!empty($events)) {
            $startdate_oldest_event = null;
            $startdate_latest_event = null;
            //TODO Maybe faster if we first start at the beginning of the array and search the oldest event, then start again the end and search the latest event
            for ($i = 0; $i < count($events); $i++) {
                $start_date_time = $events[$i]->get_start_date_time();
                if (!$start_date_time instanceof DateTime) {
                    continue;
                }
                /** $start_date_time DateTime */
                //Set first event with start date as "oldest event"
                if (null == $startdate_oldest_event) {
                    $startdate_oldest_event = $events[0]->get_start_date_time();
                }
                //Set last event with start date as "latest event"
                if (null === $startdate_latest_event || $start_date_time->getTimestamp() > $startdate_latest_event->getTimestamp()) {
                    /** $startdate_latest_event DateTime */
                    $startdate_latest_event = $start_date_time;
                }
            }

            if (null !== $startdate_oldest_event && null !== $startdate_latest_event) {
                /** @var Ems_Event_Registration[] $userRegistrations */
                $userRegistrations = [];
                $start_year = date("Y", $startdate_oldest_event->getTimestamp());
                $end_year = date("Y", $startdate_latest_event->getTimestamp());

                $participant_chart = new VerticalBarChart(500, 250);
                $registration_chart = new VerticalBarChart(500, 250);
                $participant_data = new XYDataSet();
                $registration_data = new XYDataSet();
                for (; $start_year <= $end_year; $start_year++) {
                    $start = new DateTime();
                    $start->setTimestamp(strtotime("1-1-" . $start_year));
                    $end = new DateTime();
                    $end->setTimestamp(strtotime("31-12-" . $end_year));
                    $current_year_events = Ems_Event::get_events_by_start_date(new Ems_Date_Period($start, $end));
                    $users = array();
                    $registration_count = 0;
                    foreach ($current_year_events as $event) {
                        $registrations = Ems_Event_Registration::get_registrations_of_event($event->ID);
                        foreach ($registrations as $registration) {
                            $userRegistration = Ems_Event_Registration::get_registration($event->ID, $registration->get_user_id());
                            if (is_object($userRegistration)) {
                                if (!is_array($userRegistrations[$start_year])) {
                                    $userRegistrations[$start_year] = [];
                                }
                                $userRegistrations[$start_year][] = array_merge(Fum_User::get_user_data($userRegistration->get_user_id()), $userRegistration->get_data());
                            }
                            $registration_count++;
                            $users[$registration->get_user_id()] = true;
                        }
                    }
                    $participant_data->addPoint(new Point($start_year, count($users)));
                    $registration_data->addPoint(new Point($start_year, $registration_count));
                }
                $participant_chart->setDataSet($participant_data);
                $participant_chart->setTitle("Anzahl Teilnehmer pro Jahr");
                $path = "tempDownloads/participant_count_" . General::getUrlSafeUid(__FILE__) . ".png";
                $participant_chart->render(Event_Management_System::get_plugin_path() . $path);
                echo '<img src="' . Event_Management_System::get_plugin_url() . $path . '">';

                $registration_chart->setDataSet($registration_data);
                $registration_chart->setTitle("Anzahl Anmeldungen pro Jahr");
                $path = "tempDownloads/registration_count_" . General::getUrlSafeUid(__FILE__) . ".png";
                $registration_chart->render(Event_Management_System::get_plugin_path() . $path);
                echo '<img src="' . Event_Management_System::get_plugin_url() . $path . '">';

                $content = '';
                ?>
                <h2>Teilnehmer</h2>
                <?php foreach ($userRegistrations as $year => $userRegistration): ?>
                    <h3><?= $year ?></h3>
                    <label for="csv-<?= $year ?>">CSV:</label><br>
                    <?php foreach ($userRegistration as $userData): ?>
                        <?php
                        $content .= $userData['first_name'] . ',' . $userData['last_name'] . ',' . $userData['fum_postcode'];
                        $content .= ',' . $userData['fum_birthday'] . ',' . $userData['fum_state'] . ',' . $userData['fum_aircraft'];
                        if (isset($content['fum_input_field_premium_participant'])) {
                            $content .= ',' . $content['fum_input_field_premium_participant'];
                        } else {
                            $content .= ',';
                        }
                        $content .= ';'
                        ?>
                    <?php endforeach; ?>
                    <textarea id="csv-<?= $year ?>" readonly><?= $content ?></textarea>
                <?php endforeach; ?>
                <?php
            }
            return;
        }
        echo "Couldn't create statistic. No Events found.";
    }
}