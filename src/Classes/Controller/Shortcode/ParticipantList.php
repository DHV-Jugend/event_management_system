<?php

namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\Controller\Base\Shortcode;
use BIT\EMS\Utility\General;
use BIT\EMS\Utility\PHPExcel\Value_Binder;
use Ems_Event;
use Ems_Event_Registration;
use Event_Management_System;
use Fum_Conf;
use Fum_Form_View;
use Fum_Html_Form;
use Fum_Html_Input_Field;
use Fum_User;
use Html_Input_Type_Enum;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Excel2007;

/**
 * Shortcode: ems_participant_list
 * @author Christoph Bessei
 * @version
 */
class ParticipantList extends Shortcode
{
    /**
     * Register shortcode alias for backward compatibility
     * @var array
     */
    protected $aliases = [\Ems_Conf::PREFIX . 'teilnehmerlisten'];

    public function printContent($atts = [], $content = null)
    {
        if (!current_user_can(Ems_Event::get_edit_capability())) {
            global $wp;
            ?>
            <p><strong>Du hast keinen Zugriff auf diese Seite.</strong></p>
            <?php
            if (!is_user_logged_in()) {
                $redirect_url = add_query_arg(array('select_event' => $_REQUEST['select_event']), get_permalink());
                ?>
                <p>
                    <a href="<?php echo wp_login_url($redirect_url); ?>">Anmelden</a>
                </p>
                <?php
            }

            return;
        }

        if (!empty($_REQUEST['ajax']) && !empty($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'deleteParticipant':
                    $eventID = intval($_REQUEST['eventID']);
                    $participantID = intval($_REQUEST['participantID']);
                    $eventRegistrations = Ems_Event_Registration::get_registrations_of_user($participantID);
                    if (is_array($eventRegistrations)) {
                        foreach ($eventRegistrations as $eventRegistration) {
                            if ($eventID === intval($eventRegistration->get_event_post_id())) {
                                Ems_Event_Registration::delete_event_registration($eventRegistration);
                                ob_clean();
                                echo 'OK';
                                exit();
                            }
                        }
                    }
                    break;
            }
            echo "FAILURE";
        }


        $events = Ems_Event::get_active_events();

        $form = new Fum_Html_Form('fum_parctipant_list_form', 'fum_participant_list_form', '#');
        $form->add_input_field(new Fum_Html_Input_Field('select_event', 'select_event', new Html_Input_Type_Enum(Html_Input_Type_Enum::SELECT), 'Eventauswahl', 'select_event', false));

        foreach ($events as $event) {

            $date_time = $event->get_start_date_time();
            $year = '';
            if (null !== $date_time) {
                $timestamp = $date_time->getTimestamp();
                $year = date('Y', $timestamp);
            }

            $title = $event->post_title . ' ' . $year . ' (' . count(Ems_Event_Registration::get_registrations_of_event($event->ID)) . ')';
            $value = 'ID_' . $event->ID;
            $possible_values = $form->get_input_field('select_event')->get_possible_values();
            $possible_values[] = array('title' => $title, 'value' => $value, 'ID' => $event->ID);
            $form->get_input_field('select_event')->set_possible_values($possible_values);
        }
        if (isset($_REQUEST[Fum_Conf::$fum_input_field_select_event])) {
            $form->get_input_field(Fum_Conf::$fum_input_field_select_event)->set_value($_REQUEST[Fum_Conf::$fum_input_field_select_event]);
        }
        $form->add_input_field(Fum_Html_Input_Field::get_input_field(Fum_Conf::$fum_input_field_submit));
        Fum_Form_View::output($form);

        //print particpant list if event selected
        if (isset($_REQUEST[Fum_Conf::$fum_input_field_select_event])) {
            $id = preg_replace("/[^0-9]/", "", $_REQUEST[Fum_Conf::$fum_input_field_select_event]);
            $registrations = Ems_Event_Registration::get_registrations_of_event($id);

            if (empty($registrations)) {
                echo '<p><strong>Bisher gibt es keine Anmeldungen für dieses Event</strong></p>';

                return;
            }

            //Create array with all relevant data
            $participant_list = array();

            foreach ($registrations as $registration) {
                $user_data = array_intersect_key(Fum_User::get_user_data($registration->get_user_id()), array_merge(Fum_Html_Form::get_form(Fum_Conf::$fum_event_register_form_unique_name)->get_unique_names_of_input_fields(), array("fum_premium_participant" => "fum_premium_participant")));
                if (empty($user_data)) {
                    continue;
                }
                unset($user_data[Fum_Conf::$fum_input_field_submit]);
                unset($user_data[Fum_Conf::$fum_input_field_accept_agb]);
                $merged_array = array_merge($user_data, $registration->get_data(), array('id' => $registration->get_user_id()));
                $participant_list[] = $merged_array;
            }

            $excel_array_private = array();
            $excel_array_public = array();

            $public_fields = array(
                "Vorname",
                "Nachname",
                "E-Mail",
                "Stadt",
                "Postleitzahl",
                "Bundesland",
                "Telefonnummer",
                "Handynummer",
                "Suche Mitfahrgelegenheit",
                "Biete Mitfahrgelgenheit",
            );


            $order = $participant_list[0];

            //Generate title row
            foreach ($order as $title => $value) {
                $field = Fum_Html_Input_Field::get_input_field($title);
                if (is_object($field)) {
                    $excel_array_private[0][] = $field->get_title();
                    if (in_array(Fum_Html_Input_Field::get_input_field($title)->get_title(), $public_fields)) {
                        $excel_array_public[0][] = Fum_Html_Input_Field::get_input_field($title)->get_title();
                    }
                }

            }

            //Generate entry rows
            foreach ($participant_list as $index => $participant) {
                foreach ($order as $title => $unused) {
                    $value = (0 === $participant[$title] ? 'Nein' : ("1" === $participant[$title] ? 'Ja' : $participant[$title]));
                    if ($title == "fum_premium_participant") {
                        $value = (empty($participant[$title]) ? 'Nein' : 'Ja');
                    }
                    //$index+1 because $index=0 is the title row
                    $excel_array_private[$index + 1][] = $value;
                    $field = Fum_Html_Input_Field::get_input_field($title);
                    if (is_object($field)) {
                        if (in_array($field->get_title(), $public_fields)) {
                            $excel_array_public[$index + 1][] = $value;
                        }
                    }
                }
            }

            //TODO Should be in view
            ?>
            <div style="overflow:auto;">
                <?php foreach ($participant_list as $participant): ?>
                    <div class="toggle-container">
                        <div
                                class="toggle-header">
                            <strong><?= $participant["first_name"] ?> <?= $participant["last_name"] ?></strong>
                            - <?= $participant["fum_city"] ?>
                            <span class="action-container"><a href="#"
                                                              data-action="deleteParticipant"
                                                              data-participant-id="<?= $participant['id'] ?>"
                                                              data-event-id="<?= $id ?>"
                                                              class="action">Löschen</a></span>
                        </div>
                        <div class="toggle-content" style="display: none;">
                            <div class="table-container">
                                <div class="table">
                                    <?php foreach ($participant as $key => $field): ?>
                                        <?php if ('id' === $key) {
                                            continue;
                                        } ?>
                                        <div class="row">
                                            <div class="col">
                                                <strong>
                                                    <?php echo Fum_Html_Input_Field::get_input_field($key)->get_title(); ?>
                                                </strong>
                                            </div>
                                            <div class="col">
                                                <?php
                                                if (1 === strlen(trim($field))) {
                                                    if (0 == $field) {
                                                        echo "Nein";
                                                    } else if (1 == $field) {
                                                        echo "Ja";
                                                    }
                                                } else {
                                                    echo $field;
                                                }

                                                ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
            //Create excel table
            $objPHPExcel = new PHPExcel();

            $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Teilnehmerliste');

            //Use customized value binder so phone numbers with leading zeros are preserved
            PHPExcel_Cell::setValueBinder(new Value_Binder());

            //Remove default worksheet named "Worksheet"
            $objPHPExcel->removeSheetByIndex(0);

            // Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
            $objPHPExcel->addSheet($myWorkSheet, 0);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->fromArray($excel_array_private);

            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

            $downloadDir = Event_Management_System::get_plugin_path() . 'tempDownloads/';
            $downloadUrl = Event_Management_System::get_plugin_url() . 'tempDownloads/';
            if (!file_exists($downloadDir)) {
                mkdir($downloadDir);
            }

            $filename = General::getUrlSafeUid($id) . "_" . $id . '.xlsx';

            $objWriter->save($downloadDir . $filename);
            echo '<p><a href="' . $downloadUrl . $filename . '">Teilnehmerliste für Eventleiter als Excelfile downloaden</a></p>';

            //Public participant list excel table
            $objPHPExcel = new PHPExcel();

            $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Teilnehmerliste');

            //Use customized value binder so phone numbers with leading zeros are preserved
            PHPExcel_Cell::setValueBinder(new Value_Binder());

            //Remove default worksheet named "Worksheet"
            $objPHPExcel->removeSheetByIndex(0);

            // Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
            $objPHPExcel->addSheet($myWorkSheet, 0);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->fromArray($excel_array_public);

            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $filename = General::getUrlSafeUid($id) . "_" . $id . '.xlsx';

            $objWriter->save($downloadDir . $filename);
            echo '<p><a href="' . $downloadUrl . $filename . '">Teilnehmerliste für Teilnehmer als Excelfile downloaden</a></p>';
        }
    }

    protected function addCss()
    {
        wp_enqueue_style('ems_participant_list', $this->getCssUrl("ems_participant_list"));
    }

    protected function addJs()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('ems_participant_list', $this->getJsUrl("ems_participant_list"));
    }
}
