<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Settings;

use BIT\EMS\Settings\Tab\AdvancedTab;
use BIT\EMS\Settings\Tab\BasicTab;
use BIT\EMS\Settings\Tab\CloudTab;
use BIT\EMS\Settings\Tab\EventManagerMailTab;
use BIT\EMS\Settings\Tab\ParticipantMailTab;

class Settings extends \C3\WpSettings\Settings
{
    public static function register($options = []): \C3\WpSettings\Settings
    {
        $basicOptions = [
            'pageTitle' => 'Event management system',
            'menuTitle' => 'Event management system',
            'capability' => 'delete_posts',
            'menuSlug' => \Fum_Conf::PREFIX . 'options',
        ];

        $settings = \C3\WpSettings\Settings::register(array_merge($basicOptions, $options));
        $settings->addTab(new BasicTab());
        $settings->addTab(new ParticipantMailTab());
        $settings->addTab(new EventManagerMailTab());
        $settings->addTab(new CloudTab());
        $settings->addTab(new AdvancedTab());

        return $settings;
    }
}
