<?php
/**
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/query-builder
 */

namespace Keiryo\Database\Query;

class Raw
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * Raw constructor.
     * @param $value
     * @param array $bindings
     */
    public function __construct($value, array $bindings = [])
    {
        $this->value = (string)$value;
        $this->bindings = $bindings;
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
