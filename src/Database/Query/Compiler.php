<?php
/**
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/query-builder
 */

namespace Keiryo\Database\Query;

use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Exception\Exception;

class Compiler
{

    /**
     * @var DatabaseInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tablePrefix;

    /**
     * Compiler constructor.
     * @param DatabaseInterface $connection
     */
    public function __construct(DatabaseInterface $connection)
    {
        $this->connection = $connection;
        $this->tablePrefix = '';
    }

    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $prefix
     * @return Compiler
     */
    public function setTablePrefix(string $prefix): Compiler
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    /**
     * @param string $type
     * @param array $querySegments
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function compile(string $type, array $querySegments, array $data = [])
    {
        $allowedTypes = ['select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly'];

        if (in_array(strtolower($type), $allowedTypes) === false) {
            throw new Exception($type . ' is not a known type.');
        }

        return $this->{$type}($querySegments, 'criteriaOnly' === $type ? $data['bind'] : $data);
    }

    /**
     * @param array $querySegments
     * @return array
     */
    public function select(array $querySegments): array
    {
        if (isset($querySegments['selects']) === false) {
            $querySegments['selects'][] = '*';
        }

        list($wheres, $whereBindings) = $this->buildCriteriaOfType($querySegments, 'wheres', 'WHERE');

        if (isset($querySegments['groupBy'])
            && $groupBy = $this->arrayToString($querySegments['groupBy'], ', ', 'column')) {
            $groupBy = 'GROUP BY ' . $groupBy;
        } else {
            $groupBy = '';
        }

        if (isset($querySegments['orderBy']) && is_array($querySegments['orderBy'])) {
            $orderBy = '';
            foreach ($querySegments['orderBy'] as $order) {
                $orderBy .= $this->quoteTable($order['field']) . ' ' . $order['type'] . ', ';
            }

            if ($orderBy = trim($orderBy, ', ')) {
                $orderBy = 'ORDER BY ' . $orderBy;
            }
        } else {
            $orderBy = '';
        }

        list($havings, $havingBindings) = $this->buildCriteriaOfType($querySegments, 'havings', 'HAVING');

        $segmentsToBuild = [
            'SELECT' . (isset($querySegments['distinct']) ? ' DISTINCT' : ''),
            $this->arrayToString($querySegments['selects'], ', ', 'column')
        ];

        if (isset($querySegments['tables'])) {
            $tables = $this->arrayToString($querySegments['tables'], ', ', 'table');

            if ($tables) {
                $segmentsToBuild[] = 'FROM';
                $segmentsToBuild[] = $tables;
            }
        }

        $segmentsToBuild[] = $this->compileJoin($querySegments);
        $segmentsToBuild[] = $wheres;
        $segmentsToBuild[] = $groupBy;
        $segmentsToBuild[] = $havings;
        $segmentsToBuild[] = $orderBy;
        $segmentsToBuild[] = isset($querySegments['limit']) ? 'LIMIT ' . $querySegments['limit'] : '';
        $segmentsToBuild[] = isset($querySegments['offset']) ? 'OFFSET ' . $querySegments['offset'] : '';

        return [
            'sql' => $this->buildQuerySegment($segmentsToBuild),
            'bindings' => array_merge(
                $whereBindings,
                $havingBindings
            )
        ];
    }

    /**
     * @param array $querySegments
     * @param string $key
     * @param string $type
     * @param bool $bindValues
     * @return array
     */
    protected function buildCriteriaOfType(
        array $querySegments,
        string $key,
        string $type,
        bool $bindValues = true
    ): array
    {
        $criteria = '';
        $bindings = [];

        if (isset($querySegments[$key])) {
            // Get the generic/adapter agnostic criteria string from parent
            list($criteria, $bindings) = $this->buildCriteria($querySegments[$key], $bindValues);

            if ($criteria) {
                $criteria = $type . ' ' . $criteria;
            }
        }

        return [$criteria, $bindings];
    }

