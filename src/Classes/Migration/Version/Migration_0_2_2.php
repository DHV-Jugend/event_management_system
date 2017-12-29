<?php
namespace BIT\EMS\Migration\Version;

use BIT\EMS\Domain\Mapper\EventRegistrationMapper;
use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Exception\MigrationFailedException;
use BIT\EMS\Log\EventRegistrationLog;
use BIT\EMS\Migration\Migration;
use BIT\EMS\Migration\MigrationInterface;
use Carbon\Carbon;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

/**
 *
 *
 * @author Christoph Bessei
 */
class Migration_0_2_2 implements MigrationInterface
{
    /**
     * @var  string
     */
    protected $resultMessage = '';

    /**
     * @var  \BIT\EMS\Domain\Repository\EventRegistrationRepository
     */
    protected $eventRegistrationRepository;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var string
     */
    protected $dbPrefix;

    /**
     * @var string
     */
    protected $eventRegistrationLogTable;

    /**
     * @var \BIT\EMS\Domain\Mapper\EventRegistrationMapper
     */
    protected $eventRegistrationMapper;

    public function getDescription(): string
    {
        return '* Migrated event registration log entries from legacy table<br>* Added DATETIME fields to the event registration table<br>';
    }

    public function getResultMessage(): string
    {
        return $this->resultMessage;
    }

    public function __construct()
    {
        global $wpdb;

        $this->eventRegistrationRepository = new EventRegistrationRepository();
        $this->eventRegistrationMapper = new EventRegistrationMapper();

        $config = new Configuration();
        $connectionParams = [
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => DB_HOST,
            'driver' => 'pdo_mysql',
        ];
        $this->db = DriverManager::getConnection($connectionParams, $config);
        $this->dbPrefix = $wpdb->prefix;
        $this->eventRegistrationLogTable = $this->dbPrefix . 'log_ems_eventregistration';
    }

    public function run()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $eventRegistrationTable = $wpdb->base_prefix . \Ems_Conf::PREFIX . 'event_registration';

        $this->mergeLogTables();

        $sql = file_get_contents(Migration::getMigrationResourcesPath('0.2.2') . '/db.sql');

        $sql = str_replace('###EVENT_REGISTRATION_TABLE###', $eventRegistrationTable, $sql);
        $sql = str_replace('###CHARSET###', $charset_collate, $sql);
        $sql = str_replace('###PREFIX###', $wpdb->base_prefix, $sql);

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // dbDelta problems: https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
        dbDelta($sql);

        $error = $wpdb->last_error;

        if (!empty($error)) {
            throw new MigrationFailedException('Migration failed during ALTER TABLE with error "' . $error . '"');
        }

        $migratedRegistrations = 0;

        // Update our new columns with data from event registration log
        $registrations = $this->eventRegistrationRepository->findAll();
        foreach ($registrations as $registration) {
            $registration->setCreateDate($this->determineCreatedDate($registration));
            $registration->setModifyDate($this->determineModifiedDate($registration));
            $registration->setDeleteDate($this->determineDeletedDate($registration));

            if (!empty($registration->getModifyDate())) {
                $data = $this->eventRegistrationMapper->toSingleArray($registration);
                $this->eventRegistrationRepository->update(
                    $data,
                    [
                        'event_id' => $registration->getEventId(),
                        'user_id' => $registration->getUserId(),
                    ]
                );
                $migratedRegistrations++;
            }
        }

        $this->resultMessage .= 'Added DateTime fields to ' . $migratedRegistrations . ' registrations<br>';
    }

    /**
     * Merge our event registration log tables (ems_event_registration_log and log_ems_eventregistration)
     */
    protected function mergeLogTables()
    {
        global $wpdb;

        $logEntriesMigrated = 0;
        $legacyLogEntries = $wpdb->get_results(
            'SELECT * FROM ' . $this->dbPrefix . 'ems_event_registration_log',
            ARRAY_A
        );


        foreach ($legacyLogEntries as $legacyLogEntry) {
            $data = [
                'time' => $legacyLogEntry['time'],
                'type' => EventRegistrationLog::LOG_LEVEL_INFO,
                'message' => $legacyLogEntry['message'],
                'event' => $legacyLogEntry['event'],
                'user' => $legacyLogEntry['user'],
            ];

            // Avoid duplicates, if migration is exectued multiple times
            $sql = "SELECT `time` FROM {$this->dbPrefix}log_ems_eventregistration WHERE `event` = {$legacyLogEntry['event']}  AND `user` = {$legacyLogEntry['user']} AND `time` = '{$legacyLogEntry['time']}' AND `message` = '{$legacyLogEntry['message']}'";
            $exists = $wpdb->get_results($sql, ARRAY_A);

            if (empty($exists)) {
                $wpdb->insert($this->dbPrefix . 'log_ems_eventregistration', $data);
                $logEntriesMigrated++;
            }
        }

        $this->resultMessage .= 'Migrated ' . $logEntriesMigrated . ' log entries from legacy event registration log<br>';
    }

    protected function determineModifiedDate(EventRegistration $eventRegistration): ?\DateTime
    {
        $qb = $this->db->createQueryBuilder();
        $lastEntry = $qb
            ->select('*')
            ->from($this->eventRegistrationLogTable)
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('event', $eventRegistration->getEventId()),
                    $qb->expr()->eq('user', $eventRegistration->getUserId()),
                    $qb->expr()->orX(
                        $qb->expr()->eq('message', $qb->createNamedParameter('Added event registration.')),
                        $qb->expr()->eq('message', $qb->createNamedParameter('Deleted event registration.'))
                    )
                )
            )
            ->orderBy('id', 'DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if (!empty($lastEntry)) {
            return Carbon::parse($lastEntry['time']);
        }
        return null;
    }

    protected function determineCreatedDate(EventRegistration $eventRegistration): ?\DateTime
    {
        $qb = $this->db->createQueryBuilder();
        $lastEntry = $qb
            ->select('*')
            ->from($this->eventRegistrationLogTable)
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('event', $eventRegistration->getEventId()),
                    $qb->expr()->eq('user', $eventRegistration->getUserId()),
                    $qb->expr()->eq('message', $qb->createNamedParameter('Added event registration.'))
                )
            )
            ->orderBy('id', 'DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if (!empty($lastEntry)) {
            return Carbon::parse($lastEntry['time']);
        }
        return null;
    }

    protected function determineDeletedDate(EventRegistration $eventRegistration): ?\DateTime
    {
        $qb = $this->db->createQueryBuilder();
        $lastEntry = $qb
            ->select('*')
            ->from($this->eventRegistrationLogTable)
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('event', $eventRegistration->getEventId()),
                    $qb->expr()->eq('user', $eventRegistration->getUserId()),
                    $qb->expr()->eq('message', $qb->createNamedParameter('Deleted event registration.'))
                )
            )
            ->orderBy('id', 'DESC')
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if (!empty($lastEntry)) {
            return Carbon::parse($lastEntry['time']);
        }
        return null;
    }
}
