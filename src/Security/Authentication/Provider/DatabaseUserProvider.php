<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 02:18
 */

namespace Keiryo\Security\Authentication\Provider;

use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Builder;
use Keiryo\Security\Authentication\User\User;
use Keiryo\Security\Authentication\User\UserInterface;

class DatabaseUserProvider implements UserProviderInterface
{

    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $field;

    /**
     * DatabaseUserProvider constructor.
     * @param DatabaseInterface $database
     * @param string $table
     * @param string $field
     */
    public function __construct(DatabaseInterface $database, string $table, string $field)
    {
        $this->database = $database;
        $this->builder = $database->getQueryBuilder()->table($table);
        $this->field = $field;
    }

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
    public function loadUserByUsername(string $username): ?UserInterface
    {
        $result = $this->builder
            ->where($this->field, $username)
            ->addSelect([$this->field, 'password', 'session_token'])
            ->first();

        if ($result) {
            return new User($result[$this->field], $result['session_token'], $result['password']);
        }

        return null;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param string $sessionToken
     * @return UserInterface|null
     */
    public function refreshUser(string $sessionToken): ?UserInterface
    {
        $result = $this->builder
            ->where('session_token', $sessionToken)
            ->addSelect([$this->field, 'session_token'])
            ->first();

        return $result
            ? new User($result[$this->field], $result['session_token'])
            : null;
    }

    /**
     * @param string $field
     */
    public function setFieldName(string $field): void
    {
        $this->field = $field;
    }

    /**
     * Forgets user
     *
     * @param UserInterface $user
     */
    public function forget(UserInterface $user)
    {
        $this->builder
            ->where($this->field, $user->getUsername())
            ->andWhere('session_token', $user->getToken())
            ->update([
                'session_token' => base64_encode(random_bytes(20))
            ]);
    }
}
