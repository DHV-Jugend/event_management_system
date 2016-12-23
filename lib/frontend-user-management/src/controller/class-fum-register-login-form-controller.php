<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Fum_Register_Login_Form_Controller
{


    public static function create_register_login_form_header()
    {

        add_filter('the_title', array('Fum_Register_Login_Form_Controller', 'filter_title'), 10, 2);
        add_filter('wp_title', array('Fum_Register_Login_Form_Controller', 'filter_html_title'), 10, 3);


    }

    public static function filter_title($title, $post_id)
    {
        global $wp;
        $current_url = trim(site_url($wp->request), '/');
        $current_post_id = url_to_postid($current_url);

        //TODO Check if this is a reliable method to filter the main title
        if ($current_post_id === $post_id) {
            if (isset($_REQUEST['action'])) {
                switch ($_REQUEST['action']) {
                    case 'register':
                        return "Registrieren";
                        break;
                    case 'lostpassword':
                        return 'Passwort vergessen';
                        break;
                }
            }
        }
        return $title;
    }

    public static function filter_html_title($title)
    {


        if (isset($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'register':
                    return str_replace(get_post()->post_title, 'Registrieren', $title);
                    break;
                case 'lostpassword':
                case 'resetpassword':
                case 'rp':
                    return str_replace(get_post()->post_title, 'Passwort vergessen', $title);
                    break;
            }
            return $title;
        }
    }

    public static function create_register_login_form()
    {

        $error = NULL;
        if (isset($_REQUEST[Fum_Conf::$fum_unique_name_field_name])) {
            if ($_REQUEST[Fum_Conf::$fum_unique_name_field_name] === Fum_Conf::$fum_login_form_unique_name) {
                $form = Fum_Html_Form::get_form(Fum_Conf::$fum_login_form_unique_name);
                $error = self::validate_login_form();
            } else if ($_REQUEST[Fum_Conf::$fum_unique_name_field_name] === Fum_Conf::$fum_register_form_unique_name) {
                $form = Fum_Html_Form::get_form(Fum_Conf::$fum_register_form_unique_name);
                $error = self::validate_register_form($form);
            }
        }

        if (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') {
            ?>
            <p>
                <strong>
                    Wir haben dir eine E-Mail mit einem Link zum Passwort ändern geschickt.<br/>
                    Sollte diese in den nächsten Minuten nicht ankommen überprüfe bitte deinen SPAM Ordnern.
                </strong>
            </p>
            <?php
            exit();
        }

//Print if unique_name_Field was not found OR $error != NULL
        if (!isset($_REQUEST[Fum_Conf::$fum_unique_name_field_name]) || true !== $error) {
            if (is_wp_error($error)) {
                echo $error->get_error_message();
            }

            if (isset($_GET['action'])) {
                $action = $_GET['action'];
            } else {
                $action = '';
            }
            switch ($action) {
                case 'register':
                    if (is_user_logged_in()) {
                        echo '<strong>Da du eingeloggt bist, kannst du dich nicht registrieren</strong>';
                    } else {
                        if (NULL === $error) {
                            $form = Fum_Html_Form::get_form(Fum_Conf::$fum_register_form_unique_name);
                        }
                        if (!get_option(Fum_Conf::$fum_register_form_generate_password_option)) {
                            $password_field = Fum_Html_Input_Field::get_input_field(Fum_Conf::$fum_input_field_password);
                            $form->insert_input_field_after_unique_name($password_field, Fum_Conf::$fum_input_field_username);
                        }
                        if (isset($_REQUEST[Fum_Conf::$fum_unique_name_field_name])) {
                            foreach ($form->get_input_fields() as $input_field) {
                                $input_field->set_value($_REQUEST[$input_field->get_name()]);
                            }
                        }
                        Fum_Form_View::output($form);
                    }
                    break;
                case 'lostpassword':

                    $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
                    $errors = new WP_Error();
                    if ($http_post) {
                        $errors = self::retrieve_password();
                        if (!is_wp_error($errors)) {
                            $redirect_to = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : get_permalink() . '?checkemail=confirm';
                            wp_safe_redirect($redirect_to);
                            exit();
                        }
                    }


                    if (isset($_GET['error'])) {
                        if ('invalidkey' == $_GET['error'])
                            $errors->add('invalidkey', __('Sorry, that key does not appear to be valid.'));
                        elseif ('expiredkey' == $_GET['error'])
                            $errors->add('expiredkey', __('Sorry, that key has expired. Please try again.'));
                    }

                    $lostpassword_redirect = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
                    /**
                     * Filter the URL redirected to after submitting the lostpassword/retrievepassword form.
                     *
                     * @since 3.0.0
                     *
                     * @param string $lostpassword_redirect The redirect destination URL.
                     */
                    $redirect_to = apply_filters('lostpassword_redirect', $lostpassword_redirect);

                    /**
                     * Fires before the lost password form.
                     *
                     * @since 1.5.1
                     */
                    do_action('lost_password');

                    $user_login = isset($_REQUEST['user_login']) ? wp_unslash($_REQUEST['user_login']) : '';
                    if (is_wp_error($errors)) {
                        foreach ($errors->get_error_messages() as $message) {
                            echo $message . "<br/>";
                        }
                    }
                    ?>

                    <form name="lostpasswordform" id="lostpasswordform"
                          action="<?php echo esc_url(get_permalink() . '?action=lostpassword', 'login_post'); ?>"
                          method="post">
                        <p>
                            <label for="user_login"><?php _e('Username or E-mail:') ?><br/>
                                <input type="text" name="user_login" id="user_login" class="input"
                                       value="<?php echo esc_attr($user_login); ?>" size="20"/></label>
                        </p>
                        <?php
                        /**
                         * Fires inside the lostpassword <form> tags, before the hidden fields.
                         *
                         * @since 2.1.0
                         */
                        do_action('lostpassword_form');
                        ?>
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>"/>

                        <p class="submit">
                            <input type="submit" name="wp-submit" id="wp-submit"
                                   class="button button-primary button-large"
                                   value="<?php esc_attr_e('Get New Password'); ?>"/>
                        </p>
                    </form>

                    <p id="nav">
                        <a href="<?php echo esc_url(wp_login_url()); ?>"><?php _e('Log in') ?></a>
                        <?php
                        if (get_option('users_can_register')) :
                            $registration_url = sprintf('<a href="%s">%s</a>', esc_url(wp_registration_url()), __('Register'));
                            /**
                             * Filter the registration URL below the login form.
                             *
                             * @since 1.5.0
                             *
                             * @param string $registration_url Registration URL.
                             */
                            echo ' | ' . apply_filters('register', $registration_url);
                        endif;
                        ?>
                    </p>

                    <?php
                    break;
                case 'resetpass':
                case 'rp':

                    $user = check_password_reset_key($_GET['key'], $_GET['login']);
                    if (is_wp_error($user)) {
                        if ($user->get_error_code() === 'expired_key') {
                            wp_redirect(get_permalink() . '?action=lostpassword&error=expiredkey');
                        } else {
                            wp_redirect(get_permalink() . '?action=lostpassword&error=invalidkey');
                        }
                        exit;
                    }
                    $errors = new WP_Error();

                    if (isset($_REQUEST['pass1']) && $_REQUEST['pass1'] != $_REQUEST['pass2'])
                        $errors->add('password_reset_mismatch', __('The passwords do not match.'));

                    /**
                     * Fires before the password reset procedure is validated.
                     *
                     * @since 3.5.0
                     *
                     * @param object $errors WP Error object.
                     * @param WP_User|WP_Error $user WP_User object if the login and reset key match. WP_Error object otherwise.
                     */
                    do_action('validate_password_reset', $errors, $user);

                    if ((!$errors->get_error_code()) && isset($_REQUEST['pass1']) && !empty($_REQUEST['pass1'])) {
                        reset_password($user, $_REQUEST['pass1']);
                        echo '<p><strong>Passwortänderung war erfolgreich</strong></p>';
                        echo '<a href="' . get_permalink(get_option(Fum_Conf::$fum_register_login_page_name)) . '">Anmelden</a>';
                        exit();
                    }

//					wp_enqueue_script( 'utils' );
                    //Needed for password strength estimation
//                    wp_enqueue_script('user-profile');

                    ?>
                    <form name="resetpassform" id="resetpassform"
                          action=" <?php echo esc_url(get_permalink() . '?action=resetpass&key=' . urlencode($_GET['key']) . '&login=' . urlencode($_GET['login']), 'login_post'); ?>"
                          method="post" autocomplete="off">
                        <input type="hidden" id="user_login" value="<?php echo esc_attr($_GET['login']); ?>"
                               autocomplete="off"/>

                        <p>
                            <label for="pass1"><?php _e('New password') ?><br/>
                                <input type="password" name="pass1" id="pass1" class="input" size="20" value=""
                                       autocomplete="off"/></label>
                        </p>

                        <p>
                            <label for="pass2"><?php _e('Confirm new password') ?><br/>
                                <input type="password" name="pass2" id="pass2" class="input" size="20" value=""
                                       autocomplete="off"/></label>
                        </p>

                        <p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>
                        <br class="clear"/>

                        <p class="submit">
                            <input type="submit" name="wp-submit" id="wp-submit"
                                   class="button button-primary button-large"
                                   value="<?php esc_attr_e('Reset Password'); ?>"/>
                        </p>
                    </form>

                    <p id="nav">
                        <a href="<?php echo esc_url(wp_login_url()); ?>"><?php _e('Log in'); ?></a>
                        <?php
                        if (get_option('users_can_register')) :
                            $registration_url = sprintf('<a href="%s">%s</a>', esc_url(wp_registration_url()), __('Register'));
                            /** This filter is documented in wp-login.php */
                            echo ' | ' . apply_filters('register', $registration_url);
                        endif;
                        ?>
                    </p>

                    <?php
                    break;
                case 'logout':
                    //TODO Workaround so wp-united does work
                    //Do not check nonce if logout comes from phpBB
                    if (!isset($_REQUEST['redirect_to'])) {
                        check_admin_referer('log-out');
                    } else if ($_REQUEST['redirect_to'] != 'https://forum.schwarzwald-falke.de') {
                        check_admin_referer('log-out');
                    }

                    wp_logout();
                    if (isset($_REQUEST['redirect_to'])) {
                        wp_redirect($_REQUEST['redirect_to']);
                        exit();
                    } else {
                        wp_safe_redirect(home_url());
                        exit();
                    }
                    break;
                default:
                    if (is_user_logged_in()) {
                        echo '<strong> Du bist bereits einloggt</strong>';
                    } else {
                        $form = Fum_Html_Form::get_form(Fum_Conf::$fum_login_form_unique_name);
                        if (isset($_REQUEST['interim-login']) && 1 == $_REQUEST['interim-login']) {
                            $field = new Fum_Html_Input_Field('interim-login', 'interim-login', new Html_Input_Type_Enum(Html_Input_Type_Enum::HIDDEN), '', 'interim-login', false);
                            $field->set_value(1);
                            $form->add_input_field($field);
                        }

                        Fum_Form_View::output($form);
                        echo '<a href="' . get_permalink() . '?action=lostpassword">Passwort vergessen?</a>';
                    }
            }
        } else {
            echo '<p><strong>Registrierung erfolgreich</strong></p>';
            echo 'Wir haben dir eine E-Mail mit deinem Passwort geschickt. <br />';
            echo 'Sollte diese in den nächsten Minuten nicht ankommen, übperüfe bitte deinen SPAM-Ordner.';
        }
    }


    /**
     * Validate login form (calls wp_singnon), redirects if singon was successfull, returns WP_Error if not
     * @return WP_Error
     */
    private static function validate_login_form()
    {
        $user = wp_signon($_REQUEST, true);
        if (is_wp_error($user)) {
            return $user;
        } else {
            wp_set_auth_cookie($user->ID);
            if (isset($_REQUEST['interim-login']) && 1 == $_REQUEST['interim-login']) {
                ob_end_clean();
                self::get_interim_login_html();
                exit();
            }
            if (isset($_REQUEST['redirect_to'])) {
                wp_safe_redirect($_REQUEST['redirect_to']);
                exit();
            }
            wp_safe_redirect(admin_url('profile.php'));
            exit();
        }
    }

    private static function get_interim_login_html()
    {
        ?>
        <!DOCTYPE html>        <!--[if IE 8]>
                               <html xmlns="http://www.w3.org/1999/xhtml"
                                     class="ie8" <?php language_attributes(); ?>>        <![endif]-->        <!--[if !(IE 8) ]><!-->
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <!--<![endif]-->
        <head>
            <meta http-equiv="Content-Type"
                  content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>"/>
            <title><?php bloginfo('name'); ?> &rsaquo; Login</title>
            <?php
            do_action('login_enqueue_scripts');
            do_action('login_head');
            ?>
        </head>
        <body class="interim-login-success">
        Erfolgreich eingeloggt
        </body>
        </html>
        <?php
    }

    private static function validate_register_form(Fum_Html_Form $form)
    {

        $form->set_values_from_array($_REQUEST);
        $errors = $form->validate();
        if (true === $errors) {
            if (get_option(Fum_Conf::$fum_register_form_generate_password_option)) {
                //Wordpress uses sometimes user_pass (wp_insert_usert) and sometimes user_password, this is a workaround for this problem
                $_REQUEST['user_pass'] = wp_generate_password();
                $_REQUEST[Fum_Conf::$fum_input_field_password] = $_REQUEST['user_pass'];

            }
            $ID = wp_insert_user($_REQUEST);
            if (is_wp_error($ID)) {
                $errors = $ID;
            } else {
                $id_field = new Fum_Html_Input_Field('fum_ID', 'fum_ID', new Html_Input_Type_Enum(Html_Input_Type_Enum::HIDDEN), '', '', '');
                $id_field->set_value($ID);
                $form->add_input_field($id_field);
                Fum_User::observe_object($form);
                $form->save();
            }


        }
        return $errors;
    }


    /**
     * Handles sending password retrieval email to user.
     *
     * @uses $wpdb WordPress Database object
     *
     * @return bool|WP_Error True: when finish. WP_Error on error
     */
    public static function retrieve_password()
    {
        global $wpdb, $wp_hasher;

        $errors = new WP_Error();

        if (empty($_REQUEST['user_login'])) {
            $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));
        } else if (strpos($_REQUEST['user_login'], '@')) {
            $user_data = get_user_by('email', trim($_REQUEST['user_login']));
            if (empty($user_data))
                $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
        } else {
            $login = trim($_REQUEST['user_login']);
            $user_data = get_user_by('login', $login);
        }

        /**
         * Fires before errors are returned from a password reset request.
         *
         * @since 2.1.0
         */
        do_action('lostpassword_post');

        if ($errors->get_error_code())
            return $errors;

        if (!$user_data) {
            $errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
            return $errors;
        }

        // redefining user_login ensures we return the right case in the email
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;

        /**
         * Fires before a new password is retrieved.
         *
         * @since      1.5.0
         * @deprecated 1.5.1 Misspelled. Use 'retrieve_password' hook instead.
         *
         * @param string $user_login The user login name.
         */
        do_action('retreive_password', $user_login);
        /**
         * Fires before a new password is retrieved.
         *
         * @since 1.5.1
         *
         * @param string $user_login The user login name.
         */
        do_action('retrieve_password', $user_login);

        /**
         * Filter whether to allow a password to be reset.
         *
         * @since 2.7.0
         *
         * @param     bool       true           Whether to allow the password to be reset. Default true.
         * @param int $user_data ->ID The ID of the user attempting to reset a password.
         */
        $allow = apply_filters('allow_password_reset', true, $user_data->ID);

        if (!$allow)
            return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
        else if (is_wp_error($allow))
            return $allow;

        // Generate something random for a password reset key.
        $key = wp_generate_password(20, false);

        /**
         * Fires when a password reset key is generated.
         *
         * @since 2.5.0
         *
         * @param string $user_login The username for the user.
         * @param string $key The generated password reset key.
         */
        do_action('retrieve_password_key', $user_login, $key);

        // Now insert the key, hashed, into the DB.
        if (empty($wp_hasher)) {
            require_once ABSPATH . 'wp-includes/class-phpass.php';
            $wp_hasher = new PasswordHash(8, true);
        }
        // Now insert the key, hashed, into the DB.
        if (empty($wp_hasher)) {
            require_once ABSPATH . WPINC . '/class-phpass.php';
            $wp_hasher = new PasswordHash(8, true);
        }
        $hashed = time() . ':' . $wp_hasher->HashPassword($key);
        $key_saved = $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));
        if (false === $key_saved) {
            return new WP_Error('no_password_key_update', __('Could not save password reset key to database.'));
        }

        $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
        $message .= network_home_url('/') . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
        $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
        $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

        if (is_multisite())
            $blogname = $GLOBALS['current_site']->site_name;
        else
            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $title = sprintf(__('[%s] Password Reset'), $blogname);

        /**
         * Filter the subject of the password reset email.
         *
         * @since 2.8.0
         *
         * @param string $title Default email title.
         */
        $title = apply_filters('retrieve_password_title', $title);
        /**
         * Filter the message body of the password reset mail.
         *
         * @since 2.8.0
         *
         * @param string $message Default mail message.
         * @param string $key The activation key.
         */
        $message = apply_filters('retrieve_password_message', $message, $key);
        Ems_Event_Registration::send_mail_via_smtp($user_email, $title, $message);
//		if ( $message && ! wp_mail( $user_email, $title, $message ) )
//			wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function.' ) );

        return true;
    }
}