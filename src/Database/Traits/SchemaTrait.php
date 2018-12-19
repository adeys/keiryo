<?php
/**
 * Simplex Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Simplex\Database\Traits;

use Simplex\Database\ColumnInterface;
use Simplex\Database\ForeignKeyInterface;
use Simplex\Database\IndexInterface;
use Simplex\Database\Schema\AbstractTable;

trait SchemaTrait
{
    /**
     * @inheritdoc
     */
    public function exists(): bool
    {
        return $this->getSchema()->exists();
    }

    /**
     * Array of columns dedicated to primary index. Attention, this methods will ALWAYS return
     * array, even if there is only one primary key.
     *
     * @return array
     */
    public function getPrimaryKeys(): array
    {
        return $this->getSchema()->getPrimaryKeys();
    }

    /**
     * Check if table have specified column.
     *
     * @param string $name Column name.
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        return $this->getSchema()->hasColumn($name);
    }

    /**
     * Get all declared columns.
     *
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        return $this->getSchema()->getColumns();
    }

    /**
     * Check if table has index related to set of provided columns. Columns order does matter!
     *
     * @param array $columns
     * @return bool
     */
    public function hasIndex(array $columns = []): bool
    {
        return $this->getSchema()->hasIndex($columns);
    }

    /**
     * Get all table indexes.
     *
     * @return IndexInterface[]
     */
    public function getIndexes(): array
    {
        return $this->getSchema()->getIndexes();
    }

    /**
     * Check if table has foreign key related to table column.
     *
     * @param string $column Column name.
     * @return bool
     */
    public function hasForeignKey(string $column): bool
    {
        return $this->getSchema()->hasForeignKey($column);
    }

    /**
     * Get all table foreign keys.
     *
     * @return ForeignKeyInterface[]
     */
    public function getForeignKeys(): array
    {
        return $this->getSchema()->getForeignKeys();
    }

    /**
     * Get list of table names current schema depends on, must include every table linked using
     * foreign key or other constraint. Table names MUST include prefixes.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->getSchema()->getDependencies();
    }

    /**
     * Get modifiable table schema.
     *
     * @return AbstractTable
     */
    abstract public function getSchema(): AbstractTable;
}