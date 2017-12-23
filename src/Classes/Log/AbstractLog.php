<?php
namespace BIT\EMS\Log;

use BIT\FUM\Utility\StringUtility;
use ReflectionClass;

/**
 * @author Christoph Bessei
 */
abstract class AbstractLog
{
    const LOG_LEVEL_INFO = 100;

    const LOG_LEVEL_ERROR = 400;

    /**
     * @var  string
     */
    protected $table;

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var array
     */
    protected $additionalLogFields = [];

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $this->determineTableName();
    }

    public function createTable()
    {
        $fields = [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'type' => 'int(11) NOT NULL',
            'time' => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
            'message' => 'TEXT',
        ];

        if (!empty($this->additionalLogFields)) {
            $fields = array_merge($fields, $this->additionalLogFields);
        }

        $sql = "CREATE TABLE " . $this->table . " (" . PHP_EOL;

        foreach ($fields as $name => $type) {
            $sql .= $name . " " . $type . ',' . PHP_EOL;
        }

        $sql .= "PRIMARY KEY id (id)" . PHP_EOL;
        $sql .= ") " . $this->wpdb->get_charset_collate() . ";";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Determine name of log table from class name
     *
     * @return string
     */
    protected function determineTableName(): string
    {
        $reflect = new ReflectionClass($this);
        $table = $this->wpdb->prefix . 'log_' . \Ems_Conf::PREFIX . strtolower($reflect->getShortName());

        try {
            // Remove postfix log
            if (StringUtility::endsWith($table, 'log')) {
                $table = substr($table, 0, strlen($table) - 3);
            }
        } catch (\InvalidArgumentException $e) {

        }

        return $table;
    }

    /**
     * @param int $type One of the LOG_LEVEL_* constants
     * @param string $msg
     * @param array $additionalData
     */
    protected function insert(int $type, string $msg, array $additionalData = [])
    {
        $data = ['time' => current_time('mysql'), 'type' => $type, 'message' => $msg];

        if (!empty($additionalData)) {
            foreach ($additionalData as $field => $value) {
                $data[$field] = $value;
            }
        }

        if (!$this->wpdb->insert($this->table, $data)) {
            $this->createTable();
            $this->wpdb->insert($this->table, $data);
        }
    }
}
