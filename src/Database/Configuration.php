<?php

namespace Keiryo\Database;

use Keiryo\Database\Driver\DriverInterface;
use Keiryo\Database\Driver\MysqlDriver;
use Keiryo\Database\Driver\ProstgreDriver;
use Keiryo\Database\Driver\SqliteDriver;

class Configuration
{

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Default connection
     *
     * @var array
     */
    protected $default;

    /**
     * @var array
     */
    protected $drivers = [
        'sqlite' => SqliteDriver::class,
        'prostgre' => ProstgreDriver::class,
        'mysql' => MysqlDriver::class
    ];

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->default = $this->options['connections'][$this->options['default']];
    }

    /**
     * Get used driver
     *
     * @param string $name
     * @return DriverInterface
     */
    public function getDriver(?string $name = null): DriverInterface
    {
        $options = $this->options['connections'][$name ?? $this->options['default']];
        $driver = $this->drivers[$options['type']] ?? null;
        if (!$driver) {
            throw new \UnexpectedValueException(sprintf(
                'Provided database type is incorrect or is not supported (%s)',
                (string)$name
            ));
        }

        return new $driver($options['options'] ?? []);
    }

    /**
     * Get PDO specific options or attributes
     *
     * @return array
     */
    public function getPdoOptions(): array
    {
        return $this->default['pdo_options'] ?? [];
    }
}
