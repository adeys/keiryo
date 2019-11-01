<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 02:26
 */

namespace Keiryo\Security\Authentication\User;

class User implements UserInterface
{

    /**
     * @var string
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string
     */
    private $token;

    public function __construct(string $username, string $token, ?string $password = null)
    {
        $this->username = $username;
        $this->token = $token;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Returns the authentication token either for api or persistence
     *
     * @return string The authentication token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->password = null;
        return true;
    }
}
