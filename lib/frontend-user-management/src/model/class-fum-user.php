<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Fum_User extends Fum_Observable implements Fum_Observer
{

    private static $object = NULL;
    private static $user_fields = NULL;

    public static function observe_object(Fum_Observable $observable)
    {
        if (self::$object === NULL) {
            self::$object = new Fum_User();
        }
        $observable->addObserver(self::$object);
    }

    public function update(Fum_Observable $observable)
    {
        if ($observable instanceof Fum_Html_Form) {
            self::update_user($observable);
        }
    }

    public static function change_password($ID, $pass, $new_pass, $new_pass_check)
    {
        $user = get_userdata($ID);
        if ($new_pass == $new_pass_check) {
            if (wp_check_password($pass, $user->data->user_pass, $ID)) {
                wp_set_password($new_pass, $ID);
                return true;
            }
        }
        return false;
    }

    private static function update_user(Fum_Html_Form $form)
    {
        self::$user_fields = Fum_Activation::get_user_input_fields();

        switch ($form->get_unique_name()) {
            case Fum_Conf::$fum_login_form_unique_name:
                break;
            case Fum_Conf::$fum_lost_password_form_unique_name:
                break;
            case Fum_Conf::$fum_change_password_form_unique_name:
                $params = $form->get_callback_param();
                $pass = $form->get_input_field($params['password'])->get_value();
                $new_pass = $form->get_input_field($params['new_password'])->get_value();
                $new_pass_check = $form->get_input_field($params['new_password_check'])->get_value();
                self::change_password(get_current_user_id(), $pass, $new_pass, $new_pass_check);
                break;
            case Fum_Conf::$fum_edit_form_unique_name:
            case Fum_Conf::$fum_event_register_form_unique_name:
            case Fum_Conf::$fum_register_form_unique_name:

                $ID = get_current_user_id();
                if (0 === $ID) {
                    $ID = $form->get_input_field('fum_ID')->get_value();
                }
                $user_data_fields = get_userdata($ID)->to_array();
                $user_data = array();
                foreach ($form->get_input_fields() as $input_field) {
                    $value = $input_field->get_value();
                    $name = $input_field->get_name();
                    //Check if input_field contains the data of a default wordpress user field
                    if (isset($user_data_fields[$name]) || in_array($name, $user_data_fields)) {
                        $user_data[$name] = $value;
                    } else {
                        if (in_array($name, self::$user_fields)) {
                            update_user_meta($ID, $name, $value);
                        }
                    }
                }
                if (!empty($user_data)) {
                    wp_update_user(array_merge(array('ID' => get_current_user_id()), $user_data));
                }
                break;

        }

    }

    public static function get_user_data($id)
    {
        $user_data = get_userdata($id);
        if (false === $user_data) {
            return array();
        }
        $user_data = $user_data->to_array();
        $user_meta = get_user_meta($id);

        $callback = create_function('$value', 'return implode( "", $value );');
        //get_user_meta returns an array for each field, remove this additional array
        $user_meta = array_map($callback, $user_meta);

        //From the array_merge php manual: If the input arrays have the same string keys, then the later value for that key will overwrite the previous one.
        //So the order is important and we prefer the values from user_data than from user_meta!
        $user_data = array_merge($user_meta, $user_data);
        return $user_data;
    }

    public static function fill_form(Fum_Html_Form $form)
    {
        $user_data = get_userdata(get_current_user_id())->to_array();
        $user_meta = get_user_meta(get_current_user_id());

        $callback = create_function('$value', 'return implode( "", $value );');
        //get_user_meta returns an array for each field, remove this additional array
        $user_meta = array_map($callback, $user_meta);

        //From the array_merge php manual: If the input arrays have the same string keys, then the later value for that key will overwrite the previous one.
        //So the order is important and we prefer the values from user_data than from user_meta!
        $user_data = array_merge($user_meta, $user_data);

        foreach ($form->get_input_fields() as $input_field) {
            if (!isset($user_data[$input_field->get_name()]) || $input_field->get_type() == Html_Input_Type_Enum::SUBMIT) {
                continue;
            }
            $input_field->set_value($user_data[$input_field->get_name()]);
        }
        return $form;
    }
} 