<?php

namespace Simplex\DataMapper\Mapping;

use Simplex\Database\DatabaseInterface;
use Simplex\DataMapper\QueryBuilder;
use Simplex\DataMapper\UnitOfWork;

abstract class EntityMapper implements EntityMapperInterface
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $queued = [];

    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @var \Simplex\DataMapper\QueryBuilder
     */
    protected $builder;

    /**
     * @var UnitOfWork
     */
    protected $uow;

    public function __construct(DatabaseInterface $database, UnitOfWork $uow)
    {
        $this->database = $database;
        $this->builder = new QueryBuilder($database, $this);
        $this->uow = $uow;
    }

    /**
     * Retrieves all data
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->query()->get();
    }

    /**
     * @param $id
     * @return object
     */
    public function find($id): object
    {
        return $this->query()
            ->where('id', $id)
            ->first();
    }

    /**
     * Performs an entity insertion
     *
     * @param object $entity
     * @return mixed
     */
    public function insert(object $entity)
    {
        $this->queueInsert($entity);
        $this->executeInsert();
    }

    /**
     * Queue an entity for insertion
     *
     * @internal
     * @param object $entity
     * @return mixed
     */
    public function queueInsert(object $entity)
    {
        $this->queued[] = $this->extract($entity);
    }

    /**
     * Performs batch insert
     *
     * @internal
     * @return mixed
     */
    public function executeInsert()
    {
        return $this->database->transaction(function () {
            $this->builder->insert($this->queued);
        });
    }

    /**
     * Gets entity table
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string|null $alias
     * @return QueryBuilder
     */
    public function query(?string $alias = null): QueryBuilder
    {
        return $this->builder->newQuery()->table($this->table, $alias);
    }

    /**
     * @param object $entity
     * @return mixed
     */
    public function update(object $entity)
    {
        if (method_exists($entity, 'getId')) {
            $changes = $this->uow->getChangeSet($entity);
            return $this->query()
                ->where('id', $entity->getId())
                ->update($changes);
        };

        throw new \RuntimeException('Method EntityMapperInterface::update needs to be implemented');
    }

    /**
     * @param object $entity
     * @return mixed
     */
    public function delete(object $entity)
    {
        if (method_exists($entity, 'getId')) {
            return $this->query()
                ->where('id', $entity->getId())
                ->delete();
        }

        throw new \RuntimeException('Method EntityMapperInterface::delete needs to be implemented');
    }
}
