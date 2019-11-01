<?php

namespace Keiryo\DataMapper;

use Keiryo\Database\DatabaseInterface;
use Keiryo\DataMapper\Mapping\EntityMapper;
use Keiryo\DataMapper\Mapping\EntityMapperInterface;
use Keiryo\DataMapper\Mapping\MapperRegistry;

class EntityManager
{

    /**
     * @var DatabaseInterface
     */
    protected $connection;

    /**
     * Unit of work instance
     *
     * @var UnitOfWork
     */
    protected $uow;

    /**
     * @var EntityMapper[]
     */
    protected $mappers = [];

    /**
     * @var MapperRegistry
     */
    private $mapperRegistry;

    /**
     * EntityManager constructor.
     * @param DatabaseInterface $connection
     */
    public function __construct(DatabaseInterface $connection)
    {
        $this->connection = $connection;
        $this->uow = new UnitOfWork($this);
        $this->mapperRegistry = new MapperRegistry($connection, $this->uow);
    }

    /**
     * Get metadata for provided class
     *
     * @param string $className
     * @return EntityMapperInterface
     */
    public function getMapper(string $className): EntityMapperInterface
    {
        if (!isset($this->mappers[$className])) {
            $this->mappers[$className] = $this->mapperRegistry->resolve($className);
        }

        return $this->mappers[$className];
    }

    /**
     * Find an entity by its primary key
     *
     * @param string $entityClass
     * @param mixed $key
     * @return object
     */
    public function find(string $entityClass, $key): object
    {
        return $this->uow->get($entityClass, $key);
    }

    /**
     * Gets database connection
     *
     * @return DatabaseInterface
     */
    public function getConnection(): DatabaseInterface
    {
        return $this->connection;
    }

    /**
     * Gets the unit of work instance
     *
     * @return UnitOfWork
     */
    public function getUnitOfWork(): UnitOfWork
    {
        return $this->uow;
    }

    /**
     * Store an entity as persistent
     * @param object $entity
     */
    public function persist(object $entity)
    {
        $this->uow->persist($entity);
    }

    /**
     * Removes an entity from the manager
     * @param object $entity
     */
    public function remove(object $entity)
    {
        $this->uow->remove($entity);
    }

    /**
     * Flushes the entity manager to commit the changes
     *
     * @return void
     */
    public function flush()
    {
        $this->uow->commit();
    }

    /**
     * @return MapperRegistry
     */
    public function getMapperRegistry(): MapperRegistry
    {
        return $this->mapperRegistry;
    }
}
