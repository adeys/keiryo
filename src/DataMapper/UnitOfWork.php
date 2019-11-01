<?php /** @noinspection PhpInternalEntityUsedInspection */

namespace Keiryo\DataMapper;

class UnitOfWork
{
    const STATE_NEW = 'new';

    const STATE_MANAGED = 'managed';

    const STATE_REMOVED = 'removed';

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * Container of entities by states
     *
     * @var array
     */
    protected $entities = [
        self::STATE_NEW => [],
        self::STATE_REMOVED => []
    ];

    /**
     * @var IdentityMap
     */
    private $identityMap;

    public function __construct(EntityManager $manager)
    {
        $this->em = $manager;
        $this->identityMap = new IdentityMap();
    }

    /**
     * Loads an entity by its identifier
     *
     * @param string $entityClass
     * @param mixed $id
     * @return object|null
     */
    public function get(string $entityClass, $id): ?object
    {
        if ($this->identityMap->has($entityClass, $id)) {
            return $this->identityMap->get($entityClass, $id);
        }

        $mapper = $this->em->getMapper($entityClass);
        $result = $mapper->find($id);
        $this->identityMap->add($result, $id);

        return $result;
    }

    /**
     * Sets an entity as persistent
     *
     * @param object $entity
     * @return void
     */
    public function persist(object $entity)
    {
        if (!$this->identityMap->hasEntity($entity)) {
            $this->entities[self::STATE_NEW][get_class($entity)][] = $entity;
        } else {
            //$this->entities[self::STATE_MANAGED][] = $entity;
        }
    }

    /**
     * Removes an entity from managed ones
     *
     * @param object $entity
     * @return void
     */
    public function remove(object $entity)
    {
        if ($this->identityMap->hasEntity($entity)) {
            $this->identityMap->forget($entity);
            $this->entities[self::STATE_REMOVED][] = $entity;
        }
    }

    /**
     * Commit all the changes to persisters
     *
     * @return void
     */
    public function commit()
    {
        $inserts = $this->entities[self::STATE_NEW];
        $updates = $this->identityMap->getEntities();
        $removes = array_values($this->entities[self::STATE_REMOVED]);

        if (empty($inserts) && empty($updates) && empty($removes)) {
            return;
        }

        foreach ($inserts as $class => $entities) {
            $mapper = $this->em->getMapper($class);
            foreach ($entities as $entity) {
                $mapper->queueInsert($entity);
            }
            $mapper->executeInsert();
        }

        foreach ($updates as $entity) {
            $class = get_class($entity);
            $mapper = $this->em->getMapper($class);
            $mapper->update($entity);
        }

        foreach ($removes as $entity) {
            $mapper = $this->em->getMapper(get_class($entity));
            $mapper->delete($entity);
        }

        $this->entities[self::STATE_NEW] =
        $this->entities[self::STATE_MANAGED] =
        $this->entities[self::STATE_REMOVED] = [];
    }

    /**
     * Gets changed part of entities
     *
     * @param object $entity
     * @return array
     */
    public function getChangeSet(object $entity): array
    {
        //$this->em->getMapper(\get_class($entity));
        $original = $this->identityMap->getOriginal($entity) ?? new \stdClass();

        return ChangeTracker::getChanges($original, $entity);
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap(): IdentityMap
    {
        return $this->identityMap;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }
}
