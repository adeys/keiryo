<?php

namespace Keiryo\Database\Driver;

use PDO;

interface DriverInterface
{
    /**
     * Connects to database
     *
     * @return mixed
     */
    public function connect();

    /**
     * Gets the PDO instance
     *
     * @return PDO|null
     */
    public function getPdo(): ?PDO;
}
