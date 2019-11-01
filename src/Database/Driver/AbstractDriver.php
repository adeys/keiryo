<?php

namespace Keiryo\Database\Driver;

use PDO;

abstract class AbstractDriver implements DriverInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * {@inheritdoc}
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
}
