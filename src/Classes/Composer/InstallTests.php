<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Composer;


class InstallTests
{
    public static function run()
    {
        $pluginDir = realpath(__DIR__ . "/../../../..");
        
        $eventManagementSystemDir = realpath(__DIR__ . "/../../..");
        // Install wp tests
        passthru('bash ' . $eventManagementSystemDir . '/tests/bin/install-wp-tests.sh wordpress_test root "" localhost $WP_VERSION');

        $frontendUserManagementDir = $pluginDir . '/frontend-user-management';
        if (!file_exists($frontendUserManagementDir)) {
            // Install frontend-user-management
            passthru('git clone https://github.com/SchwarzwaldFalke/frontend-user-management ' . $frontendUserManagementDir);
        }
    }
}