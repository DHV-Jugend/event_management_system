<?php
namespace BIT\EMS\Domain\Model\Event;

use BIT\EMS\Domain\Repository\UserRepository;
use BIT\EMS\Model\Event;

/**
 * @author Christoph Bessei
 */
class EventMetaBox
{
    /**
     * @var \BIT\EMS\Domain\Repository\UserRepository
     */
    protected $userRepository;

    /* Used meta boxes */
    const METABOX_EVENT_REGISTRATION_OPTIONS = \Ems_Conf::PREFIX . 'event_registration_meta_box';
    const METABOX_CALENDAR = \Ems_Conf::PREFIX . 'calendar_meta_box';
    const METABOX_PARTICIPANT_LEVEL = \Ems_Conf::PREFIX . 'participant_level_meta_box';
    const METABOX_PARTICIPANT_TYPE = \Ems_Conf::PREFIX . 'participant_type_meta_box';

    /* Meta box fields */
    const IS_PREMIUM_EVENT = \Ems_Conf::PREFIX . 'premium_field';
    const INFORM_EVENT_MANAGER_ABOUT_NEW_PARTICIPANTS = \Ems_Conf::PREFIX . 'inform_via_mail';
    const USE_CUSTOM_PARTICIPANT_MAIL = \Ems_Conf::PREFIX . 'event_registration_use_custom_participant_mail';
    const EVENT_MANAGER = \Ems_Conf::PREFIX . 'event_leader';
    const EVENT_MANAGER_CUSTOM_MAIL = \Ems_Conf::PREFIX . 'event_leader_mail';
    const PARTICIPANT_MAIL_SUBJECT = \Ems_Conf::PREFIX . 'event_registration_participant_mail_subject';
    const PARTICIPANT_MAIL_BODY = \Ems_Conf::PREFIX . 'event_registration_participant_mail_body';
    const EVENT_START_DATE = \Ems_Conf::PREFIX . 'start_date';
    const EVENT_END_DATE = \Ems_Conf::PREFIX . 'end_date';


    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register()
    {
        $this->registerEventRegistrationMetaBox();
        $this->registerCalendarMetaBox();
    }

    public function registerLegacyMetaBox()
    {
        add_meta_box(
            'participant_level_meta_box',
            'Teilnehmerstufen',
            [
                'Ems_Dhv_Jugend',
                'add_participant_level_meta_box',
            ],
            Event::get_post_type(),
            'side'
        );

        add_meta_box(
            'add_participant_type_meta_box',
            'Erlaubte Fluggeräte',
            [
                'Ems_Dhv_Jugend',
                'add_participant_type_meta_box',
            ],
            Event::get_post_type(),
            'side'
        );
    }

    protected function registerCalendarMetaBox()
    {
        $cmb = new_cmb2_box(
            [
                'id' => static::METABOX_CALENDAR,
                'title' => __('Kalender', 'cmb2'),
                'object_types' => [Event::get_post_type()],
                'context' => 'side',
                'priority' => 'core',
            ]
        );

        // TODO Add datepicker period handling and localization

        $cmb->add_field(
            [
                'name' => __('Startdatum', 'event-management-system'),
                'id' => static::EVENT_START_DATE,
                'type' => 'text_date_timestamp',
                'date_format' => get_option('date_format'),
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Enddatum', 'event-management-system'),
                'id' => static::EVENT_END_DATE,
                'type' => 'text_date_timestamp',
                'date_format' => get_option('date_format'),
            ]
        );
    }


    protected function registerEventRegistrationMetaBox()
    {
        $cmb = new_cmb2_box(
            [
                'id' => static::METABOX_EVENT_REGISTRATION_OPTIONS,
                'title' => __('Optionen', 'cmb2'),
                'object_types' => [Event::get_post_type()],
                'context' => 'normal',
                'priority' => 'high',
                'show_names' => true,
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Premiumevent', 'event-management-system'),
                'id' => static::IS_PREMIUM_EVENT,
                'type' => 'checkbox',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Eventleiter über neue Anmeldungen informieren', 'event-management-system'),
                'id' => static::INFORM_EVENT_MANAGER_ABOUT_NEW_PARTICIPANTS,
                'type' => 'checkbox',
            ]
        );
        // Allow empty event manager, we use ems_event_leader_mail in this case
        $options = ['0' => 'Benutzdefiniert ...'];

        $eventManagers = $this->userRepository->findAllUserWithEventManagerCapabilities();
        /** @var \WP_User $eventManager */
        foreach ($eventManagers as $eventManager) {
            $options[$eventManager->ID] = $eventManager->first_name . ' ' . $eventManager->last_name . ' (' . $eventManager->nickname . ')';
        }

        $cmb->add_field(
            [
                'name' => __('Eventleiter', 'event-management-system'),
                'id' => static::EVENT_MANAGER,
                'type' => 'select',
                'options' => $options,
            ]
        );
        unset($options);

        $cmb->add_field(
            [
                'name' => __(
                    'Abweichende Eventleiter-Mailadresse',
                    'event-management-system'
                ),
                'description' => 'Wird nur benutzt, falls im Feld Eventleiter "Benutzerdefiniert" ausgewählt wird',
                'id' => static::EVENT_MANAGER_CUSTOM_MAIL,
                'type' => 'text_email',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Abweichende Bestätitungsmail für Teilnehmer', 'event-management-system'),
                'description' => ' Verfügbare Platzhalter:<br>###user_firstname### - Vorname des Teilnehmers<br>
                ###user_lastname### - Nachname des Teilnehmers<br>###event_title### - Name/Titel des Events',
                'type' => 'title',
                'id' => sha1(random_bytes(30)),
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Abweichende Bestätigungsmail benutzen', 'event-management-system'),
                'id' => static::USE_CUSTOM_PARTICIPANT_MAIL,
                'type' => 'checkbox',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Betreff', 'event-management-system'),
                'id' => static::PARTICIPANT_MAIL_SUBJECT,
                'type' => 'text',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Text', 'event-management-system'),
                'id' => static::PARTICIPANT_MAIL_BODY,
                'type' => 'wysiwyg',
                'options' => [
                    'editor_height' => 300,
                    'media_buttons' => false,
                    'quicktags' => false,
                    'tinymce' => [
                        'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink',
                    ],
                ],
            ]
        );
    }
}
