<?php
/**
 * @author Christoph Bessei
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

if (isset($GLOBALS['argv'][0])) {
    define("ROOT_DIR", dirname($GLOBALS['argv'][0], 5));
} elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
    define("ROOT_DIR", dirname($_SERVER['SCRIPT_FILENAME'], 5));
} else {
    throw new Exception("Couldn't determine ROOT_DIR");
}

$application = new Application();

$application->add(new \BIT\EMS\Command\UploadParticipantListCommand());

$application->run();