    /**
     * @param array $querySegments
     * @param bool $bindValues
     * @return array
     */
    protected function buildCriteria(array $querySegments, bool $bindValues = true): array
    {
        $criteria = '';
        $bindings = [];

        foreach ($querySegments as $segment) {
            $key = is_object($segment['key']) ? $segment['key'] : $this->quoteTable($segment['key']);
            $value = $segment['value'];

            if (is_null($value) && $key instanceof \Closure) {
                $nestedCriteria = new NestedCriteria($this->connection);
                // Call the closure with our new nestedCriteria object
                $key($nestedCriteria);
                // Get the criteria only query from the nestedCriteria object
                $queryObject = $nestedCriteria->getQuery('criteriaOnly');
                // Merge the bindings we get from nestedCriteria object
                $bindings = array_merge($bindings, $queryObject->getBindings());
                // Append the sql we get from the nestedCriteria object
                $criteria .= $segment['joiner'] . ' (' . $queryObject->getSql() . ') ';
            } elseif (is_array($value)) {
                // where_in or between like query
                $criteria .= $segment['joiner'] . ' ' . $key . ' ' . $segment['operator'];

                switch ($segment['operator']) {
                    case 'BETWEEN':
                        $bindings = array_merge($bindings, $segment['value']);
                        $criteria .= ' ? AND ? ';

                        break;
                    default:
                        $placeholders = [];

                        foreach ($segment['value'] as $subValue) {
                            $placeholders[] = '?';
                            $bindings[] = $subValue;
                        }

                        $criteria .= ' (' . implode(', ', $placeholders) . ') ';

                        break;
                }
            } elseif ($value instanceof Raw) {
                $criteria .= $segment['joiner'] . ' ' . $key . ' ' . $segment['operator'] . ' ' . $value . ' ';
                $bindings = array_merge($bindings, $value->getBindings());
            } else {
                if (!$bindValues) {
                    $value = is_null($value) ? $value : $this->quote($value);
                    $criteria .= $segment['joiner'] . ' ' . $key . ' ' . $segment['operator'] . ' ' . $value . ' ';
                } elseif ($segment['key'] instanceof Raw) {
                    if ($value === null) {
                        $criteria .= $segment['joiner'] . ' ' . $key . ' ';
                        $bindings = array_merge($bindings, $segment['key']->getBindings());
                    } else {
                        $criteria .= $segment['joiner'] . ' ' . $key . ' ' . $segment['operator'] . ' ' . $value . ' ';
                    }
                } else {
                    $valuePlaceholder = '?';
                    $bindings[] = $value;
                    $criteria .= $segment['joiner'] . ' ' . $key . ' ' . $segment['operator'] . ' ' . $valuePlaceholder . ' ';
                }
            }
        }

        return [
            // Clear all white spaces, ands and ors from beginning and white spaces from both ends
            trim(preg_replace("/^(AND|OR)?/i", '', $criteria)),
            $bindings
        ];
    }

