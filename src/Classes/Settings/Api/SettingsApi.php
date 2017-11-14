<?php
namespace BIT\EMS\Settings\Api;

/**
 * @author Christoph Bessei
 */
class SettingsApi extends \WeDevs_Settings_API
{
    public function callback_datePickerStart($args)
    {
        $args['class'] = 'datepicker_period_start';
        $args['type'] = 'text';
        $this->printTextFieldHtml($args);
    }

    public function callback_datePickerEnd($args)
    {
        $args['class'] = 'datepicker_period_end';
        $args['type'] = 'text';
        $this->printTextFieldHtml($args);
    }


    protected function printTextFieldHtml($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $type = isset($args['type']) ? $args['type'] : 'text';
        $placeholder = empty($args['placeholder']) ? '' : 'placeholder="' . $args['placeholder'] . '"';
        $class = $args['class'] ?? '';

        $html = sprintf(
            '<input type="%1$s" class="%2$s-text %7$s" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" %6$s/>',
            $type,
            $size,
            $args['section'],
            $args['id'],
            $value,
            $placeholder,
            $class
        );

        echo $html;
    }
}
