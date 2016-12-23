<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Fum_Initialisation
{

    public static function initiate_plugin()
    {
        //Removes 'next' link in head because this could cause SEO problems and firefox is fetching the link in background which causes more traffic
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

        self::add_action_hooks();
        self::add_filter_hooks();
        add_shortcode(Fum_Conf::$fum_register_login_page_name, array('Fum_Register_Login_Form_Controller', 'create_register_login_form'));
        add_shortcode(Fum_Conf::$fum_edit_page_name, array('Fum_Edit_Form_Controller', 'create_edit_form'));
        add_shortcode(Fum_Conf::$fum_event_registration_page, array('Fum_Event_Registration_Controller', 'create_event_registration_form'));
        add_shortcode('ems_eventverwaltung', array('Fum_Registered_Event_list', 'create_applied_event_form'));
        add_shortcode('recent_posts', array('Fum_Initialisation', 'my_recent_posts_shortcode'));
        add_shortcode('contact_form', array('Fum_Contact_Form_Controller', 'create_contact_form'));


    }

    public static function my_recent_posts_shortcode($atts)
    {
        $q = new WP_Query(
            array('orderby' => 'date', 'posts_per_page' => '1')
        );

        $list = '<ul class="recent-posts">';

        while ($q->have_posts()) : $q->the_post();

            $list .= '<li><h3>' . get_the_title() . '</h3><i>' . get_the_date() . '</i>' . '<br/><p>' . get_the_excerpt() . '</p></li>';

        endwhile;

        wp_reset_query();

        return $list . '</ul>';
    }


    private static function add_action_hooks()
    {

        //Action hook for changing
        add_action('get_header', array('Fum_Initialisation', 'check_shortcode'));
//Register plugin settings
        add_action('admin_init', array('Fum_Option_Page_Controller', 'register_settings'));
//Create plugin admin menu page
        add_action('admin_menu', array('Fum_Option_Page_Controller', 'create_menu'));


        /*			add_action( 'wp_before_admin_bar_render', array( 'Admin_Bar', 'create_admin_bar' ) );
            add_action( 'init', array( 'Admin_Bar', 'show_admin_bar' ) );*/
        add_action('init', array('Fum_Post', 'fum_register_post_type'));
        add_action('init', array(new Fum_Front_End_Form(), 'buffer_content_if_front_end_form'));


        /*class-activation-email.php*/

        //Create activation code on user_register and add it to the user meta
        add_action('user_register', array('Fum_Activation_Email', 'new_user_registered'));

        //Check if url contains activation key and if yes, prepend "You have successfully your account etc.."
        add_filter('the_content', array('Fum_Activation_Email', 'activate_user'));

        if (get_option(Fum_Conf::$fum_register_form_use_activation_mail_option)) {
            //Check on login  if user is activated
            add_filter('wp_authenticate_user', array('Fum_Activation_Email', 'authenticate'), 10, 1);
        }

//Delete not activated users, if the home url changes, because the activation link may returns a 404 then
        add_action('update_option_home', array('Fum_Activation_Email', 'delete_not_activated_users'));
        add_action('update_option_siteurl', array('Fum_Activation_Email', 'delete_not_activated_users'));


//Redirect wp-admin/profile.php (Only redirect if the user edits his OWN profile!)
        add_action('show_user_profile', array('Fum_Redirect', 'redirect_own_profile_edit'));

        if (get_option(Fum_Conf::$fum_general_option_group_hide_wp_login_php)) {
            //Redirect wp-login.php
            add_action('login_init', array('Fum_Redirect', 'redirect_wp_login_php'));
        }

        if (get_option(Fum_Conf::$fum_general_option_group_hide_dashboard_from_non_admin)) {
            add_action('wp_dashboard_setup', array('Fum_Redirect', 'redirect_to_home_if_user_cannot_manage_options'));
        }


    }

    private static function add_filter_hooks()
    {
        add_filter('force_ssl', array(new Fum_Front_End_Form(), 'use_ssl_on_front_end_form'), 1, 3);

        add_filter('logout_url', array('Fum_Redirect', 'redirect_wp_logout'), 10, 2);

    }


    /**
     * Calls shortcode callback_header function in wp_head, useful for add styles,scripts, change title, etc.
     *
     *
     * <p>Checks if the current post (it checks the complete WP_Post object) contains a shortcode with the FUM_NAME_PREFIX
     * If a shortcode is found it takes the callback functionname adds _header and calls it (if it's callable)</p>
     *
     * <p><b>Example:</b></p>
     * <code>add_shortcode('FUM_NAME_PREFIX_test',array('Classname','functionname'));</code>
     * then the following is called:<br>
     * <code>call_user_func(array('Classname','functionname_header'));</code>
     *
     * <code>add_shortcode('FUM_NAME_PREFIX_test','functionname');</code>
     * then the following is called:
     * <code>call_user_func('functionname_header');</code>
     */
    public static function check_shortcode()
    {
        global $shortcode_tags;
        foreach ($shortcode_tags as $shortcode_tag => $callback) {
            if (0 === stripos($shortcode_tag, Fum_Conf::FUM_NAME_PREFIX)) {
                $post = get_post();
                //Maybe the current site is not a post, not sure when this happens
                if (NULL === $post) {
                    continue;
                }
                //If there are no object vars, then it's not possible that there is a shortcode tag
                if (!has_shortcode(implode(' ', get_object_vars($post)), $shortcode_tag)) {
                    continue;
                }

                switch (count($callback)) {
                    case 1:
                        $function = (string)$callback;
                        $function = $function . '_header';
                        if (is_callable($function)) {
                            call_user_func($function);
                        }
                        break;
                    case 2:
                        $class = $callback[0];
                        $function = $callback[1] . '_header';
                        $callback = array($class, $function);
                        if (is_callable($callback)) {
                            call_user_func($callback);
                        }
                        break;
                }
            }
        }
    }
}
