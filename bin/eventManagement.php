<?php
/**
 * @author Christoph Bessei
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

if (isset($GLOBALS['argv'][0])) {
    if ('/' !== $GLOBALS['argv'][0][0] && $_SERVER['PWD']) {
        // Relativ path prepend PWD
        define('ROOT_DIR', dirname($_SERVER['PWD'] . '/' . $GLOBALS['argv'][0], 5));
    } else {
        define('ROOT_DIR', dirname($GLOBALS['argv'][0], 5));
    }
} elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
    if ('/' !== $_SERVER['SCRIPT_FILENAME'][0] && $_SERVER['PWD']) {
        // Relativ path prepend PWD
        define('ROOT_DIR', dirname($_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME'], 5));
    } else {
        define('ROOT_DIR', dirname($_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME'], 5));
    }
} else {
    throw new Exception("Couldn't determine ROOT_DIR");
}

$application = new Application();

$application->add(new \BIT\EMS\Command\UploadParticipantListCommand());

$application->run();