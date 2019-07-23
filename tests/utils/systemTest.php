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

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('stamp', $array);
    }

}