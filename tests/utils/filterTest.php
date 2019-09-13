<?php
namespace Mercurio\Test;
use \Mercurio\Utils\Filter;
class FilterTest extends \PHPUnit\Framework\TestCase {

    public function testIsBool() {
        $test = [
            'bool' => false,
            'nonBool' => 'not a bool',
            'zero' => 0,
            'null' => NULL,
            'off' => 'off',
            'no' => 'no',
            'empty' => ''
        ];

        $this->assertIsBool(Filter::isBool($test['bool']));
        $this->assertFalse(Filter::isBool($test['nonBool']));
        // Test default, following filters should fail but fall to 'true'
        $this->assertTrue(Filter::isBool($test['nonBool'], true));
        $this->assertTrue(Filter::isBool($test['zero'], true));
        // Test flag, with default set to 'true' but with flag set to 'true' following test should return false, except for 'nonBool'
        $this->assertTrue(Filter::isBool($test['nonBool'], true, true));
        $this->assertFalse(Filter::isBool($test['zero'], true, true));
        $this->assertFalse(Filter::isBool($test['null'], true, true));
        $this->assertFalse(Filter::isBool($test['off'], true, true));
        $this->assertFalse(Filter::isBool($test['no'], true, true));
        $this->assertFalse(Filter::isBool($test['empty'], true, true));
    }

    public function testIsDomain() {
        $test = [
            'domain' => 'http://test.test.com/testing/?number=1',
            'notDomain' => str_repeat('*', 64)
        ];

        $this->assertIsString(Filter::isDomain($test['domain']));
        $this->assertNull(Filter::isDomain($test['notDomain']));
        $this->assertEquals('default', Filter::isDomain($test['notDomain'], 'default'));
    }

    public function testIsEmail() {
        $test = [
            'email' => 'test@testing.com',
            'notEmail' => 'not an email address'
        ];

        $this->assertEquals('test@testing.com', Filter::isEmail($test['email']));
        $this->assertNull(Filter::isEmail($test['notEmail']));
    }

    public function testGetString() {
        $test = "'this is a dangerous string' ` + # \ <html> ";

        $this->assertEquals("&#39;this is a dangerous string&#39; ` + # \  ", Filter::getString($test));
    }

}
