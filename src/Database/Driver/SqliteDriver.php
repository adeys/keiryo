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

    /**
     * @inheritDoc
     */
    public function quoteTableName(string $table): string
    {
        return $table == '*'
            ? $table
            : '`' . $table . '`';
    }

    /**
     * @inheritDoc
     */
    public function quoteColumnName(string $column): string
    {
        return $column == '*'
            ? $column
            : '`' . $column . '`';
    }

    /**
     * @inheritDoc
     */
    public function quoteSingle($value)
    {
        return is_int($value) ? $value : '`' . $value . '`';
    }
}
