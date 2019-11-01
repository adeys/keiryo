<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 17:21
 */

namespace Keiryo\Validation;

use Rakit\Validation\ErrorBag;
use Throwable;

class ValidationException extends \RuntimeException
{

    /**
     * @var array
     */
    private $errors;

    /**
     * ValidationException constructor.
     * @param ErrorBag $errors
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(ErrorBag $errors, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ErrorBag
     */
    public function getErrors(): ErrorBag
    {
        return $this->errors;
    }
}
