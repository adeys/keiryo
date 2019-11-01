<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09/08/2019
 * Time: 19:15
 */

namespace Keiryo\DataMapper\Mapping;

class MappingRegistry
{

    /**
     * @var array
     */
    protected $mappings = [];

    /**
     * @param array $mappings
     * @param string|null $connection
     */
    public function register(array $mappings, ?string $connection = 'default')
    {
        $connection = is_null($connection) ? 'default' : $connection;
        $this->mappings[$connection] = array_merge($this->mappings[$connection] ?? [], $mappings);
    }

    /**
     * @param string|null $connection
     * @return array
     */
    public function getMappings(?string $connection = 'default'): array
    {
        return $this->mappings[$connection] ?? [];
    }
}
