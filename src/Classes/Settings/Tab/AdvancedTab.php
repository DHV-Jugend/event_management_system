<?php
namespace BIT\EMS\Settings\Tab;

use C3\WpSettings\Tab\AbstractTab;

/**
 * @author Christoph Bessei
 */
class AdvancedTab extends AbstractTab
{
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
        return [];
    }
}
