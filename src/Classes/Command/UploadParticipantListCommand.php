<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Command;

use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Model\Event;
use BIT\EMS\Service\ParticipantListService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadParticipantListCommand extends Command
{

    public function __construct()
    {
        parent::__construct();
        $this->loadWordPress();
    }

    protected function configure()
    {
        $this
            ->setName('upload:participantList')
            ->setDescription('Upload participant list to remote server')
            ->addArgument('event', InputArgument::REQUIRED, 'Event ID. Use * to update all participant lists');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $input->getArgument('event');
        if ('*' !== $event && (int)$event <= 0) {
            throw new InvalidArgumentException(
                "Event has to be a positive integer or '*' to update all participant lists"
            );
        }

        $eventRepository = new EventRepository();
        if ('*' === $event) {
            $events = $eventRepository->findAll();
        } else {
            $events[] = $eventRepository->findEventById($event);
        }

        foreach ($events as $event) {
            $this->updateParticipantList($event);
        }
    }

    protected function updateParticipantList(Event $event)
    {
        $participantListService = new ParticipantListService();
        $participantListService->generateAndUploadPrivateParticipantListFromEvent($event);
        $participantListService->generateAndUploadPublicParticipantListFromEvent($event);
    }

    protected function loadWordPress()
    {
        ob_start();
        //setup global $_SERVER variables to keep WP from trying to redirect
        $_SERVER = array_merge(
            $_SERVER,
            [
                "HTTP_HOST" => "",
                "SERVER_NAME" => "",
                "REQUEST_URI" => "/",
                "REQUEST_METHOD" => "GET",
            ]
        );

        try {
            require ROOT_DIR . '/wp-load.php';
        } catch (\Exception $e) {
            // Fill $_SERVER with real values
            $_SERVER['HTTP_HOST'] = WP_SITEURL;
            $_SERVER['SERVER_NAME'] = WP_SITEURL;
        }

        $result = ob_get_clean();
        return $result;
    }
}