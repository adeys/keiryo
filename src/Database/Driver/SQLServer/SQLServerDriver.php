<?php
/**
 * Simplex, Core Components
 *
 * @author Wolfy-J
 */

namespace Simplex\Database\Driver\SQLServer;

use PDO;
use Simplex\Database\DatabaseInterface;
use Simplex\Database\Driver\AbstractDriver;
use Simplex\Database\Driver\HandlerInterface;
use Simplex\Database\Driver\SQLServer\Schema\SQLServerTable;
use Simplex\Database\Exception\DriverException;
use Simplex\Database\Exception\StatementException;

class SQLServerDriver extends AbstractDriver
{
    protected const TYPE               = DatabaseInterface::SQL_SERVER;
    protected const TABLE_SCHEMA_CLASS = SQLServerTable::class;
    protected const QUERY_COMPILER     = SQLServerCompiler::class;
    protected const DATETIME           = 'Y-m-d\TH:i:s.000';

    /**
     * @var array
     */
    protected $pdoOptions = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * {@inheritdoc}
     *
     * @throws DriverException
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if ((int)$this->getPDO()->getAttribute(\PDO::ATTR_SERVER_VERSION) < 12) {
            throw new DriverException("SQLServer driver supports only 12+ version of SQLServer");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function identifier(string $identifier): string
    {
        return $identifier == '*' ? '*' : '[' . str_replace('[', '[[', $identifier) . ']';
    }

    /**
     * {@inheritdoc}
     */
    public function tableNames(): array
    {
        $query = "SELECT [table_name] FROM [information_schema].[tables] WHERE [table_type] = 'BASE TABLE'";

        $tables = [];
        foreach ($this->query($query)->fetchAll(PDO::FETCH_NUM) as $name) {
            $tables[] = $name[0];
        }

        return $tables;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable(string $name): bool
    {
        $query = "SELECT COUNT(*) FROM [information_schema].[tables] WHERE [table_type] = 'BASE TABLE' AND [table_name] = ?";

        return (bool)$this->query($query, [$name])->fetchColumn();
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
        return new SQLServerHandler($this);
    }

    /**
     * Create nested transaction save point.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     *
     * @param string $name Savepoint name/id, must not contain spaces and be valid database
     *                     identifier.
     */
    protected function savepointCreate(string $name)
    {
        $this->isProfiling() && $this->getLogger()->info("Transaction: new savepoint 'SVP{$name}'");
        $this->execute('SAVE TRANSACTION ' . $this->identifier("SVP{$name}"));
    }

    /**
     * Commit/release savepoint.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     *
     * @param string $name Savepoint name/id, must not contain spaces and be valid database
     *                     identifier.
     */
    protected function savepointRelease(string $name)
    {
        $this->isProfiling() && $this->getLogger()->info("Transaction: release savepoint 'SVP{$name}'");
        //SQLServer automatically commits nested transactions with parent transaction
    }

    /**
     * Rollback savepoint.
     *
     * @link http://en.wikipedia.org/wiki/Savepoint
     *
     * @param string $name Savepoint name/id, must not contain spaces and be valid database
     *                     identifier.
     */
    protected function savepointRollback(string $name)
    {
        $this->isProfiling() && $this->getLogger()->info("Transaction: rollback savepoint 'SVP{$name}'");
        $this->execute('ROLLBACK TRANSACTION ' . $this->identifier("SVP{$name}"));
    }

    /**
     * {@inheritdoc}
     */
    protected function mapException(\PDOException $exception, string $query): StatementException
    {
        if (
            strpos($exception->getMessage(), '0800') !== false
            || strpos($exception->getMessage(), '080P') !== false
        ) {
            return new StatementException\ConnectionException($exception, $query);
        }

        if ($exception->getCode() == 23000) {
            return new StatementException\ConstrainException($exception, $query);
        }

        return new StatementException($exception, $query);
    }
}