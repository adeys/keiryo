<?php

namespace Keiryo\DataMapper\Mapping;

use Keiryo\Database\DatabaseInterface;
use Keiryo\DataMapper\IdentifiableInterface;
use Keiryo\DataMapper\QueryBuilder;
use Keiryo\DataMapper\UnitOfWork;

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
     * @var \Keiryo\DataMapper\QueryBuilder
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
     * @return IdentifiableInterface|null
     */
    public function find($id): ?IdentifiableInterface
    {
        return $this->query()
            ->where('id', $id)
            ->first();
    }

    /**
     * Performs an entity insertion
     *
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function insert(IdentifiableInterface $entity)
    {
        $this->queueInsert($entity);
        return $this->executeInsert();
    }

    /**
     * Queue an entity for insertion
     *
     * @internal
     * @param IdentifiableInterface $entity
     */
    public function queueInsert(IdentifiableInterface $entity)
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
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function update(IdentifiableInterface $entity)
    {
        $changes = $this->uow->getChangeSet($entity);
        if (!empty($changes)) {
            return $this->query()
                ->where('id', $entity->getId())
                ->update($changes);
        }

        throw new \RuntimeException('Method EntityMapperInterface::update needs to be implemented');
    }

    /**
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function delete(IdentifiableInterface $entity)
    {
        return $this->query()
            ->where('id', $entity->getId())
            ->delete();
    }
}
