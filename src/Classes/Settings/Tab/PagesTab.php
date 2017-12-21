<?php
namespace BIT\EMS\Settings\Tab;

use C3\WpSettings\Tab\AbstractTab;

/**
 * @author Christoph Bessei
 */
class PagesTab extends AbstractTab
{
    const EVENT_LIST = \Ems_Conf::PREFIX . 'event_list';
    const EVENT_REGISTRATION_FORM = \Ems_Conf::PREFIX . 'event_registration_form';

    const EVENT_PARTICIPANTS_LIST = \Ems_Conf::PREFIX . 'event_participants_list';
    const EVENT_STATISTICS = \Ems_Conf::PREFIX . 'event_statistics';
    const USER_REGISTRATIONS_LIST = \Ems_Conf::PREFIX . 'user_registrations_list';


    public function getId(): string
    {
        return \Ems_Conf::PREFIX . 'pages';
    }

    public function getTitle(): string
    {
        return __('Pages', 'ems_text_domain');
    }

    public function getFields(): array
    {
        return [
            [
                'name' => static::EVENT_LIST,
                'label' => __('Event list', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_REGISTRATION_FORM,
                'label' => __('Event registration form', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_PARTICIPANTS_LIST,
                'label' => __('Event participants list', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_STATISTICS,
                'label' => __('Event statistics', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::USER_REGISTRATIONS_LIST,
                'label' => __('Event registrations list of current user', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}
