<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 17/02/2019
 * Time: 10:24
 */

namespace Keiryo\DataMapper;

interface IdentifiableInterface
{

    /**
     * Gets entity identifier
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Set entity identifier
     *
     * @param int $id
     */
    public function setId(int $id): void;
}
