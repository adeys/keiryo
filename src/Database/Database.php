<?php

namespace Keiryo\Database;

use PDO;
use PDOException;
use PDOStatement;
use Keiryo\Database\Driver\DriverInterface;
use Keiryo\Database\Query\Builder;

class Database implements DatabaseInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var PDOStatement
     */
    protected $statement;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
        
        if (!($pdo = $this->driver->getPdo())) {
            $this->driver->connect();
            $pdo = $this->driver->getPdo();
        }
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $statement, array $bindings = []): PDOStatement
    {
        if (!empty($bindings)) {
            $this->statement = $this->pdo->prepare($statement);
            $this->statement->execute($bindings);
            return $this->statement;
        }

        return $this->statement = $this->pdo->query($statement);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $statement, array $bindings = []): PDOStatement
    {
        try {
            $this->statement = $this->pdo->prepare($statement);
            $this->statement->execute($bindings);
            return $this->statement;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(\Closure $transaction, /*?object*/ $bound = null): bool
    {
        try {
            $this->pdo->beginTransaction();
            $transaction($bound ?? $this);
            return $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        return $this->statement->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(): array
    {
        return $this->statement->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryBuilder(): Builder
    {
        return new Builder($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}
