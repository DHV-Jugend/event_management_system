<?php

/**
 * @author Christoph Bessei
 * @version
 */
abstract class Ems_Log
{

    /**
     * @var array
     */
    protected static $additionalLogFields = [];

    /**
     * @return string
     */
    protected static function getTableName()
    {
        global $wpdb;
        return $wpdb->prefix . strtolower(get_called_class()) . '_log';
    }

    public static function createTables()
    {
        global $wpdb;

        $fields = [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT',
            'time' => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
            'message' => 'TEXT',
        ];

        if (!empty(static::$additionalLogFields)) {
            $fields = array_merge($fields, static::$additionalLogFields);
        }

        $sql = "CREATE TABLE " . static::getTableName() . " (" . PHP_EOL;
        foreach ($fields as $name => $type) {
            $sql .= $name . " " . $type . PHP_EOL;
        }
        $sql .= "PRIMARY KEY id (id)" . PHP_EOL;
        $sql .= ") " . $wpdb->get_charset_collate() . ";";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }


    /**
     * @param string $msg
     * @param array $additionalData
     */
    public static function log($msg, array $additionalData = [])
    {
        global $wpdb;

        $data = ['time' => current_time('mysql'), 'message' => $msg];

        if (!empty($additionalData)) {
            foreach ($additionalData as $field => $value) {
                $data[$field] = $value;
            }
        }

        $wpdb->insert(static::getTableName(), $data);
    }
}