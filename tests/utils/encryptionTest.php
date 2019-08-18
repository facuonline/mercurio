<?php
namespace Mercurio\Test;

class EncryptionTest extends \PHPUnit\Framework\TestCase {

    public function testEncryptReturnsString() {
        $string = \Mercurio\Utils\Encryption::encrypt('test');

        $this->assertIsString($string);
    }

    public function testEncryptReturnsDifferentString() {
        $string = \Mercurio\Utils\Encryption::encrypt('test');
        
        $this->assertNotEquals('test', $string);
    }

    public function testDecryptionReturnsOriginalString() {
        $original = \Mercurio\Utils\Encryption::encrypt('test');
        $decrypted = \Mercurio\Utils\Encryption::decrypt($original);

        $this->assertEquals('test', $decrypted);
    }

}