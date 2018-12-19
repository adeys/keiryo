<?php
/**
 * Simplex Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Simplex\Database;

/**
 * Represents single table index associated with set of columns.
 */
interface IndexInterface
{
    /**
     * Get element name (unquoted).
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if index is unique.
     *
     * @return bool
     */
    public function isUnique(): bool;

    /**
     * Column names used to form index.
     *
     * @return array
     */
    public function getColumns(): array;
}
