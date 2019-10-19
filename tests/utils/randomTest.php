<?php

namespace Mercurio\Test;

class RandomTest extends \PHPUnit\Framework\TestCase {

    public function testHashIsStringAndIsSHA256() {
        $hash = \Mercurio\Utils\Random::hash();

        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function testHashIsRandom() {
        $key1 = \Mercurio\Utils\Random::hash();
        $key2 = \Mercurio\Utils\Random::hash();
        
        $this->assertNotEquals($key1, $key2);
    }

}
