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
            throw new MigrationFailedException('Migration failed with error ' . $error);
        }

        // Check if there are already entries in event registration table. Skip the data migration step in this case
        $rowcount = (int)$wpdb->get_var("SELECT COUNT(*) FROM " . $eventRegistrationTable);

        if (0 === $rowcount) {
            // Migrate registrations
            $registrations = get_option('ems_event_registration');
            /** @var \Ems_Event_Registration $registration */
            foreach ($registrations ?? [] as $registration) {
                $row = [
                    'user_id' => $registration->get_user_id(),
                    'event_id' => $registration->get_event_post_id(),
                    'fum_aircraft' => $registration->get_data()['fum_aircraft'],
                    'fum_search_ride' => $registration->get_data()['fum_search_ride'],
                    'fum_offer_ride' => $registration->get_data()['fum_offer_ride'],
                    'deleted' => false,
                ];

                $wpdb->insert($eventRegistrationTable, $row);
            }
        }
    }
}
