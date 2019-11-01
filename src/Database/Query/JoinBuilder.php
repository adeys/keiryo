<?php
/**
 * Copyright (c) 2017 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2017, Adam Banaszkiewicz
 * @link      https://github.com/requtize/query-builder
 */

namespace Keiryo\Database\Query;

class JoinBuilder extends Builder
{
    public function on(string $key, string $operator, $value = null)
    {
        return $this->joinHandler($key, $operator, $value, 'AND');
    }

    protected function joinHandler(string $key, ?string $operator = null, $value = null, string $joiner = 'AND')
    {
        if ($key && $operator && !$value) {
            $value = $operator;
            $operator = '=';
        }

        $this->querySegments['criteria'][] = [
            'key' => $this->addTablePrefix($key),
            'operator' => $operator,
            'value' => $this->addTablePrefix($value),
            'joiner' => $joiner
        ];

        return $this;
    }

    public function orOn(string $key, string $operator, $value = null)
    {
        return $this->joinHandler($key, $operator, $value, 'OR');
    }
}
