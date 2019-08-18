<?php
namespace Mercurio\Test;

$_SERVER['REMOTE_PORT'] = 0120;

class IdTest extends \PHPUnit\Framework\TestCase {

    public function testNewReturnsInteger() {
        $id = \Mercurio\Utils\ID::new();

        $this->assertIsInt($id);
    }

    /**
     * Utils\ID::new() must be able to retrieve at least 200 different numbers in a second
     */
    public function testNewReturnsDifferentNumbers() {
        $start = time();
        
        $i = 0;
        while ($i < 99) {
            $id1 = \Mercurio\Utils\ID::new();
            // delay for 0.01 second
            usleep(10000);
            $id2 = \Mercurio\Utils\ID::new();

            $this->assertNotEquals($id1, $id2, "ID generation test nº$i");
            $i++;
        }

        if ($i == 99) {
            $end = time();
            $result = $end - $start;

            $this->assertLessThanOrEqual(2, $result);
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