    /**
     * @param string|Raw|\Closure $value
     * @return string|\Closure
     */
    public function quoteTable($value)
    {
        if ($value instanceof Raw) {
            return (string)$value;
        } elseif ($value instanceof \Closure) {
            return $value;
        }

        if (strpos($value, '.')) {
            $segments = explode('.', $value, 2);
            $segments[0] = $this->quoteTableName($segments[0]);
            $segments[1] = $this->quoteColumnName($segments[1]);

            return implode('.', $segments);
        } else {
            return $this->quoteTableName($value);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteTableName(string $name): string
    {
        return $this->connection
            ->getDriver()
            ->quoteTableName($name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteColumnName(string $name): string
    {
        return $this->connection
            ->getDriver()
            ->quoteColumnName($name);
    }

    /**
     * @param string|Raw|\Closure $value
     * @return int|string|\Closure
     */
    public function quote($value)
    {
        if ($value instanceof Raw) {
            return (string)$value;
        } elseif ($value instanceof \Closure) {
            return $value;
        }

        if (strpos($value, '.')) {
            $segments = [];

            foreach (explode('.', $value, 2) as $val) {
                $segments[] = $this->quoteSingle($val);
            }

            return implode('.', $segments);
        } else {
            return $this->quoteSingle($value);
        }
    }

    /**
     * @param int|string $value
     * @return int
     */
    public function quoteSingle($value)
    {
        return $this->connection
            ->getDriver()
            ->quoteSingle($value);
    }

    /**
     * @param array $data
     * @param string $glue
     * @param string|null $quote
     * @return string
     */
    protected function arrayToString(array $data, string $glue, ?string $quote = 'value'): string
    {
        $elements = [];

        foreach ($data as $key => $val) {
            if (is_int($val) === false) {
                if ($quote === 'table' || $quote === 'column') {
                    $val = $this->quoteTable($val);
                } elseif ($quote === 'value') {
                    $val = $this->quote($val);
                }
            }

            $elements[] = $val;
        }

        return implode($glue, $elements);
    }

    /**
     * @param array $querySegments
     * @return string
     */
    protected function compileJoin(array $querySegments): string
    {
        $sql = '';

        if (isset($querySegments['joins']) === false || is_array($querySegments['joins']) === false) {
            return $sql;
        }

        foreach ($querySegments['joins'] as $joinArr) {
            if (is_array($joinArr['table'])) {
                $table = $this->quoteTable($joinArr['table'][0]) . ' AS ' . $this->quoteTable($joinArr['table'][1]);
            } else {
                $table = $joinArr['table'] instanceof Raw ? (string)$joinArr['table'] : $this->quoteTable($joinArr['table']);
            }

            /** @var JoinBuilder $joinBuilder */
            $joinBuilder = $joinArr['joinBuilder'];

            $sqlArr = [
                $sql,
                strtoupper($joinArr['type']),
                'JOIN',
                $table,
                'ON',
                $joinBuilder->getQuery('criteriaOnly', ['bind' => false])->getSql()
            ];

            $sql = $this->buildQuerySegment($sqlArr);
        }

        return $sql;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function buildQuerySegment(array $data): string
    {
        $string = '';

        foreach ($data as $val) {
            $value = trim($val);

            if ($value) {
                $string = trim($string) . ' ' . $value;
            }
        }

        return $string;
    }

    /**
     * @param array $querySegments
     * @param bool $binds
     * @return array
     */
    public function criteriaOnly(array $querySegments, bool $binds = true): array
    {
        $sql = '';
        $bindings = [];

        if (isset($querySegments['criteria']) === false) {
            return [
                'sql' => $sql,
                'bindings' => $bindings
            ];
        } else {
            list($sql, $bindings) = $this->buildCriteria($querySegments['criteria'], $binds);

            return [
                'sql' => $sql,
                'bindings' => $bindings
            ];
        }
    }

    /**
     * @param array $querySegments
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function insert(array $querySegments, array $data): array
    {
        return $this->doInsert($querySegments, $data, 'INSERT');
    }

    /**
     * @param array $querySegments
     * @param array $data
     * @param string $type
     * @return array
     * @throws Exception
     */
    private function doInsert(array $querySegments, array $data, string $type): array
    {
        if (!isset($querySegments['tables'])) {
            throw new Exception('No table given.');
        }

        $table = end($querySegments['tables']);

        $bindings = [];
        $keys = [];
        $values = [];
        $length = 0;

        if (isset($data[0])) {
            // Batch insert
            $keys = array_keys($data[0]);
            foreach ($data as $entry) {
                $length++;
                $bindings = array_merge($bindings, array_values($entry));
            }

            $values = array_fill(0, count($keys), '?');
        } else {
            foreach ($data as $key => $value) {
                $keys[] = $key;

                if ($value instanceof Raw) {
                    $values[] = (string)$value;
                } else {
                    $values[] = ':' . $key;
                    $bindings[':' . $key] = $value;
                }
            }
            $length++;
        }


        $segmentsToBuild = [
            $type . ' INTO',
            $this->quoteTable($table),
            '(' . $this->arrayToString($keys, ', ', 'column') . ')',
            'VALUES',
            implode(', ', array_fill(0, $length, '(' . $this->arrayToString($values, ', ', null) . ')')),
        ];

        if (isset($querySegments['onduplicate'])) {
            if (count($querySegments['onduplicate']) < 1) {
                throw new Exception('No data given.');
            }

            list($updateStatement, $updateBindings) = $this->getUpdateStatement($querySegments['onduplicate']);

            $segmentsToBuild[] = 'ON DUPLICATE KEY UPDATE ' . $updateStatement;

            $bindings = array_merge($bindings, $updateBindings);
        }

        return [
            'sql' => $this->buildQuerySegment($segmentsToBuild),
            'bindings' => $bindings
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getUpdateStatement(array $data): array
    {
        $bindings = [];
        $segment = '';

        foreach ($data as $key => $value) {
            if ($value instanceof Raw) {
                $segment .= $this->quoteColumnName($key) . ' = ' . $value . ', ';
            } else {
                $segment .= $this->quoteColumnName($key) . ' = ? , ';
                $bindings[] = $value;
            }
        }

        return [trim($segment, ', '), $bindings];
    }

    /**
     * @param array $querySegments
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function insertIgnore(array $querySegments, array $data): array
    {
        return $this->doInsert($querySegments, $data, 'INSERT IGNORE');
    }

    /**
     * @param array $querySegments
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function replace(array $querySegments, array $data): array
    {
        return $this->doInsert($querySegments, $data, 'REPLACE');
    }

    /**
     * @param array $querySegments
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(array $querySegments, array $data): array
    {
        if (isset($querySegments['tables']) === false) {
            throw new Exception('No table given.');
        } elseif (count($data) < 1) {
            throw new Exception('No data given.');
        }

        $table = end($querySegments['tables']);

        list($updates, $bindings) = $this->getUpdateStatement($data);

        list($wheres, $whereBindings) = $this->buildCriteriaOfType($querySegments, 'wheres', 'WHERE');

        $limit = isset($querySegments['limit']) ? 'LIMIT ' . $querySegments['limit'] : '';

        $segmentsToBuild = [
            'UPDATE',
            $this->quoteTable($table),
            'SET ' . $updates,
            $wheres,
            $limit
        ];

        return [
            'sql' => $this->buildQuerySegment($segmentsToBuild),
            'bindings' => array_merge($bindings, $whereBindings)
        ];
    }

    /**
     * @param array $querySegments
     * @return array
     * @throws Exception
     */
    public function delete(array $querySegments): array
    {
        if (isset($querySegments['tables']) === false) {
            throw new Exception('No table given.');
        }

        $table = end($querySegments['tables']);

        list($wheres, $whereBindings) = $this->buildCriteriaOfType($querySegments, 'wheres', 'WHERE');

        //$limit = isset($querySegments['limit']) ? 'LIMIT ' . $querySegments['limit'] : '';

        $segmentsToBuild = ['DELETE FROM', $this->quoteTable($table), $wheres];

        return [
            'sql' => $this->buildQuerySegment($segmentsToBuild),
            'bindings' => $whereBindings
        ];
    }

    /**
     * @param mixed $value
     * @return string|mixed
     */
    public function addTablePrefix($value)
    {
        if (is_null($this->tablePrefix)) {
            return $value;
        }

        if (is_string($value) === false) {
            return $value;
        }

        return $this->tablePrefix . $value;
    }
}
