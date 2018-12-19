<?php
/**
 * Simplex Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Simplex\Database\Driver\Traits;

use Simplex\Database\Driver\Compiler;
use Simplex\Database\Driver\CompilerInterface;
use Simplex\Database\Query\DeleteQuery;
use Simplex\Database\Query\InsertQuery;
use Simplex\Database\Query\SelectQuery;
use Simplex\Database\Query\UpdateQuery;

/**
 * Provides ability to construct query builders for the driver.
 */
trait BuilderTrait
{
    /**
     * Get InsertQuery builder with driver specific query compiler.
     *
     * @param string      $prefix Database specific table prefix, used to quote table names and
     *                            build aliases.
     * @param string|null $table
     * @return InsertQuery
     */
    public function insertQuery(string $prefix, string $table = null): InsertQuery
    {
        return new InsertQuery($this, $this->getCompiler($prefix), $table);
    }

    /**
     * Get SelectQuery builder with driver specific query compiler.
     *
     * @param string $prefix Database specific table prefix, used to quote table names and build
     *                       aliases.
     * @param array  $from
     * @param array  $columns
     * @return SelectQuery
     */
    public function selectQuery(string $prefix, array $from = [], array $columns = []): SelectQuery
    {
        return new SelectQuery($this, $this->getCompiler($prefix), $from, $columns);
    }

    /**
     * @param string      $prefix
     * @param string|null $from  Database specific table prefix, used to quote table names and
     *                           build aliases.
     * @param array       $where Initial builder parameters.
     * @return DeleteQuery
     */
    public function deleteQuery(string $prefix, string $from = null, array $where = []): DeleteQuery
    {
        return new DeleteQuery($this, $this->getCompiler($prefix), $from, $where);
    }

    /**
     * Get UpdateQuery builder with driver specific query compiler.
     *
     * @param string      $prefix Database specific table prefix, used to quote table names and
     *                            build aliases.
     * @param string|null $table
     * @param array       $where
     * @param array       $values
     * @return UpdateQuery
     */
    public function updateQuery(
        string $prefix,
        string $table = null,
        array $where = [],
        array $values = []
    ): UpdateQuery {
        return new UpdateQuery($this, $this->getCompiler($prefix), $table, $where, $values);
    }

    /**
     * Get instance of Driver specific QueryCompiler.
     *
     * @param string $prefix Database specific table prefix, used to quote table names and build
     *                       aliases.
     *
     * @return Compiler
     */
    abstract public function getCompiler(string $prefix = ''): CompilerInterface;
}