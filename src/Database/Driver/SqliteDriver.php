<?php

namespace Keiryo\Database\Driver;

use PDO;
use PDOException;

class SqliteDriver extends AbstractDriver
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Sqlite driver implementation
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        try {
            $this->connect();
        } catch (PDOException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        try {
            $path = $this->options['database'];
            $dsn = "sqlite:$path";
            $this->pdo = new PDO($dsn);
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
