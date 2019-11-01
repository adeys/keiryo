<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 21:26
 */

namespace Keiryo\Queue;

use Keiryo\Queue\Contracts\JobInterface;

abstract class AbstractJob implements JobInterface
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
