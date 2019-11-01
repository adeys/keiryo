<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 07/07/2019
 * Time: 16:32
 */

namespace Keiryo\Queue\Contracts;

interface JobInterface
{

    /**
     * Executes the job
     *
     * @return mixed
     */
    public function fire();

    /**
     * Gets job id
     *
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return mixed
     */
    public function setId(string $id);
}
