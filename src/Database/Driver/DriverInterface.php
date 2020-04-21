<?php

namespace Keiryo\Database\Driver;

use PDO;

interface DriverInterface
{
    /**
     * Connects to database
     *
     * @return mixed
     */
    public function connect();

    /**
     * Gets the PDO instance
     *
     * @return PDO|null
     */
    public function getPdo(): ?PDO;

    /**
     * Quotes the table name
     *
     * @param string $table
     * @return string
     */
    public function quoteTableName(string $table): string;

    /**
     * Quotes the column name
     *
     * @param string $column
     * @return string
     */
    public function quoteColumnName(string $column): string;

    /**
     * Quotes a single value
     *
     * @param $value
     * @return mixed
     */
    public function quoteSingle($value);
}
