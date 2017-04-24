<?php
/**
 * @author Christoph Bessei
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \BIT\EMS\Command\UploadParticipantListCommand());

$application->run();