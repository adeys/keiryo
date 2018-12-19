<?php
/**
 * Simplex Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Simplex\Database\Driver;

use Simplex\Database\Exception\CompilerException;
use Simplex\Database\Injection\FragmentInterface;

interface CompilerInterface
{
    /**
     * Prefix associated with compiler.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Query query identifier, if identified stated as table - table prefix must be added.
     *
     * @param string|FragmentInterface $identifier Identifier can include simple column operations and functions, having
     *                                             "." in it must automatically force table prefix to first value.
     * @param bool                     $isTable    Set to true to let quote method know that identified is related to
     *                                             table name.
     * @return string
     */
    public function quote($identifier, bool $isTable = false): string;

    /**
     * Create insert query using table names, columns and rowsets. Must support both - single and
     * batch inserts.
     *
     * @param string              $table
     * @param array               $columns
     * @param FragmentInterface[] $rowsets Every rowset has to be convertable into string. Raw data not allowed!
     * @return string
     *
     * @throws CompilerException
     */
    public function compileInsert(string $table, array $columns, array $rowsets): string;

    /**
     * Create update statement.
     *
     * @param string $table
     * @param array  $updates
     * @param array  $whereTokens
     * @return string
     *
     * @throws CompilerException
     */
    public function compileUpdate(string $table, array $updates, array $whereTokens = []): string;

    /**
     * Create delete statement.
     *
     * @param string $table
     * @param array  $whereTokens
     * @return string
     *
     * @throws CompilerException
     */
    public function compileDelete(string $table, array $whereTokens = []): string;

    /**
     * Create select statement. Compiler must validly resolve table and column aliases used in
     * conditions and joins.
     *
     * @param array       $fromTables
     * @param bool|string $distinct String only for PostgresSQL.
     * @param array       $columns
     * @param array       $joinTokens
     * @param array       $whereTokens
     * @param array       $havingTokens
     * @param array       $grouping
     * @param array       $ordering
     * @param int         $limit
     * @param int         $offset
     * @param array       $unionTokens
     * @return string
     *
     * @throws CompilerException
     */
    public function compileSelect(
        array $fromTables,
        $distinct,
        array $columns,
        array $joinTokens = [],
        array $whereTokens = [],
        array $havingTokens = [],
        array $grouping = [],
        array $ordering = [],
        int $limit = 0,
        int $offset = 0,
        array $unionTokens = []
    ): string;
}