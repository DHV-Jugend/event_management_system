<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Settings;

use BIT\EMS\Exception\InvalidSettingsTabException;
use BIT\EMS\Settings\Api\SettingsApi;
use BIT\EMS\Settings\Tab\AdvancedTab;
use BIT\EMS\Settings\Tab\BasicTab;
use BIT\EMS\Settings\Tab\CloudTab;
use BIT\EMS\Settings\Tab\EventManagerMailTab;
use BIT\EMS\Settings\Tab\ParticipantMailTab;
use BIT\EMS\Settings\Tab\TabInterface;

class Settings
{
    /**
     * @var SettingsApi
     */
    protected $settings_api;

    /**
     * @var \BIT\EMS\Settings\Tab\TabInterface[]
     */
    protected $tabs;

    public static function register(): Settings
    {
        return new static();
    }

    /**
     * Get the value of a settings field
     *
     * @param string $option Settings field name
     * @param mixed $section Object which implements TabInterface or class name of a class which implements TabInterface or id of tab
     * @param mixed $default Default value if the option does not exist
     * @return string
     * @throws \BIT\EMS\Exception\InvalidSettingsTabException
     */
    public static function get(string $option, $section, $default = false)
    {
        if (is_string($section)) {
            if (is_a($section, TabInterface::class, true)) {
                $section = new $section();
                $options = get_option($section->getId());
            } else {
                $options = get_option($section);
            }
        } elseif ($section instanceof TabInterface) {
            $options = get_option($section->getId());
        } else {
            throw new InvalidSettingsTabException("Couldn't find settings tab: " . (string)$section);
        }

        if (array_key_exists($option, $options)) {
            return $options[$option];
        }

        return $default;
    }

    public function __construct()
    {
        $this->settings_api = new SettingsApi();

        $this->tabs[BasicTab::class] = new BasicTab();
        $this->tabs[ParticipantMailTab::class] = new ParticipantMailTab();
        $this->tabs[EventManagerMailTab::class] = new EventManagerMailTab();
        $this->tabs[CloudTab::class] = new CloudTab();
        $this->tabs[AdvancedTab::class] = new AdvancedTab();

        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public function admin_init()
    {
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());

        $this->settings_api->admin_init();
    }

    public function admin_menu()
    {
        add_options_page(
            'Event management system',
            'Event management system',
            'delete_posts',
            'ems_options',
            [$this, 'plugin_page']
        );
    }

    public function get_settings_sections()
    {
        $sections = [];

        foreach ($this->tabs as $tab) {
            $sections[] = [
                'id' => $tab->getId(),
                'title' => $tab->getTitle(),
            ];
        }

        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
        $fields = [];

        foreach ($this->tabs as $tab) {
            $fields[$tab->getId()] = $tab->getFields();
        }

        return $fields;
    }

    function plugin_page()
    {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }
}
