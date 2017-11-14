<?php
namespace BIT\EMS\Migration\Version;

use BIT\EMS\Exception\MigrationFailedException;
use BIT\EMS\Migration\Migration;
use BIT\EMS\Migration\MigrationInterface;

/**
 * @author Christoph Bessei
 */
class Migration_0_2_0 implements MigrationInterface
{
    /**
     * @var  string
     */
    protected $resultMessage;

    public function getDescription(): string
    {
        return 'Moved event registrations to own database table';
    }

    public function getResultMessage(): string
    {
        return $this->resultMessage;
    }

    public function run()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $eventRegistrationTable = $wpdb->base_prefix . \Ems_Conf::PREFIX . 'event_registration';

        $sql = file_get_contents(Migration::getMigrationResourcesPath('0.2.0') . '/db.sql');

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

        // Check if there are already entries in event registration table. Skip the data migration step in this case
        $rowcount = (int)$wpdb->get_var("SELECT COUNT(*) FROM " . $eventRegistrationTable);

        $updatedEntries = 0;
        if (0 === $rowcount) {
            // Migrate registrations
            $registrations = get_option('ems_event_registration');
            /** @var \Ems_Event_Registration $registration */
            foreach ($registrations ?? [] as $registration) {
                $row = [
                    'user_id' => $registration->get_user_id(),
                    'event_id' => $registration->get_event_post_id(),
                    'data' => json_encode($registration->get_data()),
                ];

                $wpdb->insert($eventRegistrationTable, $row);
                $error = $wpdb->last_error;
                if (!empty($error)) {
                    // Clean up and throw exception
                    $wpdb->delete($eventRegistrationTable, []);
                    throw new MigrationFailedException('Migration failed during INSERT with error "' . $error . '"');
                }
                $updatedEntries++;
            }
        }

        $this->resultMessage = 'Migrated ' . $updatedEntries . ' registrations.';
    }
}
