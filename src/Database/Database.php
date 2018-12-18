<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Database;

use Spiral\Core\Container\InjectableInterface;
use Spiral\Database\Driver\DriverInterface;
use Spiral\Database\Query\DeleteQuery;
use Spiral\Database\Query\InsertQuery;
use Spiral\Database\Query\SelectQuery;
use Spiral\Database\Query\UpdateQuery;

/**
 * Database class is high level abstraction at top of Driver. Databases usually linked to real
 * database or logical portion of database (filtered by prefix).
 */
class Database implements DatabaseInterface, InjectableInterface
{
    public const INJECTOR = DatabaseManager::class;

    // Isolation levels for transactions
    public const ISOLATION_SERIALIZABLE     = DriverInterface::ISOLATION_SERIALIZABLE;
    public const ISOLATION_REPEATABLE_READ  = DriverInterface::ISOLATION_REPEATABLE_READ;
    public const ISOLATION_READ_COMMITTED   = DriverInterface::ISOLATION_READ_COMMITTED;
    public const ISOLATION_READ_UNCOMMITTED = DriverInterface::ISOLATION_READ_UNCOMMITTED;

    /** @var string */
    private $name = '';

    /** @var string */
    private $prefix = '';

    /** @var DriverInterface */
    private $driver;

    /** @var DriverInterface|null */
    private $readDriver;

    /**
     * @param string               $name       Internal database name/id.
     * @param string               $prefix     Default database table prefix, will be used for all
     *                                         table identifiers.
     * @param DriverInterface      $driver     Driver instance responsible for database connection.
     * @param DriverInterface|null $readDriver Read-only driver connection.
     */
    public function __construct(
        string $name,
        string $prefix = '',
        DriverInterface $driver,
        DriverInterface $readDriver = null
    ) {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->driver = $driver;
        $this->readDriver = $readDriver;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->getDriver(self::WRITE)->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(int $type = DatabaseInterface::WRITE): DriverInterface
    {
        if ($type == self::READ && !empty($this->readDriver)) {
            return $this->readDriver;
        }

        return $this->driver;
    }

    /**
     * {@inheritdoc}
     */
    public function withPrefix(string $prefix, bool $add = true): DatabaseInterface
    {
        $database = clone $this;

        if ($add) {
            $database->prefix .= $prefix;
        } else {
            $database->prefix = $prefix;
        }

        return $database;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable(string $name): bool
    {
        return $this->getDriver()->hasTable($this->prefix . $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return Table[]
     */
    public function getTables(): array
    {
        $result = [];
        foreach ($this->getDriver(self::READ)->tableNames() as $table) {
            if ($this->prefix && strpos($table, $this->prefix) !== 0) {
                //Logical partitioning
                continue;
            }

            $result[] = $this->table(substr($table, strlen($this->prefix)));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @return Table
     */
    public function table(string $name): TableInterface
    {
        return new Table($this, $name);
    }

    /**
     * Shortcut to get table abstraction.
     *
     * @param string $name Table name without prefix.
     * @return Table
     */
    public function __get(string $name): Table
    {
        return $this->table($name);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $query, array $parameters = []): int
    {
        return $this->getDriver(self::WRITE)->execute($query, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $query, array $parameters = []): Statement
    {
        return $this->getDriver(self::READ)->query($query, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table = null): InsertQuery
    {
        return $this->getDriver(self::WRITE)->insertQuery($this->prefix, $table);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table = null, array $values = [], array $where = []): UpdateQuery
    {
        return $this->getDriver(self::WRITE)->updateQuery($this->prefix, $table, $where, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $table = null, array $where = []): DeleteQuery
    {
        return $this->getDriver(self::WRITE)->deleteQuery($this->prefix, $table, $where);
    }

    /**
     * {@inheritdoc}
     */
    public function select($columns = '*'): SelectQuery
    {
        $columns = func_get_args();
        if (is_array($columns) && isset($columns[0]) && is_array($columns[0])) {
            //Can be required in some cases while collecting data from Table->select(), stupid bug.
            $columns = $columns[0];
        }

        return $this->getDriver(self::READ)->selectQuery($this->prefix, [], $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(callable $callback, string $isolationLevel = null)
    {
        $this->begin($isolationLevel);

        try {
            $result = call_user_func($callback, $this);
            $this->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function begin(string $isolationLevel = null): bool
    {
        return $this->getDriver(self::WRITE)->beginTransaction($isolationLevel);
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return $this->getDriver(self::WRITE)->commitTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): bool
    {
        return $this->getDriver(self::WRITE)->rollbackTransaction();
    }
}
