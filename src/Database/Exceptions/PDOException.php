<?php

namespace Keiryo\Database\Exceptions;

use PDOException as BasePDOException;
use Throwable;

/**
 * {@inheritDoc}
 *
 * @author Surgie
 */
class PDOException extends BasePDOException
{
    /**
     * @var string|null SQL query which caused the error (if caused by a query)
     */
    protected $query;

    /**
     * @var array|null Bound values (if caused by a query)
     */
    protected $values;

    /**
     * {@inheritDoc}
     * @param string|null $query SQL query which caused the error (if caused by a query)
     * @param array|null $values Bound values (if caused by a query)
     */
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        string $query = null,
        array $values = null
    )
    {
        if ($query !== null) {
            $message .= '; SQL query: (' . $query . ')';
        }
        if ($values !== null) {
            $message .= '; bound values: ' . $this->valueToString($values);
        }

        parent::__construct($message, 0, $previous);

        // The constructor doesn't except string as a $code value so we have to set it manually
        $this->code = $code;
        $this->query = $query;
        $this->values = $values;
    }

    /**
     * Converts an arbitrary value to string for a debug message.
     *
     * @param mixed $value
     * @return string
     */
    protected function valueToString($value): string
    {
        if ($value === false) {
            return 'false';
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === null) {
            return 'null';
        }
        if (is_string($value)) {
            if (call_user_func(function_exists('mb_strlen') ? 'mb_strlen' : 'strlen', $value) > 100) {
                $value = call_user_func(function_exists('mb_substr') ? 'mb_substr' : 'substr', $value, 0, 97) . '...';
            }
            return '"' . $value . '"';
        }
        if (is_object($value)) {
            return 'a ' . get_class($value) . ' instance';
        }
        if (is_array($value)) {
            $keys = array_keys($value);
            $isAssociative = $keys !== array_keys($keys);
            $valuesStrings = [];

            foreach ($value as $key => $subValue) {
                $valuesStrings[] = ($isAssociative ? $this->valueToString($key) . ' => ' : '')
                    . $this->valueToString($subValue);
            }

            return '[' . implode(', ', $valuesStrings) . ']';
        }
        if (is_resource($value)) {
            return 'a resource';
        }
        return (string)$value;
    }

    /**
     * Makes a self instance based on a base PDOException instance.
     *
     * @param BasePDOException $exception Original exception
     * @param string|null $query SQL query which caused the error (if caused by a query)
     * @param array|null $values Bound values (if caused by a query)
     * @return self
     */
    public static function wrapBaseException(BasePDOException $exception, string $query = null, array $values = null)
    {
        $newException = new static($exception->getMessage(), $exception->getCode(), $exception, $query, $values);
        $newException->errorInfo = $exception->errorInfo;
        return $newException;
    }

    /**
     * @return string SQL query which caused the error (empty if the error is not caused by a query)
     */
    public function getQuery(): string
    {
        return $this->query ?? '';
    }

    /**
     * @return array Bound values (empty if the error is not caused by a query)
     */
    public function getValues(): array
    {
        return $this->values ?? [];
    }
}
