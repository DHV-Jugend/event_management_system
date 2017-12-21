<?php
namespace BIT\EMS\Settings\Tab;

use C3\WpSettings\Tab\AbstractTab;

/**
 * @author Christoph Bessei
 */
class AdvancedTab extends AbstractTab
{
    const EVENT_LIST_DATE_FORMAT = \Ems_Conf::PREFIX . 'event_liste_date_format';

    public function getId(): string
    {
        return \Ems_Conf::PREFIX . 'advanced';
    }

    public function getTitle(): string
    {
        return __('Advanced Settings', 'ems_text_domain');
    }

    public function getFields(): array
    {
        return [
            [
                'name' => static::EVENT_LIST_DATE_FORMAT,
                'label' => __('Event list date format', 'ems_text_domain'),
                'type' => 'text',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}
