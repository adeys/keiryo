<?php

namespace Keiryo\Tests\DataMapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Keiryo\Database\DatabaseInterface;
use Keiryo\Database\Query\Builder;
use Keiryo\DataMapper\Configuration;
use Keiryo\DataMapper\EntityManager;
use Keiryo\DataMapper\Mapping\EntityMetadata;
use Keiryo\DataMapper\Mapping\MetadataFactory;
use Keiryo\Tests\DataMapper\Fixtures\Entity\User;

class ConfigurationTest extends TestCase
{

    /**
     * @var Configuration
     */
    private $config;

    public function setUp()
    {
        $this->config = new Configuration(__DIR__.'/Fixtures/Mapping');
        
        $db = $this->prophesize(DatabaseInterface::class);
        $qb = $this->prophesize(Builder::class);
        $qb->table(Argument::any())->willReturn($qb);
        $db->getQueryBuilder()->willReturn($qb);
        $em = $this->prophesize(EntityManager::class);
        $em->getConnection()->willReturn($db);

        $this->config->setUp($em->reveal());
    }

    public function testGetMetadataFactory()
    {
        $factory = $this->config->getMetadataFactory();
        $this->assertInstanceOf(MetadataFactory::class, $factory);
    }

    public function testMetadataCorrectlyConfigured()
    {
        $factory = $this->config->getMetadataFactory();

        $this->assertFalse($factory->hasMetadataFor(\stdClass::class));
        $this->assertTrue($factory->hasMetadataFor(User::class));

        $metadata = $factory->getClassMetadata(User::class);
        $this->assertInstanceOf(EntityMetadata::class, $metadata);
        $this->assertEquals(User::class, $metadata->getEntityClass());
    }
}
