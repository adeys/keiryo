<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28/07/2019
 * Time: 19:26
 */

namespace Keiryo\Security\Authentication\Authorization;

use Keiryo\Security\Authentication\AuthenticationManager;

class AuthorizationManager
{

    /**
     * @var AuthenticationManager
     */
    private $authManager;

    /**
     * @var array
     */
    private $abilities = [];

    /**
     * AuthorizationManager constructor.
     * @param AuthenticationManager $authManager
     */
    public function __construct(AuthenticationManager $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * @param string $ability
     * @param $arguments
     * @return bool
     * @throws AuthorizationException
     */
    public function allow(string $ability, $arguments = null): bool
    {
        if ($this->has($ability)) {
            if ($user = $this->authManager->getUser()) {
                $arguments = func_get_args();
                array_shift($arguments);
                $result = call_user_func_array($this->abilities[$ability], array_merge([$user], $arguments));
                if ($result) {
                    return true;
                }
            }

            throw new AuthorizationException();
        }

        throw new \InvalidArgumentException(sprintf('Ability %s not registered', $ability));
    }

    /**
     * @param string $ability
     * @return bool
     */
    public function has(string $ability): bool
    {
        return isset($this->abilities[$ability]);
    }

    /**
     * @param string $ability
     * @param callable $handler
     */
    public function define(string $ability, callable $handler)
    {
        $this->abilities[$ability] = $handler;
    }
}
