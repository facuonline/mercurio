<?php
namespace Mercurio\Test;

class DatabaseTest extends \PHPUnit\Framework\TestCase {

    protected $database;

    protected function setUp(): void {
        $dbparams = \Mercurio\App::getDatabase();
        $this->database = new \Mercurio\App\Database($dbparams);
    }

    public function testDatabaseInstantiatesMedoo() {
        $this->assertIsObject($this->database->getSql());
    }

    public function testInsertCreatesNewRecord() {
        // Faking ID needed values
        $_SERVER['REMOTE_PORT'] = 7777;

        $config = new \Mercurio\App\Config;
        $config->setName('test');
        $config->setValue('test value');

        $this->database->insert($config);

        $result = $this->database->getSql()->get(DB_CONF, 'value', ['name' => 'test']);
        $this->assertEquals('test value', $result);
    }

    public function testGetLoadsData() {
        $config = new \Mercurio\App\Config;
        $config->getByName('test');
        $config = $this->database->get($config);

        $this->assertIsObject($config);
        $this->assertIsIterable($config->data);
        $this->assertArrayHasKey('name', $config->data);
        $this->assertEquals('test', $config->data['name']);
    }

    public function testUpdate() {
        $config = new \Mercurio\App\Config;
        $config->getByName('test');
        $config = $this->database->get($config);
        $config->setValue('new test value');

        $this->database->update($config);
        $config = $this->database->get($config);

        $this->assertEquals('new test value', $config->data['value']);
    }

    public function testDelete() {
        $config = new \Mercurio\App\Config;
        $config->getByName('test');
        $config = $this->database->get($config);

        $this->database->delete($config);

        $this->assertNull($this->database->get($config));
    }

}
