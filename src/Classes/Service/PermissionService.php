<?php
namespace BIT\EMS\Service;

/**
 * @author Christoph Bessei
 */
class PermissionService
{
    /**
     * Check if current user has a capability
     * - User is not logged an: Redirect to login (including redirect_to link to current page) and EXIT(!)
     * - User is logged in but has no permission: Add 403 header, show an error message and return FALSE
     * - User is logged in an has permission: return true
     *
     * @param string $capability
     * @param string $redirectAfterLogin Overwrite the default (the current url) as redirect after login url
     * @return bool
     */
    public function checkCapability(string $capability, string $redirectAfterLogin = null)
    {
        $this->requireLogin();

        if (!current_user_can($capability)) {
            header('HTTP/1.0 403 Forbidden');
            ?>
            <p><strong>Du hast keinen Zugriff auf diese Seite.</strong></p>
            <?php
            return false;
        }
        return true;
    }

    public function requireLogin(string $redirectAfterLogin = null)
    {
        if (!is_user_logged_in()) {
            if (empty($redirectAfterLogin)) {
                global $wp;
                // Determine current url: https://gist.github.com/leereamsnyder/fac3b9ccb6b99ab14f36
                $redirectAfterLogin = add_query_arg($_SERVER['QUERY_STRING'], '', home_url($wp->request));;
            }

            wp_safe_redirect(wp_login_url($redirectAfterLogin));
            exit();
        }
    }
}
