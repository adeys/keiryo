<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30/05/2019
 * Time: 20:12
 */

namespace Keiryo\Database;

class DatabaseManager
{

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var array
     */
    private $dbs = [];

    /**
     * DatabaseManager constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Gets database for given connection
     *
     * @param string $name
     * @return DatabaseInterface
     */
    public function getDatabase(string $name = 'default'): DatabaseInterface
    {
        if (!in_array($name, $this->dbs)) {
            $driver = $this->configuration->getDriver($name === 'default' ? null : $name);
            return $this->dbs[$name] = new Database($driver);
        }

        return $this->dbs[$name];
    }
}
