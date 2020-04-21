<?php
/**
 * Created by PhpStorm.
 * User: K. Raph
 * Date: 02/11/2019
 * Time: 16:55
 */

namespace Keiryo\Security\Authentication;


use Keiryo\Security\Authentication\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

interface AuthenticationManagerInterface
{

    /**
     * Authenticates a request
     *
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request): bool;

    /**
     * Gets authenticated user
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

}