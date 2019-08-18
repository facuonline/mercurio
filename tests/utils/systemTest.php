<?php
namespace Mercurio;

class SystemTest extends \PHPUnit\Framework\TestCase {

    public function testPropertyThrowsException() {
        try {

            \Mercurio\Utils\System::property([
                'id' => 'myOwnID', 
                'stamp' => 'myOwnTimestamp'
            ]);

            $this->expectException(\Mercurio\Exception\Usage\SystemProperty::class);
        } catch (\Mercurio\Exception\Usage\SystemProperty $e) {
            $this->assertIsObject($e);
        }
    }

    public function testPropertyAddsIdAndStamp() {
        $array = \Mercurio\Utils\System::property([]);

        $this->assertIsIterable($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('stamp', $array);
    }

    public function testRequiredThrowsException() {
        try {

            \Mercurio\Utils\System::required(['test'], ['testOther'], 'testRequired');

            $this->expectException(\Mercurio\Exception\Usage\SystemRequired::class);
        } catch (\Mercurio\Exception\Usage\SystemRequired $e) {
            $this->assertIsObject($e);
        }
    }

    public function testEmptyFieldThrowsException() {
        try {

            \Mercurio\Utils\System::emptyField(['test'], ['test' => '']);

            $this->expectException(\Mercurio\Exception\User\EmptyField::class);
        } catch (\Mercurio\Exception\User\EmptyField $e) {
            $this->assertIsObject($e);
        }
    }

}
