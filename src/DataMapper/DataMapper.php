<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30/05/2019
 * Time: 21:00
 */

namespace Keiryo\DataMapper;

use Keiryo\Database\DatabaseManager;
use Keiryo\DataMapper\Mapping\EntityMapperInterface;
use Keiryo\DataMapper\Mapping\MappingRegistry;

class DataMapper
{

    /**
     * @var DatabaseManager
     */
    private $manager;

    /**
     * @var EntityManager[]
     */
    private $managers = [];

    /**
     * @var MappingRegistry
     */
    private $mappings;

    /**
     * DataMapper constructor.
     * @param DatabaseManager $manager
     * @param MappingRegistry $mappings
     */
    public function __construct(DatabaseManager $manager, MappingRegistry $mappings)
    {
        $this->manager = $manager;
        $this->mappings = $mappings;
    }

    /**
     * Gets entity manager for given database name
     *
     * @param string $name
     * @return EntityManager
     */
    public function getManager(string $name = 'default'): EntityManager
    {
        if (!in_array($name, $this->managers)) {
            $manager = new EntityManager($this->manager->getDatabase($name));
            $registry = $manager->getMapperRegistry();
            foreach ($this->mappings->getMappings($name) as $class => $mapper) {
                if (is_subclass_of($mapper, EntityMapperInterface::class)) {
                    $registry->register($class, $mapper);
                }
            }
            $this->managers[$name] = $manager;
        }

        return $this->managers[$name];
    }
}
