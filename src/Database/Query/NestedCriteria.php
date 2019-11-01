<?php
/**
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/query-builder
 */

namespace Keiryo\Database\Query;

class NestedCriteria extends Builder
{
    /**
     * @param string $key
     * @param string|null $operator
     * @param null $value
     * @param string $joiner
     * @return Builder
     */
    protected function handleWhere(string $key, ?string $operator = null, $value = null, string $joiner = 'AND'): Builder
    {
        $this->querySegments['criteria'][] = [
            'key' => $this->addTablePrefix($key),
            'operator' => $operator,
            'value' => $value,
            'joiner' => $joiner
        ];

        return $this;
    }
}
