<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 08/02/2019
 * Time: 00:25
 */

namespace Keiryo\DataMapper;

class IdentityMap
{

    /**
     * @var \ArrayObject
     */
    private $entities;

    /**
     * @var \SplObjectStorage
     */
    private $storage;

    /**
     * @var \SplObjectStorage
     */
    private $originals;

    public function __construct()
    {
        $this->entities = new \ArrayObject();
        $this->storage = new \ArrayObject();
        $this->originals = new \ArrayObject();
    }

    /**
     * Add an entity to the map
     *
     * @param object $entity
     * @param $id
     */
    public function add(object $entity, $id = null)
    {
        $uid = spl_object_hash($entity);

        if ($id) {
            $id = get_class($entity) . ':' . $id;
            $this->entities[$id] = $uid;
        }

        $this->storage[$uid] = $entity;
        $this->originals[$uid] = clone $entity;
    }

    /**
     * Checks wether asked entity is registered
     *
     * @param string $entityClass
     * @param $id
     * @return bool
     */
    public function has(string $entityClass, $id): bool
    {
        return $this->entities->offsetExists("$entityClass:$id");
    }

    /**
     * Get an entity by given key
     *
     * @param string $entityClass
     * @param $id
     * @return object|null
     */
    public function get(string $entityClass, $id): ?object
    {
        $uid = $this->entities->offsetGet("$entityClass:$id") ?? '';
        return $this->storage->offsetGet($uid) ?? null;
    }

    /**
     * Checks if given entity is already stored
     *
     * @param object $entity
     * @return bool
     */
    public function hasEntity(object $entity): bool
    {
        return isset($this->storage[spl_object_hash($entity)]);
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return (array)$this->storage;
    }

    public function getOriginal(object $entity): ?object
    {
        return $this->originals->offsetGet(spl_object_hash($entity)) ?? null;
    }

    /**
     * Forgets an entity
     *
     * @param object $entity
     */
    public function forget(object $entity)
    {
        $uid = spl_object_hash($entity);
        $this->storage->offsetUnset($uid);
        $this->originals->offsetUnset($uid);
    }
}
