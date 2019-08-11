<?php
namespace Mercurio;

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

    public function testSetMetaCreatesRecord() {
        $db = new \Mercurio\App\Database;
        $db->dbSetMeta(1111, [
            'test_meta' => 'test Value',
            'test_meta2' => 'second test value'
        ], 'testing');
        $meta = $db->dbGetMeta('1111');

        $this->assertIsIterable($meta);
    }

    public function testGetMetaAll() {
        $db = new \Mercurio\App\Database;
        $meta = $db->dbGetMeta(1111);

        $this->assertIsIterable($meta);
        $this->assertArrayHasKey(0, $meta);
        $this->assertArrayHasKey(1, $meta);
        $this->assertEquals('test Value', $meta[0]['value']);
    }

    public function testGetMetaByArray() {
        $db = new \Mercurio\App\Database;
        $meta = $db->dbGetMeta(1111, ['test_meta', 'test_meta2']);

        $this->assertIsIterable($meta);
        $this->assertArrayHasKey(0, $meta);
        $this->assertArrayHasKey(1, $meta);
        $this->assertEquals('test Value', $meta[0]['value']);
    }

    public function testGetMetaByString() {
        $db = new \Mercurio\App\Database;
        $meta = $db->dbGetMeta(1111, 'test_meta');

        $this->assertIsIterable($meta);
        $this->assertArrayNotHasKey(0, $meta);
        $this->assertEquals('test Value', $meta['value']);
    }

    public function testGetMetaByGroup() {
        $db = new \Mercurio\App\Database;
        $meta = $db->dbGetMeta(1111, '', 'testing');

        $this->assertIsIterable($meta);
        $this->assertArrayHasKey(0, $meta);
        $this->assertEquals('test Value', $meta[0]['value']);
    }

    public function testGetMetaInexistentKey() {
        $db = new \Mercurio\App\Database;
        $meta = $db->dbGetMeta(0000, 'test_meta');

        $this->assertNull($meta);
    }

    public function testSetMetaUpdates() {
        $db = new \Mercurio\App\Database;
        $db->dbSetMeta(1111, ['test_meta' => 'updated value']);
        $meta = $db->dbGetMeta(1111, 'test_meta');

        $this->assertEquals('updated value', $meta['value']);
        $this->assertEquals('testing', $meta['grouping']);
    }

    public function testUnsetMetaDeletes() {
        $db = new \Mercurio\App\Database;
        $db->dbUnsetMeta(1111);
        $meta = $db->dbGetMeta(1111, 'test_meta');

        $this->assertNull($meta);
    }

}
