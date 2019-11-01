<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 17/02/2019
 * Time: 10:31
 */

namespace Keiryo\DataMapper;

trait IdentifiableTrait
{

    /**
     * @var int|null
     */
    protected $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
