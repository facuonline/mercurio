<?php
namespace Mercurio\Test;
class IdTest extends \PHPUnit\Framework\TestCase {

    public function testNewReturnsInteger() {
        // We fake this because this is what ID will find in real world environments
        $_SERVER['REMOTE_PORT'] = '7000';
        $_SERVER['REMOTE_ADDR'] = '123.456.789.0';
        $id = \Mercurio\Utils\ID::new();

        $this->assertIsInt($id);
    }

    /**
     * Utils\ID::new() must be able to retrieve at least 1000 different numbers
     */
    public function testNewReturnsDifferentNumbers() {
        // We fake this because this is what ID will find in real world environments
        $_SERVER['REMOTE_PORT'] = '7000';
        $_SERVER['REMOTE_ADDR'] = '123.456.789.0';
        
        $i = 0;
        while ($i < 499) {
            $id1 = \Mercurio\Utils\ID::new();
            /* Delay for 0.006 second not precisely to simulate system latency 
            but the fastest human latency actually, 10 miliseconds buffer to accomodate actual latency of running system*/
            usleep(5990);
            $id2 = \Mercurio\Utils\ID::new();

            $this->assertNotEquals($id1, $id2, "ID generation test nº$i:");
            $i++;
        }
    }
    
    public function testEncryptReturnsEncryptedOnEmptyCall() {
        $id = \Mercurio\Utils\ID::encrypt();
        
        $this->assertIsString($id);
    }

    public function testEncryptReturnsStringOnIntInput() {
        $id = '1234567890000';
        $id = \Mercurio\Utils\ID::encrypt($id);

        $this->assertIsString($id);
    }

    public function testDecryptReturnsDecryptedInteger() {
        $id = '1234567890000';
        $id = \Mercurio\Utils\ID::encrypt($id);
        $id = \Mercurio\Utils\ID::decrypt($id);

        $this->assertIsInt($id);
        $this->assertEquals('1234567890000', $id);
    }

}