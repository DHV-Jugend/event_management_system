<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Participant_List_Controller
{
    public static $parent_slug = 'ems_participant_list';
    /** @var  Fum_Option_Page[] $pages */
    public static $pages;

    public static function get_participant_lists()
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

            /**
             *
             *             <div style="overflow:auto;">
             * <table>
             * <thead>
             * <tr>
             * <?php foreach ($order as $title => $value): ?>
             * <th><?php echo Fum_Html_Input_Field::get_input_field($title)->get_title(); ?></th>
             * <?php endforeach; ?>
             * </tr>
             *
             * </thead>
             * <tbody>
             * <?php foreach ($participant_list as $participant): ?>
             * <tr>
             * <?php foreach ($order as $title => $unused): ?>
             * <td><?php echo(0 === $participant[$title] ? 'Nein' : ("1" === $participant[$title] ? 'Ja' : $participant[$title])); ?></td>
             * <?php endforeach; ?>
             * </tr>
             * <?php endforeach; ?>
             * </tbody>
             * </table>
             * </div>
             */

            //TODO Should be in view
            //Print html table

            $dropDownUrl = Event_Management_System::get_plugin_url() . "images/drop_down.png";
            $dropUpUrl = Event_Management_System::get_plugin_url() . "images/drop_up.png";

            ?>

            <style type="text/css">

                .toggle-container .toggle-header {
                    background-color: #efefef;
                    border-bottom: 1px solid #aaa;
                    cursor: pointer;
                    padding: 15px 15px 15px 30px;
                    position: relative;
                }

                .toggle-container .toggle-header:before {
                    content: ' ';
                    position: absolute;
                    width: 30px;
                    height: 30px;
                    display: inline-block;
                    background-size: contain;
                    left: 0;
                    top: 12px;
                    background-image: url('<?=$dropDownUrl?>');
                }

                .toggle-container.active .toggle-header:before {
                    background-image: url('<?=$dropUpUrl?>');
                }

                .toggle-container:last-of-type .toggle-header {
                    border-bottom: none;
                }

                .toggle-container .toggle-content {
                    padding-left: 45px;
                    margin-bottom: 15px;
                    margin-top: 5px;
                }

                .toggle-container:last-of-type .toggle-content {
                    margin-bottom: 0;
                }

                .toggle-container .action-container {
                    float: right;
                }

                .table-container .table {
                    display: table;
                }

                .table-container .table .row {
                    display: table-row;
                    margin-bottom: 15px;
                }

                .table-container .table .row:last-of-type {
                    margin-bottom: 0;
                }

                .table-container .table .row .col {
                    display: table-cell;
                    padding: 0 5px;
                }

                @media (max-width: 700px) {
                    .toggle-container .action-container {
                        display: block;
                        float: none;
                    }

                    .table-container .table {
                        display: block;
                    }

                    .table-container .table .row {
                        display: block;
                        margin-bottom:;: 5 px;
                    }

                    .table-container .table .row:last-of-type {
                        margin-bottom: 0;
                    }

                    .table-container .table .row .col {
                        display: block;
                    }
                }

            </style>


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

            <script>
                jQuery(".toggle-header").on('click', function (e) {
                    if (jQuery(e.target).is('a')) {
                        return;
                    }
                    var $container = jQuery(this).closest(".toggle-container");
                    var $content = $container.find(".toggle-content");
                    $content.slideToggle(200, function () {
                        $container.toggleClass('active');
                    });
                });

                jQuery('.action').on('click', function (e) {
                    e.preventDefault();

                    var r = confirm("Wirklich löschen?");
                    if (r == true) {
                        var self = this;

                        var eventID = jQuery(this).data('event-id');
                        var participantID = jQuery(this).data('participant-id');
                        var action = jQuery(this).data('action');
                        var data = {ajax: 'true', eventID: eventID, participantID: participantID, action: action};
                        jQuery.ajax({
                            type: "POST",
                            data: data,

                            success: function (data) {
                                if ('OK' == jQuery.trim(data)) {
                                    alert("Gelöscht");
                                    self.closest(".toggle-container").remove();
                                } else {
                                    alert("Fehlgeschlagen.");
                                }
                            }
                        });
                    }
                });
            </script>
            <?php
            //Create excel table
            $objPHPExcel = new PHPExcel();

            $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Teilnehmerliste');

            //Use customized value binder so phone numbers with leading zeros are preserved
            PHPExcel_Cell::setValueBinder(new PHPExcel_Value_Binder());

            //Remove default worksheet named "Worksheet"
            $objPHPExcel->removeSheetByIndex(0);

            // Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
            $objPHPExcel->addSheet($myWorkSheet, 0);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->fromArray($excel_array_private);

            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

            // TODO This is not cryptographic save. Replace with dyamic file download with php access check.
            $filename = sha1(uniqid('participant_list', true)) . "_" . $id . '.xlsx';
            $objWriter->save(Event_Management_System::get_plugin_path() . $filename);
            echo '<p><a href="' . Event_Management_System::get_plugin_url() . $filename . '">Teilnehmerliste für Eventleiter als Excelfile downloaden</a></p>';

            //Public participant list excel table
            $objPHPExcel = new PHPExcel();

            $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Teilnehmerliste');

            //Use customized value binder so phone numbers with leading zeros are preserved
            PHPExcel_Cell::setValueBinder(new PHPExcel_Value_Binder());

            //Remove default worksheet named "Worksheet"
            $objPHPExcel->removeSheetByIndex(0);

            // Attach the "My Data" worksheet as the first worksheet in the PHPExcel object
            $objPHPExcel->addSheet($myWorkSheet, 0);
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->fromArray($excel_array_public);

            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $filename = $id . "_public" . '.xlsx';
            $objWriter->save(Event_Management_System::get_plugin_path() . $filename);
            echo '<p><a href="' . Event_Management_System::get_plugin_url() . $filename . '">Teilnehmerliste für Teilnehmer als Excelfile downloaden</a></p>';
        }
    }
}
