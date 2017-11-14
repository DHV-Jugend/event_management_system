<?php
namespace BIT\EMS\Domain\Repository;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;

/**
 * @author Christoph Bessei
 */
class AbstractDatabaseRepository extends AbstractRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var string  string
     */
    protected $tablePrefix;

    /**
     * @var string
     */
    protected $tableWithoutPrefix;


    protected $table;

    /**
     * @var \wpdb
     */
    protected $wpdb;

    public function __construct()
    {
        $this->wpdb = $GLOBALS['wpdb'];

        $config = new Configuration();
        $connectionParams = [
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => DB_HOST,
            'driver' => 'pdo_mysql',
        ];
        $this->db = DriverManager::getConnection($connectionParams, $config);

        $this->tablePrefix = $this->wpdb->base_prefix;

        if (empty($this->table)) {
            $this->table = $this->tablePrefix . $this->tableWithoutPrefix;
        }
    }

    public function insert(array $values, $returnUid = false)
    {
        $qb = $this->createQb();
        $qb->insert($this->table)->values($this->createPreparedValues($qb, $values))->execute();

        if ($returnUid) {
            $qb = $this->createQb();
            // TODO: Load primary key field from schema manager
            //$this->db->getSchemaManager()->listTableColumns()
            // Get uid of inserted entry
            return $qb
                ->select('uid')
                ->from($this->table)
                ->where($this->createMultipleEquals($qb, $values))
                ->orderBy('uid', 'DESC')
                ->execute()
                ->fetchColumn();
        }
        return null;
    }

    public function update(array $values, array $identifier)
    {
        $qb = $this->createQb();
        $qb->update($this->table);
        $this->setMultipleValues($qb, $values);
        $qb->where($this->createMultipleEquals($qb, $identifier))->execute();
    }

    public function findByIdentifier(array $identifier, array $fields = ['*'])
    {
        $qb = $this->createQb();
        $condition = $this->createMultipleEquals($qb, $identifier);
        return $qb->select($fields)->from($this->table)->where($condition)->execute()->fetchAll();
    }

    public function findAll(array $fields = ['*'])
    {
        $qb = $this->createQb();
        return $qb->select($fields)->from($this->table)->execute()->fetchAll();
    }

    public function delete(array $identifier)
    {
        $qb = $this->createQb();
        $qb->delete($this->table)->where($this->createMultipleEquals($qb, $identifier))->execute();
    }

    protected function createEquals(QueryBuilder $qb, $field, $value)
    {
        return $qb->expr()->eq($field, $this->createdTypedNamedParameter($qb, $value));
    }

    protected function createMultipleEquals(QueryBuilder $qb, array $conditions, $connector = 'andX')
    {
        $expressions = [];
        foreach ($conditions as $field => $value) {
            $expressions[] = $this->createEquals($qb, $field, $value);
        }

        $expr = $qb->expr();
        return call_user_func_array([$expr, $connector], $expressions);
    }

    protected function setMultipleValues(QueryBuilder $qb, array $values)
    {
        foreach ($values as $field => $value) {
            $qb->set($field, $this->createdTypedNamedParameter($qb, $value));
        }
        return $qb;
    }

    protected function createPreparedValues(QueryBuilder $qb, array $values)
    {
        $preparedValues = [];
        foreach ($values as $field => $value) {
            $preparedValues[$field] = $this->createdTypedNamedParameter($qb, $value);
        }
        return $preparedValues;
    }

    /**
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $qb
     * @param $value
     * @return string
     */
    protected function createdTypedNamedParameter(QueryBuilder $qb, $value)
    {
        if ($value instanceof \DateTimeImmutable) {
            return $qb->createNamedParameter($value, Type::DATE_IMMUTABLE);
        }

        if ($value instanceof \DateTimeInterface) {
            return $qb->createNamedParameter($value, Type::DATETIME);
        }

        if (is_int($value)) {
            return $qb->createNamedParameter($value, \PDO::PARAM_INT);
        }

        return $qb->createNamedParameter($value);
    }

    protected function createQb()
    {
        return $this->db->createQueryBuilder();
    }

    protected function getConnection()
    {
        return $this->db;
    }

}
