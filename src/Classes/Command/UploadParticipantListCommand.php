<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Command;

use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Model\Event;
use BIT\EMS\Service\Cloud\WebDav;
use BIT\EMS\Service\ParticipantListService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadParticipantListCommand extends Command
{
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

        $this->loadWordPress();
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
        $basePath = \Event_Management_System::getPluginPath();
        // File names must stay the same for an event. Otherwise each run would create a new file instead of updating the old one
        $fileNameBase = sanitize_file_name($event->get_post()->post_title) . '_' . $event->getID();

        $privateListFileName = $fileNameBase . '_Eventleiter.xlsx';
        $privateListFileNameSecured = sha1(random_bytes(30)) . $privateListFileName;

        $publicListFileName = $fileNameBase . '_Teilnehmer.xlsx';
        $publicListFileNameSecured = sha1(random_bytes(30)) . $publicListFileName;

        $privateListPath = $basePath . 'tmp/' . $privateListFileNameSecured;
        $publicListPath = $basePath . 'tmp/' . $publicListFileNameSecured;

        $participantListService->generatePrivateParticipantListFromEvent($event, $privateListPath);
        $participantListService->generatePublicParticipantListFromEvent($event, $publicListPath);

        $settings = [
            'baseUri' => get_option(\Ems_Conf::PREFIX . 'event_list_upload_remote_server_host'),
            'userName' => get_option(\Ems_Conf::PREFIX . 'event_list_upload_remote_server_username'),
            'password' => get_option(\Ems_Conf::PREFIX . 'event_list_upload_remote_server_password'),
        ];

        $webDav = new WebDav($settings);
        $webDavFolder = 'Events/Teilnehmerlisten/' . $event->get_start_date_time()->format('Y');

        $webDav->upload(file_get_contents($privateListPath), $webDavFolder . '/' . $privateListFileName);
        $webDav->upload(file_get_contents($publicListPath), $webDavFolder . '/' . $publicListFileName);

        unlink($privateListPath);
        unlink($publicListPath);
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