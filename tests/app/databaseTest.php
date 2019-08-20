<?php
namespace Mercurio\Test;

class DatabaseTest extends \PHPUnit\Framework\TestCase {

    public function testGetConfigReturnsFalseOnNoConfigurationFound() {
        $DB = new \Mercurio\App\Database;
        $config = $DB->getConfig('this configuration should not exist');

        $this->assertFalse($config);
    }

    public function testSetConfigInsertsNewValueAndGetConfigReturnsIt() {
        $DB = new \Mercurio\App\Database;
        $config = $DB->setConfig('test_config', 'test Value');

        $this->assertEquals('test Value', $DB->getConfig('test_config'));
    }

    public function testSetConfigUpdatesValue() {
        $DB = new \Mercurio\App\Database;
        $config = $DB->setConfig('test_config', 'test Value changed');

        $this->assertEquals('test Value changed', $DB->getConfig('test_config'));
    }

    public function testSetConfigDoesNotDuplicateEntries() {
        $DB = new \Mercurio\App\Database;
        $result = $DB->DB->select('mro_conf', '*', ['name' => 'test_config']);

        $this->assertCount(1, $result);
    }

    public function testUnsetDeletesRow() {
        $DB = new \Mercurio\App\Database;
        $DB->unsetConfig('test_config');
        $config = $DB->getConfig('test_config');

        $this->assertFalse($config);
    }

}
