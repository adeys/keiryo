<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 02:19
 */

namespace Keiryo\Security\Authentication\Provider;

use Keiryo\Security\Authentication\User\UserInterface;

interface UserProviderInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     *
     */
    public function loadUserByUsername(string $username): ?UserInterface;

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param string $token
     * @return UserInterface
     */
    public function refreshUser(string $token): ?UserInterface;

    /**
     * Whether this provider supports the given user class.
     *
     * @param UserInterface $user
     */
    public function forget(UserInterface $user);
}
