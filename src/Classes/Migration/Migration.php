<?php
namespace BIT\EMS\Migration;

use BIT\EMS\Migration\Version\Migration_0_2_0;

/**
 * @author Christoph Bessei
 */
class Migration
{
    const MIGRATION_VERSION_KEY = \Ems_Conf::PREFIX . 'migration_version';

    protected static $migrations = [
        '0.2.0' => Migration_0_2_0::class,
    ];

    public static function run()
    {
        $dbVersion = get_option(static::MIGRATION_VERSION_KEY);
        if (empty($dbVersion)) {
            // No dbVersion set, we migrate from a version without DB migration. Just execute all migrations
            foreach (static::$migrations as $migrationVersion => $migration) {
                (new $migration())->run();
                // Update migration level
                update_option(static::MIGRATION_VERSION_KEY, $migrationVersion);
            }
        } else {
            // dbVersion is set, execute all migrations which are greater then dbVersion
            foreach (static::$migrations as $migrationVersion => $migration) {
                if (version_compare($migrationVersion, $dbVersion, '>')) {
                    (new $migration())->run();
                    // Update migration level
                    update_option(static::MIGRATION_VERSION_KEY, $migrationVersion);
                }
            }
        }
    }


    public static function getMigrationResourcesPath(string $version): string
    {
        return \Event_Management_System::getPluginPath() . '/resources/migration/' . $version;
    }
}
