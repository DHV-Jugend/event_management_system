<?php
namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Service\Event\Registration\RegistrationService;
use Ems_Event;
use Fum_Conf;
use Fum_Form_View;
use Fum_Html_Form;
use Fum_Html_Input_Field;
use Html_Input_Type_Enum;

/**
 * @author Christoph Bessei
 */
class UserEventRegistrationListController extends AbstractShortcodeController
{
    public function printContent($atts = [], $content = null)
    {
        $eventRegistrationRepository = new EventRegistrationRepository();
        $eventRegistrationService = new RegistrationService();

        if (isset($_REQUEST[Fum_Conf::$fum_unique_name_field_name])) {
            $user_id = get_current_user_id();
            $registrations = $eventRegistrationRepository->findByParticipant($user_id);

            foreach ($registrations as $registration) {
                $event_id = $registration->getEventId();
                if (isset($_REQUEST['event_' . $event_id])) {
                    $eventRegistrationService->removeByEventRegistration($registration);
                    $event_name = get_post($event_id)->post_title;
                    echo '<p><strong>Abgemeldet von: ' . $event_name . '</strong></p>';
                }
            }
        }
        $form = Fum_Html_Form::get_form(Fum_Conf::$fum_user_applied_event_form_unique_name);
        $registrations = $eventRegistrationRepository->findByParticipant(get_current_user_id());

        $event_count = 0;
        if (!empty($registrations)) {
            ob_start();
            echo '<p><strong>Angemeldete Events</strong></p>';

            $type_checkbox = new Html_Input_Type_Enum(Html_Input_Type_Enum::CHECKBOX);
            foreach ($registrations as $registration) {
                $id = $registration->getEventId();
                $event = Ems_Event::get_event($id);

                //Skip if event does not exist or is not accesible (e.g when set to private)
                if (null === $event) {
                    continue;
                }

                //Is it allowed to register/unregister for events in the past?
                if (
                    is_null($event->get_start_date_time()) ||
                    (
                        !get_option("ems_allow_event_management_past_events") &&
                        $event->get_start_date_time()->getTimestamp() < time()
                    )
                ) {
                    continue;
                }

                $name = get_post($id)->post_title;

                //TODO Prepend underscore on the id, because input field name seems not to work with numeric values
                $input_field = new Fum_Html_Input_Field($name, 'event_' . $id, $type_checkbox, $name, $id, false);
                $form->insert_input_field_before_unique_name($input_field, Fum_Conf::$fum_input_field_submit);
                $event_count++;
            }
            $form->get_input_field(Fum_Conf::$fum_input_field_submit)->set_value('Abmelden');
            Fum_Form_View::output($form);
            if ($event_count !== 0) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
        if (empty($registrations) || 0 === $event_count) {

            echo '<p><strong>Du bist für keine Events angemeldet.</strong></p>';
        }
    }
}
