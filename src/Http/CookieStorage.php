<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 12:01
 */

namespace Keiryo\Http;

use Symfony\Component\HttpFoundation\Cookie;

class CookieStorage
{
    /**
     * @var Cookie[]
     */
    private $cookies = [];

    /**
     * Enqueue a cookie for next request
     *
     * @param string $name
     * @param string|null $value
     * @param int $expires
     * @param bool $httpOnly
     * @param bool $secure
     * @param string|null $path
     * @param string|null $domain
     */
    public function setCookie(
        string $name,
        ?string $value = null,
        $expires = 0,
        bool $httpOnly = true,
        bool $secure = true,
        ?string $path = '/',
        ?string $domain = null
    )
    {
        $this->cookies[] = new Cookie(
            $name,
            $value,
            $expires,
            $path,
            $domain,
            $secure,
            $httpOnly
        );
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }
}
