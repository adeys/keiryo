<?php

namespace Keiryo\Tests\DataMapper\Fixtures\Entity;

class Comment
{
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var User
     */
    private $author;

    public function getId()
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
