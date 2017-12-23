<?php
namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Service\Event\Registration\RegistrationService;
use BIT\EMS\Settings\Tab\PagesTab;
use Ems_Event;
use Fum_Conf;
use Fum_Form_View;
use Fum_Html_Form;
use Fum_Html_Input_Field;
use Fum_User;
use Html_Input_Type_Enum;
use WP_Error;
use WP_Post;

/**
 * @author Christoph Bessei
 */
class EventRegistrationFormController extends AbstractShortcodeController
{
    const REGISTRATOM_FORM_FIELDS_FILTER = \Ems_Conf::PREFIX . 'registration_form_fields_filter';

    /**
     * @var \BIT\EMS\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    protected $eventRegistrationRepository;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->eventRegistrationRepository = new EventRegistrationRepository();
    }

    public function printContent($atts = [], $content = null)
    {
        // Check if user is logged in and show register/login link if not
        if (!is_user_logged_in()) {
            ?>
            Du musst dich einloggen, bevor du dich für ein Event anmelden kannst:<br/>
            <?php
            wp_loginout(get_permalink());
            ?>
            <br/>Du hast noch keinen Account? Registriere dich:<br/>
            <?php
            wp_register('', '');
            return;
        }
        $this->printForm();
    }

    public function validate_event_registration_form(Fum_Html_Form $form, array $params = null)
    {
        if (isset($params['error_on_input_field']) && true === $params['error_on_input_field']) {
            return new WP_Error($form->get_unique_name(), 'Das Registrierungsformular ist nicht vollständig');
        }
        return true;
    }

    protected function printForm()
    {
        $form = Fum_Html_Form::get_form(Fum_Conf::$fum_event_register_form_unique_name);

        $event_field = $form->get_input_field(Fum_Conf::$fum_input_field_select_event);

        if (isset($_REQUEST[$this->get_event_request_parameter()])) {
            $event_field->set_value($_REQUEST[$this->get_event_request_parameter()]);
            $event_field->set_readonly(true);
            //Check if event is an valid event
            $return_value = self::validate_event_select_field($event_field);
            if (is_wp_error($return_value)) {
                /** @var WP_Error $return_value */
                echo '<p><strong>' . $return_value->get_error_message() . '</strong></p>';
                echo '<p><a href="' . get_permalink() . '">Für ein anderes Event anmelden</a></p>';
                return;
            }
        } else {
            //if no event is specified, just show the select event field
            $event_field->set_name(self::get_event_request_parameter());
            $event_field->set_id(self::get_event_request_parameter());
            $form->set_input_fields([$event_field]);
            $form->set_unique_name('select_event');
            $form->add_input_field(Fum_Html_Input_Field::get_input_field(Fum_Conf::$fum_input_field_submit));
        }

        $eventID = (int)preg_replace(
            "/[^0-9]/",
            "",
            $form->get_input_field(Fum_Conf::$fum_input_field_select_event)->get_value()
        );

        if (get_post_meta($eventID, 'ems_premium_field', true)) {
            $form->insert_input_field_after_unique_name(
                Fum_Html_Input_Field::get_input_field(Fum_Conf::$fum_input_field_premium_participant),
                Fum_Conf::$fum_input_field_emergency_phone_number
            );
        }

        // TODO Add validation filter?
        if (!empty($eventID)) {
            $fields = $form->get_input_fields();
            // Generic field filter
            $fields = apply_filters(
                static::REGISTRATOM_FORM_FIELDS_FILTER,
                $fields,
                $form,
                $eventID
            );

            // Event specific filter
            $fields = apply_filters(
                static::REGISTRATOM_FORM_FIELDS_FILTER . '_' . $eventID,
                $fields,
                $form,
                $eventID
            );

            $form->set_input_fields($fields);
        }


        $form = Fum_User::fill_form($form);
        $url = $form->get_action();
        $form->set_action(add_query_arg([self::get_event_request_parameter() => $event_field->get_value()], $url));
        if ($url == '#') {
            $form->set_action(add_query_arg([self::get_event_request_parameter() => $event_field->get_value()]));
        }

        $posts = Ems_Event::get_active_events(true);
        $events = [];
        foreach ($posts as $post) {
            $event_field = $form->get_input_field(Fum_Conf::$fum_input_field_select_event);
            if ($event_field->get_readonly() && $event_field->get_value() != 'ID_' . $post->ID) {
                continue;
            }
            $title = $post->post_title;
            $events[] = ['title' => $title, 'value' => 'ID_' . $post->ID, 'ID' => $post->ID];
        }

        foreach ($form->get_input_fields() as $input_field) {
            if ($input_field->get_type() == Html_Input_Type_Enum::CHECKBOX && $input_field->get_unique_name(
                ) != Fum_Conf::$fum_input_field_accept_agb) {
                continue;
            }
            $input_field->set_required(true);
        }

        if (isset($_REQUEST[Fum_Conf::$fum_unique_name_field_name]) && $_REQUEST[Fum_Conf::$fum_unique_name_field_name] == Fum_Conf::$fum_event_register_form_unique_name) {
            $form->set_callback([$this, 'validate_event_registration_form']);
            //Check if event select field contains a valid event
            $form->get_input_field(Fum_Conf::$fum_input_field_select_event)->set_validate_callback(
                [$this, 'validate_event_select_field']
            );
            $form->set_values_from_array($_REQUEST);
            $form->validate(true);
            Fum_User::observe_object($form);
            Ems_Event::observe_object($form);
            $form->save();
            if (true === $form->get_validation_result()) {
                $registrationSuccessfulPage = get_permalink(PagesTab::get(PagesTab::EVENT_REGISTRATION_SUCCESSFUL));
                wp_safe_redirect($registrationSuccessfulPage);
                exit();
            }
        }

        $form->get_input_field(Fum_Conf::$fum_input_field_select_event)->set_possible_values($events);

        if ($form->get_input_field(Fum_Conf::$fum_input_field_select_event)->get_readonly()) {
            echo '<p><a href="' . get_permalink() . '">Für ein anderes Event anmelden</a></p>';
        }

        Fum_Form_View::output($form);

        if (!$form->get_input_field(Fum_Conf::$fum_input_field_select_event)->get_readonly()) {
            ?>
            <script type="text/javascript">
              var test = document.getElementsByName('<?php echo(isset($event_field) ? $event_field->get_name(
              ) : ''); ?>')[0];
              test.onchange = function () {
                var url = "<?php echo get_permalink() . '?' . self::get_event_request_parameter(
                    ) . '='; ?>" + this.options[this.selectedIndex].value;
                document.location.href = url;
              };
            </script>
            <?php
        }
    }

    public function validate_event_select_field(Fum_Html_Input_Field $input_field)
    {
        $posts = get_posts(
            [
                'posts_per_page' => -1,
                'post_type' => Ems_Event::get_post_type(),
            ]
        );
        $is_valid_event = false;
        $ID = null;
        /** @var WP_Post[] $posts */
        foreach ($posts as $post) {
            if ('ID_' . $post->ID == $input_field->get_value()) {
                $ID = $post->ID;
                $is_valid_event = true;
                break;
            }
        }
        if ($is_valid_event) {
            $registrationService = new RegistrationService();
            if ($registrationService->isRegistered($ID, get_current_user_id())) {
                $redirectPage = get_permalink(PagesTab::get(PagesTab::EVENT_REGISTRATION_ALREADY_REGISTERED));
                wp_safe_redirect($redirectPage);
                exit();
            } else {
                return true;

            }
        }
        return new WP_Error($input_field->get_unique_name(), 'Das ausgewählte Event existiert nicht');
    }

    protected function get_event_request_parameter()
    {
        return 'select_' . Ems_Event::get_post_type();
    }
}
