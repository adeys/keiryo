<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08/02/2019
 * Time: 00:04
 */

namespace Keiryo\DataMapper\Mapping;

use Keiryo\DataMapper\IdentifiableInterface;
use Keiryo\DataMapper\QueryBuilder;

interface EntityMapperInterface
{

    /**
     * Creates an entity from given input values
     *
     * @param array $input
     * @return IdentifiableInterface
     */
    public function createEntity(array $input): IdentifiableInterface;

    /**
     * Extract an entity to persistable state
     *
     * @param object $entity
     * @return array
     */
    public function extract(IdentifiableInterface $entity): array;

    /**
     * Gets an entity by its primary key
     *
     * @param $id
     * @return IdentifiableInterface|null
     */
    public function find($id): ?IdentifiableInterface;

    /**
     * Retrieves all data
     *
     * @return IdentifiableInterface[]
     */
    public function findAll(): array;

    /**
     * Performs an entity insertion
     *
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function insert(IdentifiableInterface $entity);

    /**
     * Performs an entity update
     *
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function update(IdentifiableInterface $entity);

    /**
     * Performs an entity deletion
     *
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function delete(IdentifiableInterface $entity);

    /**
     * Queue an entity for insertion
     *
     * @internal
     * @param IdentifiableInterface $entity
     * @return mixed
     */
    public function queueInsert(IdentifiableInterface $entity);

    /**
     * Performs batch insert
     *
     * @internal
     * @return mixed
     */
    public function executeInsert();

    /**
     * Gets entity table
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * @param string|null $alias
     * @return QueryBuilder
     */
    public function query(?string $alias = null): QueryBuilder;
}
