<?php
namespace BIT\EMS\Settings\Tab;

use C3\WpSettings\Tab\AbstractTab;

/**
 * @author Christoph Bessei
 */
class CloudTab extends AbstractTab
{
    const CLOUD1_HOST = \Ems_Conf::PREFIX . 'cloud1_host';
    const CLOUD1_USERNAME = \Ems_Conf::PREFIX . 'cloud1_username';
    const CLOUD1_PASSWORD = \Ems_Conf::PREFIX . 'cloud1_password';
    const CLOUD1_DIR = \Ems_Conf::PREFIX . 'cloud1_dir';


    public function getId(): string
    {
        return \Ems_Conf::PREFIX . 'cloud';
    }

    public function getTitle(): string
    {
        return __('Cloud Settings', 'ems_text_domain');
    }

    public function getFields(): array
    {
        return [
            [
                'name' => static::CLOUD1_HOST,
                'label' => __('Event list upload server', 'ems_text_domain'),
                'type' => 'url',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::CLOUD1_USERNAME,
                'label' => __('Event list upload username', 'ems_text_domain'),
                'type' => 'text',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::CLOUD1_PASSWORD,
                'label' => __('Event list upload password', 'ems_text_domain'),
                'type' => 'password',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => static::CLOUD1_DIR,
                'label' => __('Event list upload directory', 'ems_text_domain'),
                'type' => 'text',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}
