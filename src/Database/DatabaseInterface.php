<?php

namespace Keiryo\Database;

use PDOStatement;
use Keiryo\Database\Driver\DriverInterface;
use Keiryo\Database\Query\Builder;

interface DatabaseInterface
{
    /**
     * Makes a query against database
     *
     * @param string $statement
     * @param array $bindings
     * @return PDOStatement
     */
    public function query(string $statement, array $bindings = []): PDOStatement;

    /**
     * Executes a query against database
     *
     * @param string $statement
     * @param array $bindings
     * @return PDOStatement
     */
    public function execute(string $statement, array $bindings = []): PDOStatement;

    /**
     * Wraps database operations as a transaction
     *
     * @param \Closure $transaction
     * @param object|null $bound Optional param to bind to closure
     * @return bool
     */
    public function transaction(
        \Closure $transaction, /*?object*/
        $bound = null
    ): bool;

    /**
     * Fetch a single entry from a query
     *
     * @return mixed
     */
    public function fetch();

    /**
     * Fetch a collection of entries
     *
     * @return array
     */
    public function fetchAll(): array;

    /**
     * Get last inserted id
     *
     * @return int|string
     */
    public function lastInsertId();

    /**
     * Gets an instance of query builder
     *
     * @return Builder
     */
    public function getQueryBuilder(): Builder;

    /**
     * Get used driver
     *
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface;
}
