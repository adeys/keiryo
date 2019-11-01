<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28/07/2019
 * Time: 19:32
 */

namespace Keiryo\Security\Authentication\Authorization;

use Throwable;

class AuthorizationException extends \Exception
{

    public function __construct(string $message = "", int $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
