<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 08:42
 */

namespace Keiryo\DataMapper;

use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Builder;
use Keiryo\DataMapper\Mapping\EntityMapperInterface;

class QueryBuilder extends Builder
{

    /**
     * @var EntityMapperInterface
     */
    protected $mapper;

    /**
     * QueryBuilder constructor.
     *
     * @param DatabaseInterface $connection
     * @param EntityMapperInterface $mapper
     */
    public function __construct(DatabaseInterface $connection, EntityMapperInterface $mapper)
    {
        parent::__construct($connection);
        $this->mapper = $mapper;
        $this->table($mapper->getTable());
    }

    /**
     * {@inheritdoc}
     */
    public function get(): array
    {
        $result = parent::get();

        return array_map([$this->mapper, 'createEntity'], $result);
    }

    /**
     * Finds by identifier
     *
     * @param $id
     * @return object|null
     */
    public function find($id): ?object
    {
        return $this->mapper->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        $result = parent::first();

        return $result
            ? $this->mapper->createEntity($result)
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function newQuery(?string $alias = null): Builder
    {
        return (new static($this->connection, $this->mapper))
            ->table($this->mapper->getTable(), $alias);
    }

    /**
     * @return Builder
     */
    public function nativeQuery(): Builder
    {
        return (new parent($this->connection))->table($this->mapper->getTable());
    }

    /**
     * Update an entity
     *
     * @param array|IdentifiableInterface $entity
     * @return int
     */
    public function update($entity): int
    {
        /** @var object $entity */
        if ($this->isSupported($entity)) {
            return $this->mapper->update($entity);
        } elseif (is_array($entity)) {
            /** @var array $entity */
            return parent::update($entity);
        }

        return 0;
    }

    /**
     * Checks wether given object is supported
     *
     * @param object $entity
     * @return bool
     */
    protected function isSupported($entity): bool
    {
        return is_object($entity);
    }
}
