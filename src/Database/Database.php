<?php

namespace Simplex\Database;

use PDO;
use PDOStatement;
use PDOException;
use Simplex\Database\Driver\DriverInterface;

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

    public function __construct(Configuration $config)
    {
        $this->driver = $config->getDriver();
        
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
    public function execute(string $statement, array $bindings = []): bool
    {
        try {
            $this->statement = $this->pdo->prepare($statement);
            return $this->statement->execute($bindings);
        } catch (PDOException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(\Closure $transaction): bool
    {
        try {
            $this->pdo->beginTransaction();
            $transaction($this);
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
}
