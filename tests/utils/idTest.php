<?php
namespace Mercurio\Test;

// We fake this because this is what ID will find in real world environments
$_SERVER['REMOTE_PORT'] = '1234';
$_SERVER['REMOTE_ADDR'] = '123.45.678.9';

class IdTest extends \PHPUnit\Framework\TestCase {

    public function testNewReturnsInteger() {
        $id = \Mercurio\Utils\ID::new();

        $this->assertIsInt($id);
    }

    /**
     * Utils\ID::new() must be able to retrieve at least 1000 different numbers in a second
     */
    public function testNewReturnsDifferentNumbers() {
        // We fake this because this is what ID will find in real world environments
        $_SERVER['REMOTE_PORT'] = '1234';
        $_SERVER['REMOTE_ADDR'] = '123.45.678.9';
        $start = time();
        
        $i = 0;
        while ($i < 499) {
            $id1 = \Mercurio\Utils\ID::new();
            /* Delay for 0.006 second not precisely to simulate system latency 
            but the fastest human latency actually */
            usleep(6000);
            $id2 = \Mercurio\Utils\ID::new();

            $this->assertNotEquals($id1, $id2, "ID generation test nº$i:");
            $i++;
        }

        if ($i == 499) {
            $end = time();
            $result = $end - $start;

            // We check against 4 to take in consideration the hardcoded delay that takes 3 seconds
            $this->assertLessThanOrEqual(4, $result);
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