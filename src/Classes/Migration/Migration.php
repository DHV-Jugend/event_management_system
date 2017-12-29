<?php
namespace BIT\EMS\Migration;

use BIT\EMS\Migration\Version\Migration_0_2_0;
use BIT\EMS\Migration\Version\Migration_0_2_1;
use BIT\EMS\Migration\Version\Migration_0_2_2;

/**
 * @author Christoph Bessei
 */
class Migration
{
    const MIGRATION_VERSION_KEY = \Ems_Conf::PREFIX . 'migration_version';

    protected static $migrations = [
        '0.2.0' => Migration_0_2_0::class,
        '0.2.1' => Migration_0_2_1::class,
        '0.2.2' => Migration_0_2_2::class,
    ];

    public static function run()
    {
        try {
            $migrations = [];

            $dbVersion = get_option(static::MIGRATION_VERSION_KEY);
            if (empty($dbVersion)) {
                // No dbVersion set, we migrate from a version without DB migration. Just execute all migrations
                foreach (static::$migrations as $migrationVersion => $migration) {
                    /** @var \BIT\EMS\Migration\MigrationInterface $migration */
                    $migration = new $migration();
                    $migration->run();
                    $migrations[] = $migration->getDescription() . ': ' . $migration->getResultMessage();
                    // Update migration level
                    update_option(static::MIGRATION_VERSION_KEY, $migrationVersion);

                }
            } else {
                // dbVersion is set, execute all migrations which are greater then dbVersion
                foreach (static::$migrations as $migrationVersion => $migration) {
                    if (version_compare($migrationVersion, $dbVersion, '>')) {
                        $migration = new $migration();
                        $migration->run();
                        $migrations[] = $migration->getDescription() . ': ' . $migration->getResultMessage();
                        // Update migration level
                        update_option(static::MIGRATION_VERSION_KEY, $migrationVersion);
                    }
                }
            }
            if (!empty($migrations)) {
                static::showSuccessNotice($migrations);
            }
        } catch (\Throwable $e) {
            static::showErrorNotice($e->getMessage());
        }
    }

    public static function getMigrationResourcesPath(string $version): string
    {
        return \Event_Management_System::getPluginPath() . '/resources/migration/' . $version;
    }

    /**
     * @param string[] $entries
     */
    protected static function showSuccessNotice(array $entries)
    {
        add_action(
            'admin_notices',
            function () use ($entries) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Migration to new version of event management system was successful:</p>
                    <ul>
                        <?php
                        foreach ($entries as $entry) {
                            echo '<li>' . $entry . '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
        );
    }

    protected static function showErrorNotice(string $msg)
    {
        add_action(
            'admin_notices',
            function () use ($msg) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Migration to new version of event management system failed:<br>
                        <?php echo $msg; ?></p>
                </div>
                <?php
            }
        );
    }
}
