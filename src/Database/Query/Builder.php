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
use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Exception\QueryExecutionFailException;

class Builder
{
    /**
     * @var DatabaseInterface
     */
    protected $connection;

    /**
     * @var array
     */
    protected $querySegments = [];

    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @var array
     */
    protected $fetchMode = [PDO::FETCH_OBJ];

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * QueryBuilder constructor.
     * @param DatabaseInterface $connection
     */
    public function __construct(DatabaseInterface $connection)
    {
        $this->setConnection($connection);

        // Set default compiler.
        $this->setCompiler(new Compiler($connection));
    }

    /**
     * @param Compiler $compiler
     * @return $this
     */
    public function setCompiler(Compiler $compiler)
    {
        $this->compiler = $compiler;

        return $this;
    }

    /**
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        return $this->compiler;
    }

    /**
     * @return array
     */
    public function getQuerySegments(): array
    {
        return $this->querySegments;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getQuerySegment($key)
    {
        if (isset($this->querySegments[$key])) {
            return $this->querySegments[$key];
        } else {
            return null;
        }
    }

    /**
     * @param $mode
     * @return Builder
     */
    public function setFetchMode($mode)
    {
        $this->fetchMode = func_get_args();

        return $this;
    }

    /**
     * @return array
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * @param DatabaseInterface $connection
     * @return Builder
     */
    public function setConnection(DatabaseInterface $connection)
    {
        $this->connection = $connection;
        $this->pdo = $connection->getDriver()->getPdo();

        return $this;
    }

    /**
     * @return DatabaseInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string|null $alias
     * @return Builder
     */
    public function newQuery(?string $alias = null)
    {
        return new self($this->connection);
    }

    /**
     * @return Builder
     */
    public function forkQuery()
    {
        return clone $this;
    }

    /**
     * @param $value
     * @param array $bindings
     * @return Raw
     */
    public function raw($value, $bindings = [])
    {
        return new Raw($value, $bindings);
    }

    /**
     * @param string $type
     * @param array $parameters
     * @return Query
     */
    public function getQuery($type = 'select', array $parameters = [])
    {
        $result = $this->compiler->compile($type, $this->querySegments, $parameters);

        return new Query($result['sql'], $result['bindings']);
    }

    /**
     * @param Builder $queryBuilder
     * @param null $alias
     * @return Raw
     */
    public function subQuery(Builder $queryBuilder, $alias = null)
    {
        $sql = '(' . $queryBuilder->getQuery()->getRawSql($this->connection->getDriver()->getPdo()) . ')';

        if ($alias) {
            $sql = $sql . ' AS ' . $alias;
        }

        return $queryBuilder->raw($sql);
    }

    /**
     * @param string $type
     * @param array $parameters
     * @return \PDOStatement
     */
    protected function execute(string $type, array $parameters = []): \PDOStatement
    {
        $query = $this->getQuery($type, $parameters);

        $result = 'select' === $type
            ? $this->connection->query($query->getSql(), $query->getBindings())
            : $this->connection->execute($query->getSql(), $query->getBindings());

        $this->removeQuerySegments();
        return $result;
    }

    /**
     * @return array
     * @throws QueryExecutionFailException
     */
    public function get()
    {
        return $this->execute('select')
            ->fetchAll();
    }

    /**
     * @return mixed
     */
    public function first()
    {
        $this->limit(1);

        return $this->execute('select')
            ->fetch();
    }

    /**
     * @param string $table
     * @param string|null $alias
     * @return Builder
     */
    public function from($table, ?string $alias = null)
    {
        if ($table instanceof Builder) {
            $table = $table->getQuerySegment('tables');
        }

        $table = $alias
            ? $this->raw($this->compiler->quoteTableName($table) . ' AS ' . $this->compiler->quoteTableName($alias))
            : $table;

        $this->replaceQuerySegment('tables', $this->addTablePrefix($table, true));

        return $this;
    }

    /**
     * @param string $table
     * @param string|null $alias
     * @return Builder
     */
    public function table(string $table, ?string $alias = null): self
    {
        return $this->from($table, $alias);
    }

    /**
     * @return Builder
     */
    public function removeTables(): self
    {
        $this->removeQuerySegment('tables');

        return $this;
    }

    /**
     * @param string|array $fields
     * @return Builder
     */
    public function select($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $fields = $this->addTablePrefix($fields);
        $this->addQuerySegment('selects', $fields);

        return $this;
    }

    /**
     * @param $fields
     * @param string|null $alias
     * @return Builder
     */
    public function addSelect($fields, ?string $alias = null): self
    {
        if (is_string($fields) && $alias) {
            $compiler = $this->compiler;
            $fields = $this->raw(
                $compiler->quote($fields) .
                ' AS ' .
                $compiler->quoteTableName($alias)
            );
        }

        return $this->select($fields);
    }

    /**
     * @return Builder
     */
    public function removeSelects(): self
    {
        $this->removeQuerySegment('selects');

        return $this;
    }

    /**
     * @param string|array $fields
     * @return Builder
     */
    public function selectDistinct($fields): self
    {
        $this->select($fields);
        $this->addQuerySegment('distinct', true);

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeSelectDistinct(): self
    {
        $this->removeQuerySegment('distinct');

        return $this;
    }

    /**
     * @param string $column
     * @param bool $execute
     * @return int
     * @throws QueryExecutionFailException
     */
    public function count(string $column = '*')
    {
        $segments = $this->querySegments;

        unset($this->querySegments['orderBy']);
        unset($this->querySegments['limit']);
        unset($this->querySegments['offset']);

        $count = $this->aggregate('COUNT(' . $column . ')');
        $this->querySegments = $segments;

        return $count;
    }

    /**
     * @param string $column
     * @return int
     */
    public function max(string $column): int
    {
        return $this->aggregate('MAX(' . $column . ')');
    }

    /**
     * @param string $column
     * @return int
     */
    public function min(string $column): int
    {
        return $this->aggregate('MIN(' . $column . ')');
    }

    /**
     * @param string $column
     * @return mixed
     */
    public function sum(string $column): int
    {
        return $this->aggregate('SUM(' . $column . ')');
    }

    /**
     * @param string $column
     * @return int
     */
    public function avg(string $column): int
    {
        return $this->aggregate('AVG(' . $column . ')');
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return $this->doInsert($data, 'insert');
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insertIgnore(array $data)
    {
        return $this->doInsert($data, 'insertignore');
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function replace(array $data)
    {
        return $this->doInsert($data, 'replace');
    }

    /**
     * @param array $data
     * @return int
     * @throws QueryExecutionFailException
     */
    public function update($data)
    {
        return $this->execute('update', $data)
            ->rowCount();
    }

    /**
     * @param $data
     * @return $this
     */
    public function onDuplicateKeyUpdate($data)
    {
        $this->addQuerySegment('onduplicate', $data);

        return $this;
    }

    /**
     * @return int
     * @throws QueryExecutionFailException
     */
    public function delete()
    {
        return $this->execute('delete')
            ->rowCount();
    }

    /**
     * @param string $field
     * @return Builder
     */
    public function groupBy(string $field): self
    {
        $this->addQuerySegment('groupBy', $this->addTablePrefix($field));

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeGroupBy(): self
    {
        $this->removeQuerySegment('groupBy');

        return $this;
    }

    /**
     * @param string|array $fields
     * @param string $defaultDirection
     * @return Builder
     */
    public function orderBy($fields, $defaultDirection = 'ASC'): self
    {
        if (is_array($fields) === false) {
            $fields = [$fields];
        }

        foreach ($fields as $key => $value) {
            $field = $key;
            $type = $value;

            if (is_int($key)) {
                $field = $value;
                $type = $defaultDirection;
            }

            if (!$field instanceof Raw) {
                $field = $this->addTablePrefix($field);
            }

            $this->querySegments['orderBy'][] = [
                'field' => $field,
                'type' => $type
            ];
        }

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeOrderBys(): self
    {
        $this->removeQuerySegment('orderBy');

        return $this;
    }

    /**
     * @param int $limit
     * @return Builder
     */
    public function limit(int $limit): self
    {
        $this->querySegments['limit'] = $limit;

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeLimit(): self
    {
        $this->removeQuerySegment('limit');

        return $this;
    }

    /**
     * @param int $offset
     * @return Builder
     */
    public function offset(int $offset): self
    {
        $this->querySegments['offset'] = $offset;

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeOffset(): self
    {
        $this->removeQuerySegment('offset');

        return $this;
    }

    /**
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @param string $joiner
     * @return Builder
     */
    public function having(string $key, string $operator, $value, string $joiner = 'AND'): self
    {
        $this->querySegments['havings'][] = [
            'key' => $this->addTablePrefix($key),
            'operator' => $operator,
            'value' => $value,
            'joiner' => $joiner
        ];

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeHavings(): self
    {
        $this->removeQuerySegment('havings');

        return $this;
    }

    /**
     * @param string $key
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    public function orHaving(string $key, string $operator, $value): self
    {
        return $this->having($key, $operator, $value, 'OR');
    }

    public function andWhere($key, $operator = null, $value = null)
    {
        return $this->where($key, $operator, $value);
    }

    public function where($key, $operator = null, $value = null)
    {
        return $this->handleWhere($key, $operator, $value);
    }

    public function orWhere($key, $operator = null, $value = null)
    {
        return $this->handleWhere($key, $operator, $value, 'OR');
    }

    public function whereNot($key, $operator = null, $value = null)
    {
        return $this->handleWhere($key, $operator, $value, 'AND NOT');
    }

    public function orWhereNot($key, $operator = null, $value = null)
    {
        return $this->handleWhere($key, $operator, $value, 'OR NOT');
    }

    public function whereIn($key, $values)
    {
        return $this->handleWhere($key, 'IN', $values, 'AND');
    }

    public function whereNotIn($key, $values)
    {
        return $this->handleWhere($key, 'NOT IN', $values, 'AND');
    }

    public function orWhereIn($key, $values)
    {
        return $this->handleWhere($key, 'IN', $values, 'OR');
    }

    public function orWhereNotIn($key, $values)
    {
        return $this->handleWhere($key, 'NOT IN', $values, 'OR');
    }

    public function whereBetween($key, $valueFrom, $valueTo)
    {
        return $this->handleWhere($key, 'BETWEEN', [$valueFrom, $valueTo], 'AND');
    }

    public function orWhereBetween($key, $valueFrom, $valueTo)
    {
        return $this->handleWhere($key, 'BETWEEN', [$valueFrom, $valueTo], 'OR');
    }

    public function whereNull($key)
    {
        return $this->handleWhereNull($key);
    }

    public function whereNotNull($key)
    {
        return $this->handleWhereNull($key, 'NOT');
    }

    public function orWhereNull($key)
    {
        return $this->handleWhereNull($key, '', 'or');
    }

    public function orWhereNotNull($key)
    {
        return $this->handleWhereNull($key, 'NOT', 'or');
    }

    /**
     * @return Builder
     */
    public function removeWheres(): self
    {
        $this->removeQuerySegment('wheres');

        return $this;
    }

    /**
     * @param string|array $table
     * @param string|\Closure $key
     * @param string|null $operator
     * @param mixed $value
     * @param string $type
     * @return Builder
     */
    public function join($table, $key, $value = null, string $operator = '=', string $type = 'inner'): self
    {
        if (!$key instanceof \Closure) {
            $key = function (JoinBuilder $joinBuilder) use ($key, $operator, $value) {
                $joinBuilder->on($key, $value, $operator);
            };
        }

        $joinBuilder = new JoinBuilder($this->connection);
        $joinBuilder = &$joinBuilder;

        $key($joinBuilder);

        $this->querySegments['joins'][] = [
            'type' => $type,
            'table' => $this->addTablePrefix($table, true),
            'joinBuilder' => $joinBuilder
        ];

        return $this;
    }

    /**
     * @param string|array $table
     * @param string|\Closure $key
     * @param string|null $operator
     * @param mixed $value
     * @return Builder
     */
    public function leftJoin($table, $key, $value = null, string $operator = '='): self
    {
        return $this->join($table, $key, $operator, $value, 'left');
    }

    /**
     * @param string|array $table
     * @param string|\Closure $key
     * @param string|null $operator
     * @param mixed $value
     * @return Builder
     */
    public function rightJoin($table, $key, $value = null, string $operator = '='): self
    {
        return $this->join($table, $key, $operator, $value, 'right');
    }

    /**
     * @param string|array $table
     * @param string|\Closure $key
     * @param string|null $operator
     * @param mixed $value
     * @return Builder
     */
    public function innerJoin($table, $key, $value = null, string $operator = '='): self
    {
        return $this->join($table, $key, $operator, $value, 'inner');
    }

    /**
     * @return Builder
     */
    public function removeJoins()
    {
        $this->removeQuerySegment('joins');

        return $this;
    }

    public function addTablePrefix($values, $forceAddToAll = false)
    {
        $wasSingleValue = false;

        if (is_array($values) === false) {
            $values = [$values];
            $wasSingleValue = true;
        }

        $result = [];

        foreach ($values as $key => $value) {
            if (is_string($value) === false) {
                $result[$key] = $value;

                continue;
            }

            $target = &$value;

            if (is_int($key) === false) {
                $target = &$key;
            }

            if (strpos($target, '.') === false) {
                if ($target !== '*' && $forceAddToAll) {
                    $target = $this->compiler->addTablePrefix($target);
                }
            } else {
                $target = $this->compiler->addTablePrefix($target);
            }

            $result[$key] = $value;
        }

        return $wasSingleValue ? $result[0] : $result;
    }

    /**
     * @param int|string $value
     * @return int|string
     */
    public function quote($value)
    {
        return $this->compiler->quote($value);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addQuerySegment(string $key, $value)
    {
        if (is_array($value) === false) {
            $value = [$value];
        }

        if (isset($this->querySegments[$key]) === false) {
            $this->querySegments[$key] = $value;
        } else {
            $this->querySegments[$key] = array_merge($this->querySegments[$key], $value);
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return Builder
     */
    public function replaceQuerySegment(string $key, $value): self
    {
        $this->removeQuerySegment($key);
        $this->addQuerySegment($key, $value);

        return $this;
    }

    /**
     * @param string $key
     * @return Builder
     */
    public function removeQuerySegment(string $key): self
    {
        unset($this->querySegments[$key]);

        return $this;
    }

    /**
     * @return Builder
     */
    public function removeQuerySegments(): self
    {
        $table = $this->querySegments['tables'];
        $this->querySegments = [];
        $this->querySegments['tables'] = $table;

        return $this;
    }

    /**
     * @param array $data
     * @param string $type
     * @return int|null
     * @throws QueryExecutionFailException
     */
    protected function doInsert(array $data, string $type)
    {
        $result = $this->execute($type, $data);

        return $result->rowCount() === 1
            ? $this->connection->lastInsertId()
            : null;
    }

    /**
     * @param string $type
     * @return int
     */
    protected function aggregate(string $type)
    {
        $mainSelects = isset($this->querySegments['selects']) ? $this->querySegments['selects'] : null;

        $this->querySegments['selects'] = [$this->raw($type . ' AS field')];

        $row = $this->execute('select')
            ->fetch();

        if ($mainSelects) {
            $this->querySegments['selects'] = $mainSelects;
        } else {
            unset($this->querySegments['selects']);
        }

        if (is_array($row)) {
            return (int)$row['field'];
        } elseif (is_object($row)) {
            return (int)$row->field;
        }

        return 0;
    }

    /**
     * @param string|null $key
     * @param string|null $operator
     * @param string|array $value
     * @param string $joiner
     * @return Builder
     */
    protected function handleWhere($key, string $operator = null, $value = null, string $joiner = 'AND'): self
    {
        if (is_array($key)) {
            $operator = '=';
            foreach ($key as $field => $value) {
                $this->querySegments['wheres'][] = [
                    'key' => $this->addTablePrefix($field),
                    'operator' => $operator,
                    'value' => $value,
                    'joiner' => $joiner
                ];
            }
        } else {
            if ($key && $operator && is_null($value)) {
                $value = $operator;
                $operator = '=';
            }

            $this->querySegments['wheres'][] = [
                'key' => $this->addTablePrefix($key),
                'operator' => $operator,
                'value' => $value,
                'joiner' => $joiner
            ];
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $prefix
     * @param string $operator
     * @return Builder
     */
    protected function handleWhereNull(string $key, string $prefix = '', string $operator = ''): self
    {
        $key = $this->compiler->quoteColumnName($this->addTablePrefix($key));

        return $this->{$operator . 'Where'}($this->raw($key . ' IS ' . $prefix . ' NULL'));
    }
}
