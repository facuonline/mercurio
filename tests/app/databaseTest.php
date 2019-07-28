<?php
namespace Mercurio;

class DatabaseTest extends \PHPUnit\Framework\TestCase {

    public function testGetConfigReturnsFalseOnNoConfigurationFound() {
        $config = \Mercurio\App\Database::getConfig('this configuration should not exist');

        $this->assertFalse($config);
    }

    public function testSetConfigInsertsNewValueAndGetConfigReturnsIt() {
        $config = \Mercurio\App\Database::setConfig('test_config', 'test Value');

        $this->assertEquals('test Value', \Mercurio\App\Database::getConfig('test_config'));
    }

    public function testSetConfigUpdatesValue() {
        $config = \Mercurio\App\Database::setConfig('test_config', 'test Value changed');

        $this->assertEquals('test Value changed', \Mercurio\App\Database::getConfig('test_config'));
    }

    public function testSetConfigDoesnotDuplicateEntries() {
        $medoo = new \Medoo\Medoo([
            'database_type' => getenv('DB_TYPE'),
            'database_name' => getenv('DB_NAME'),
            'server' => getenv('DB_HOST'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS')
        ]);
        $result = $medoo->select('mro_conf', '*', ['name' => 'test_config']);

        $this->assertCount(1, $result);
    }

    public function testUnsetFunctionDeletesRow() {
        \Mercurio\App\Database::unsetConfig('test_config');
        $config = \Mercurio\App\Database::getConfig('test_config');

        $this->assertFalse($config);
    }

}
