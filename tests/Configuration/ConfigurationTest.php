<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 31/01/2019
 * Time: 14:03
 */

namespace Keiryo\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Keiryo\Configuration\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp()
    {
        $this->config = new Configuration([
            'app' => 'Keiryo',
            'root' => 'test'
        ]);

        $this->config->load(__DIR__ . '/Fixtures/config.yml');
    }

    public function testGet()
    {
        $this->assertEquals('debug', $this->config->get('env'));
        $this->assertEquals('Keiryo', $this->config->get('app_name'));
    }

    public function testGetNestedValues()
    {
        $this->assertTrue($this->config->has('database'));
        $this->assertEquals('root', $this->config->get('database.user'));
        $this->assertEquals('Keiryo.db', $this->config->get('database.name'));
    }

    public function testSetWithNestedParams()
    {
        $this->config->set('user.name', 'kraph');
        $this->config->set('user.email', 'kraph@gmail.com');

        $this->assertTrue($this->config->has('user'));
        $this->assertEquals([
            'name' => 'kraph',
            'email' => 'kraph@gmail.com'
        ], $this->config->get('user'));
    }

    public function testNestedHas()
    {
        $this->assertTrue($this->config->has('env'));
        $this->assertTrue($this->config->has('database'));
        $this->assertTrue($this->config->has('database.user'));
        $this->assertTrue($this->config->has('database.name'));
    }

    public function testLoadMultipleConfig()
    {
        $this->config->load(__DIR__ . '/Fixtures/config.json');

        $this->assertEquals('test', $this->config->get('env'));
        $this->assertEquals('john', $this->config->get('database.user'));
        $this->assertEquals('Keiryo_admin', $this->config->get('users.john.password'));
    }
}
