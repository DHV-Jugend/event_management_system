<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Composer;


use Composer\Config;
use Composer\Script\Event;

class InstallTests
{
    public static function run(Event $event)
    {
        /** @var Config $config */
        $config = $event->getComposer()->getConfig();
        $pluginDir = dirname(dirname($config->get('vendor-dir')));

        $eventManagementSystemDir = dirname($config->get('vendor-dir'));
        // Install wp tests
        passthru('bash ' . $eventManagementSystemDir . '/tests/bin/install-wp-tests.sh wordpress_test root "" localhost $WP_VERSION');

        $frontendUserManagementDir = $pluginDir . '/frontend-user-management';
        if (!file_exists($frontendUserManagementDir)) {
            // Install frontend-user-management
            passthru('git clone https://github.com/SchwarzwaldFalke/frontend-user-management ' . $frontendUserManagementDir);
        }
    }
}