<?php

namespace Keiryo\Tests\DataMapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Builder;
use Keiryo\DataMapper\EntityManager;
use Keiryo\DataMapper\Mapping\EntityMetadata;
use Keiryo\Tests\DataMapper\Fixtures\Entity\User;

class EntityManagerTest extends TestCase
{

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $db = $this->prophesize(DatabaseInterface::class);
        $qb = $this->prophesize(Builder::class);
        $qb->table(Argument::any())->willReturn($qb);
        $qb->where(Argument::any())->willReturn($qb);
        $qb->get()->willReturn([]);
        $db->getQueryBuilder()->willReturn($qb);

        $this->em = new EntityManager($db->reveal());

        $user = new User();
        $user->setId(1);
        $user->setName('kraph');
        $user->setEmail('kraph@email.fr');

        $this->em->persist($user);
        $this->em->flush();
    }

    public function testFindWhenExistsReturnObject()
    {
        $user = new User();
        $this->em->persist($user);

        /**
         * Try getting without persist returns null
         */
        $user = $this->em->find(User::class, 2);
        $this->assertNull($user);
        
        $this->em->flush();
        
        $user = $this->em->find(User::class, 2);
        $this->assertEquals('object', gettype($user));
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(2, $user->getId());
        
        $this->assertNull($this->em->find(User::class, 3));
    }

    public function testFlushAfterEntityUpdate()
    {
        /*$user = $this->em->find(User::class, 1);
        $this->assertEquals('kraph', $user->getName());

        $user->setName('bukimi');
        $this->em->flush();

        $user = $this->em->find(User::class, 1);
        $this->assertEquals('bukimi', $user->getName());*/
    }

    public function testRemove()
    {
        $user = $this->em->find(User::class, 1);
        $this->em->remove($user);
        $this->em->flush();
        
        $this->assertNull($this->em->find(User::class, 1));
    }
}
