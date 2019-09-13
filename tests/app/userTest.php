<?php
namespace Mercurio\Test;
class UserTest extends \PHPUnit\Framework\TestCase {

    protected $user;

    protected function setUp(): void {
        $this->user = new \Mercurio\App\User(new \Mercurio\App\Database);
    }

    public function testUserInfoIsEmpty() {
        $this->assertIsIterable($this->user->info);

        foreach($this->user->info as $key => $value) {
            $this->assertNull($value);
        }
    }

}
