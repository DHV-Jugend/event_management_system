<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Redirect
{

    /**
     * Redirects fum_event_registration with 'event' parameter to fum_event_registration with Fum_Event_Registration_Controller::$event_get_parameter
     * This necessary because 'event' seems to be a reserved parameter in wordpress
     */
    public static function redirect_event_parameter()
    {
        global $wp;
        if (isset($_REQUEST['event']) &&
            url_to_postid(trim(site_url($wp->request), '/')) == get_option(Fum_Conf::$fum_event_registration_page)) {
            $url = add_query_arg(
                [
                    \BIT\EMS\Controller\Shortcode\EventRegistrationFormController::get_event_request_parameter(
                    ) => $_REQUEST['event'],
                ],
                trim(site_url($wp->request), '/')
            );
            wp_safe_redirect($url);
            exit();
        }
    }
} 