<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Command;

use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Service\ParticipantListService;
use BIT\EMS\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadParticipantListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('upload:participantList')
            ->setDescription('Upload participant list to remote server')
            ->addOption('server', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Remote server(s)')
            ->addArgument('event', InputArgument::REQUIRED, 'Event ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = (int)$input->getArgument('event');
        if ($event <= 0) {
            throw new InvalidArgumentException("Event has to be a positive integer");
        }

        $this->loadWordPress();
        $eventRepository = new EventRepository();
        $event = $eventRepository->findEventById($event);
        $participantListService = new ParticipantListService();
        $basePath = \Event_Management_System::getPluginPath();

        $privateListPath = $basePath . 'tmp/' . ParticipantListService::getUrlSafeFileName($event, 'Eventleiter');
        $publicListPath = $basePath . 'tmp/' . ParticipantListService::getUrlSafeFileName($event, 'Teilnehmer');

        $participantListService->generatePrivateParticipantListFromEvent($event, $privateListPath);
        $participantListService->generatePublicParticipantListFromEvent($event, $publicListPath);
    }

    protected function loadWordPress()
    {
        ob_start();
        //setup global $_SERVER variables to keep WP from trying to redirect
        $_SERVER = array_merge($_SERVER, [
            "HTTP_HOST" => "",
            "SERVER_NAME" => "",
            "REQUEST_URI" => "/",
            "REQUEST_METHOD" => "GET",
        ]);

        try {
            include __DIR__ . '/../../../../../../wp-load.php';
        } catch (\Exception $e) {
            // Fill $_SERVER with real values
            $_SERVER['HTTP_HOST'] = WP_SITEURL;
            $_SERVER['SERVER_NAME'] = WP_SITEURL;
        }

        $result = ob_get_clean();
        return $result;
    }
}