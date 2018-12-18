<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Database\Driver\MySQL;

use PDO;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\Driver\AbstractDriver;
use Spiral\Database\Driver\HandlerInterface;
use Spiral\Database\Driver\MySQL\Schema\MySQLTable;
use Spiral\Database\Exception\StatementException;

/**
 * Talks to mysql databases.
 */
class MySQLDriver extends AbstractDriver
{
    protected const TYPE               = DatabaseInterface::MYSQL;
    protected const TABLE_SCHEMA_CLASS = MySQLTable::class;
    protected const QUERY_COMPILER     = MySQLCompiler::class;

    /**
     * {@inheritdoc}
     */
    protected $pdoOptions = [
        PDO::ATTR_CASE               => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"',
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function identifier(string $identifier): string
    {
        return $identifier == '*' ? '*' : '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * {@inheritdoc}
     */
    public function tableNames(): array
    {
        $result = [];
        foreach ($this->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM) as $row) {
            $result[] = $row[0];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable(string $name): bool
    {
        $query = "SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema` = ? AND `table_name` = ?";

        return (bool)$this->query($query, [$this->getSource(), $name])->fetchColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function eraseData(string $table)
    {
        $this->execute("TRUNCATE TABLE {$this->identifier($table)}");
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): HandlerInterface
    {
        return new MySQLHandler($this);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://dev.mysql.com/doc/refman/5.6/en/error-messages-client.html#error_cr_conn_host_error
     */
    protected function mapException(\PDOException $exception, string $query): StatementException
    {
        if ($exception->getCode() == 23000) {
            return new StatementException\ConstrainException($exception, $query);
        }

        if ($exception->getCode() > 2000) {
            return new StatementException\ConnectionException($exception, $query);
        }

        return new StatementException($exception, $query);
    }
}