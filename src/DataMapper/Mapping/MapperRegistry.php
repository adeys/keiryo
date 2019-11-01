<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/02/2019
 * Time: 23:57
 */

namespace Keiryo\DataMapper\Mapping;

use Keiryo\Database\DatabaseInterface;
use Keiryo\DataMapper\UnitOfWork;

class MapperRegistry
{

    private $mappings = [];

    /**
     * @var callable
     */
    private $resolver;

    /**
     * MapperRegistry constructor.
     * @param DatabaseInterface $connection
     * @param UnitOfWork $uow
     */
    public function __construct(DatabaseInterface $connection, UnitOfWork $uow)
    {
        $this->resolver = function (string $mapper) use ($connection, $uow) {
            return new $mapper($connection, $uow);
        };
    }

    /**
     * Register an entity class against a mapper class
     *
     * @param string $entityClass
     * @param string $mapperClass
     */
    public function register(string $entityClass, string $mapperClass)
    {
        $this->mappings[$entityClass] = $mapperClass;
    }

    /**
     * Sets mapper instantiation resolver
     *
     * @param callable $resolver
     */
    public function setResolver(callable $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * Resolve given class mapper
     *
     * @param string $entityClass
     * @return mixed
     */
    public function resolve(string $entityClass)
    {
        if (!isset($this->mappings[$entityClass])) {
            throw new \OutOfBoundsException(sprintf('There is no mapper registered for entity class %s', $entityClass));
        }

        return call_user_func($this->resolver, $this->mappings[$entityClass]);
    }
}
