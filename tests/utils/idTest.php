<?php
namespace Mercurio;

$_SERVER['REMOTE_PORT'] = 0120;

class IdTest extends \PHPUnit\Framework\TestCase {

    public function testNewReturnsInteger() {
        $id = \Mercurio\Utils\ID::new();

        $this->assertIsInt($id);
    }

    /**
     * Utils\ID::new() must be able to retrieve at least 10 different numbers
     * in a second
     */
    public function testNewReturnsDifferentNumbers() {
        $i = 0;
        while ($i < 9) {
            $id1 = \Mercurio\Utils\ID::new();
            // delay for 0.1 second
            usleep(100000);
            $id2 = \Mercurio\Utils\ID::new();

            $this->assertNotEquals($id1, $id2);
            $i++;
        }
    }
    
    public function testEncReturnsEncryptedOnEmptyCall() {
        $id = \Mercurio\Utils\ID::enc();
        
        $this->assertIsString($id);
    }

    public function testEncReturnsStringOnIntInput() {
        $id = '1234567890000';
        $id = \Mercurio\Utils\ID::enc($id);

        $this->assertIsString($id);
    }

}