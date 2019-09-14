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

    public function testSelectReturnsArray() {
        $configs = new \Mercurio\App\Config;
        $configs->selectByValue('test value');
        $configs = $this->database->select($configs);

        $this->assertIsIterable($configs);
        $this->assertIsObject($configs[0]);
        $this->assertIsIterable($configs[0]->data);
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
        $this->assertNull($this->database->select($config));
    }

    /**
     * This is example API of what `App` components usage should look like
     */
    public function exampleAPI() {
        $database = new \Mercurio\App\Database($dbparams);

        // 1 object
        $config = new \Mercurio\App\Config;
        $config->getByName('test');
        $config = $database->get($config);
        $config->getValue();

        // Varios objetos
        $configs->selectByValue(40);
        $configs = $database->select($config);
        $configs[0]->getValue();
    }

}
