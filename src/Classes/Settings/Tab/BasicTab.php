<?php
namespace BIT\EMS\Settings\Tab;

use BIT\EMS\Domain\Model\Enum\EventsActiveUntilEnum;
use C3\WpSettings\Tab\AbstractTab;

/**
 * @author Christoph Bessei
 */
class BasicTab extends AbstractTab
{
    const EVENT_START_DATE = \Ems_Conf::PREFIX . 'event_start_date';
    const EVENT_END_DATE = \Ems_Conf::PREFIX . 'event_end_date';
    const EVENT_ACTIVE_UNTIL = \Ems_Conf::PREFIX . 'event_active_until';
    const EVENT_HIDE_IF_IN_PAST = \Ems_Conf::PREFIX . 'event_hide_if_in_past';


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
                'name' => static::EVENT_ACTIVE_UNTIL,
                'label' => __('Events are active (shown in list) as long as ', 'ems_text_domain'),
                'type' => 'select',
                'options' => [
                    EventsActiveUntilEnum::START_DATE => 'Event start is in active events period',
                    EventsActiveUntilEnum::END_DATE => 'Event end is in active events period',
                ],
            ],
            [
                'name' => static::EVENT_HIDE_IF_IN_PAST,
                'label' => __('Hide events if the end date is in the past', 'ems_text_domain'),
                'type' => 'select',
                'options' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
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
