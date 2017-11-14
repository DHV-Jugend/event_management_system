<?php
namespace BIT\EMS\Settings\Tab;

/**
 * @author Christoph Bessei
 */
class BasicTab implements TabInterface
{
    const DEFAULT_EVENT_PAGE = \Ems_Conf::PREFIX . 'default_event_page';
    const EVENT_START_DATE = \Ems_Conf::PREFIX . 'event_start_date';
    const EVENT_END_DATE = \Ems_Conf::PREFIX . 'event_end_date';

    const EVENT_ALLOW_REGISTRATION_UNTIL = \Ems_Conf::PREFIX . 'allow_registration_until';
    const EVENT_ALLOW_CANCEL_REGISTRATION_UNTIL = \Ems_Conf::PREFIX . 'allow_cancel_registration_until';


    public function getId(): string
    {
        return \Ems_Conf::PREFIX . 'basics';
    }

    public function getTitle(): string
    {
        return __('Basic Settings', 'ems_text_domain');
    }

    public function getFields(): array
    {
        return [
            [
                'name' => static::DEFAULT_EVENT_PAGE,
                'label' => __('Default event page', 'ems_text_domain'),
                'type' => 'pages',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_START_DATE,
                'label' => __('Active events period<br>Start date', 'ems_text_domain'),
                'type' => 'datePickerStart',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_END_DATE,
                'label' => __('Active events period<br>End date', 'ems_text_domain'),
                'type' => 'datePickerEnd',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::EVENT_ALLOW_REGISTRATION_UNTIL,
                'label' => __('Allow registration until', 'ems_text_domain'),
                'type' => 'select',
                'options' => [
                    'event_start' => 'Event start',
                    'event_end' => 'Event end',
                    'always' => 'Always',
                ],
            ],
            [
                'name' => static::EVENT_ALLOW_CANCEL_REGISTRATION_UNTIL,
                'label' => __('Allow cancel registration until', 'ems_text_domain'),
                'type' => 'select',
                'options' => [
                    'event_start' => 'Event start',
                    'event_end' => 'Event end',
                    'never' => 'Never',
                    'always' => 'Always',
                ],
            ],
        ];
    }
}
