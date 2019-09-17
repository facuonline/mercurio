<?php
namespace Mercurio\Test;

\Mercurio\App::setApp([
    'KEY' => 'test',
    'URL' => 'http://localhost/mercurio/tests',
    'CUSTOM' => 'customEnvironmentalVariable'
], [
    'TYPE' => 'mysql',
    'HOST' => 'localhost',
    'USER' => 'root',
    'PASS' => '',
    'NAME' => 'mercurio'
]);

class AppTest extends \PHPUnit\Framework\TestCase {

    public function testSetAppDefinesEnvironmentalVariables() {
        $this->assertEquals('test', getenv('APP_KEY'));
        $this->assertEquals('customEnvironmentalVariable', getenv('APP_CUSTOM'));
    }

    public function testSetAppDefinesConstants() {
        $this->assertEquals('http://localhost/mercurio/tests/', APP_URL);
        $this->assertIsString(APP_STATIC);
        $this->assertIsString(APP_STATIC_LINK);
        $this->assertIsString(APP_USERSTATIC);
        $this->assertIsString(APP_USERSTATIC_LINK);
    }

    public function testSetAppDefinesDatabaseConstants() {
        $this->assertEquals('mro_', DB_PREFIX);
        $this->assertEquals('mro_conf', DB_CONF);
        $this->assertIsString(DB_META);
        $this->assertIsString(DB_USERS);
        $this->assertIsString(DB_CHANNELS);
        $this->assertIsString(DB_MEDIA);
    }

    public function testGetAppTrimsUrl() {
        $this->assertStringEndsWith('/', \Mercurio\App::getApp('URL'));
    }

    public function testRandomKeyReturnsString() {
        $this->assertIsString(\Mercurio\App::randomKey());
    }

    public function testRandomKeyIsRandom() {
        $key1 = \Mercurio\App::randomKey();
        $key2 = \Mercurio\App::randomKey();
        $this->assertNotEquals($key1, $key2);
    }

}