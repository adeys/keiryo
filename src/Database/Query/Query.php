<?php
/**
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/query-builder
 */

namespace Keiryo\Database\Query;

use PDO;

class Query
{
    /**
     * @var string
     */
    protected $sql;

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * Query constructor.
     * @param $sql
     * @param array $bindings
     * @param PDO $pdo
     */
    public function __construct($sql, array $bindings)
    {
        $this->sql = (string)trim($sql);
        $this->bindings = $bindings;
    }

    /**
     * @param PDO $pdo
     * @return string|string[]|null
     */
    public function getRawSql(PDO $pdo)
    {
        return $this->interpolateQuery($this->sql, $this->bindings, $pdo);
    }

    /**
     * See: http://stackoverflow.com/a/1376838/656489
     * @param string $query
     * @param array $params
     * @param PDO $pdo
     * @return string|string[]|null
     */
    protected function interpolateQuery(string $query, array $params, PDO $pdo)
    {
        $keys = array();
        $values = $params;

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_string($value)) {
                $values[$key] = $pdo->quote($value);
            }

            if (is_array($value)) {
                $values[$key] = implode(',', $pdo->quote($value));
            }

            if (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }

        return preg_replace($keys, $values, $query, 1, $count);
    }

    /**
     * @return string|string[]|null
     */
    public function toString()
    {
        return $this->getRawSql();
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
