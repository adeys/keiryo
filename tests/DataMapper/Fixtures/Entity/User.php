<?php

namespace Keiryo\Tests\DataMapper\Fixtures\Entity;

class User
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var \DateTime
     */
    private $joinedAt;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
    
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
    
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function getEmail(): ?string
    {
        return $this->email;
    }
    
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return \DateTime
     */
    public function getJoinedAt(): \DateTime
    {
        return $this->joinedAt;
    }

    /**
     * @param \DateTime $joinedAt
     */
    public function setJoinedAt(\DateTime $joinedAt): void
    {
        $this->joinedAt = $joinedAt;
    }
}
