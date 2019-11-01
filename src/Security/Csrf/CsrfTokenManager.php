<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 22:40
 */

namespace Keiryo\Security\Csrf;

use Keiryo\Helper\Str;
use Symfony\Component\HttpFoundation\Request;

class CsrfTokenManager
{
    /**
     * @var string
     */
    const TOKEN_NAME = '_csrf_token';

    /**
     * @var bool
     */
    private $valid = false;

    /**
     * @var int
     */
    private $entropy;
    /**
     * @var string
     */
    private $fieldName;

    /**
     * CsrfTokenManager constructor.
     * @param int $entropy
     * @param string $fieldName
     */
    public function __construct(int $entropy = 256, string $fieldName = '_token')
    {
        $this->entropy = $entropy;
        $this->fieldName = $fieldName;
    }

    /**
     * Validate the request
     *
     * @param Request $request
     */
    public function validate(Request $request)
    {
        $token = $request->request->get($this->fieldName, null);

        if ($token) {
            $this->valid = hash_equals($request->getSession()->get(self::TOKEN_NAME), $token);
        }
    }

    /**
     * Csrf token validation state
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Generate new token
     *
     * @return string
     * @throws \Exception
     */
    public function generateToken(): string
    {
        return Str::random(32);
    }
}
