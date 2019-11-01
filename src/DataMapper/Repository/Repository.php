<?php

namespace Keiryo\DataMapper\Repository;

use Keiryo\DataMapper\QueryBuilder;

abstract class Repository implements RepositoryInterface
{

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
    public function findBy(array $criteria): array
    {
        $result = $this->query()
            ->where($criteria)
            ->get();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->query()
            ->where($criteria)
            ->first();
    }

    /**
     * @param string|null $alias
     * @return QueryBuilder
     */
    abstract protected function query(?string $alias = null): QueryBuilder;
}
