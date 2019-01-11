<?php

namespace Simplex\DataMapper\Repository;

use Simplex\DataMapper\Persistence\PersisterInterface;
use Simplex\DataMapper\Mapping\EntityMetadata;
use Simplex\DataMapper\Proxy\ProxyFactory;
use Simplex\DataMapper\EntityManager;

class Repository implements RepositoryInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityMetadata
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $className;

    public function __construct(EntityManager $manager, EntityMetadata $metadata)
    {
        $this->metadata = $metadata;
        $this->className = $metadata->getEntityClass();
        $this->em = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function find($id): ?object
    {
        return $this->em->find($this->className, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        return $this->findBy([]);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, ?string $orderBy = 'DESC', ?int $limit = null, int $offset = 0): array
    {
        $uow = $this->em->getUnitOfWork();
        $persister = $uow->getPersister($this->className);
        $result = $persister->loadAll($criteria, $orderBy, $limit, $offset);
        return array_map(function (array $entry) use ($uow) {
            return $uow->createEntity($this->metadata, $entry);
        }, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->findBy($criteria, null, 1, null);
    }

    /**
     * Get managed entity class name
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
