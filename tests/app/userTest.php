<?php
namespace Mercurio\Test;

class UserTest extends \PHPUnit\Framework\TestCase {

    public function testGetReturnsNULLOnNoUserHint() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $get = $user->get();

        $this->assertNull($get);
    }

    public function testNewCreatesDatabaseRecord() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        if ($user->get('test_handle')) $user->unset();
        $new = $user->new([
            'handle' => 'test_handle',
            'password' => 'test_password'
        ]);

        $this->assertIsIterable($new);
        $this->assertArrayHasKey('handle', $user->info);
        $this->assertArrayHasKey('nickname', $user->info);
        $this->assertArrayHasKey('img', $user->info);
    }

    public function testGetReturnsUserInfoArray() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $info = $user->get('test_handle');

        $this->assertIsIterable($info);
    }

    public function testSetUpdatesDatabaseAndGetNicknameReturnsNickname() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $user->set([
            'nickname' => 'test Nickname',
        ]);

        $this->assertEquals('test Nickname', $user->getNickname());
    }

    public function testGetImgReturnsFalse() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $false = $user->getImg();

        $this->assertFalse($false);
    }

    /**
     * I don't know how to test for image creation without submitting an actual file via POST
     */

    public function testGetIDReturnsIntegerAndString() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $int = $user->getID();
        $string = $user->getID(true);

        $this->assertIsInt($int);
        $this->assertIsString($string);
    }

    public function testGetHandleReturnsWithAndWithoutArroba() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $handle = $user->getHandle();
        $arroba = $user->getHandle(true);

        $this->assertIsString($handle);
        $this->assertStringStartsWith('t', $handle);
        $this->assertIsString($arroba);
        $this->assertStringStartsWith('@', $arroba);
    }

    public function testGetEmailReturnsString() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $email = $user->getEmail();

        $this->assertIsString($email);
        $this->assertEquals('', $email);
    }

    public function testGetLinkReturnsString() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $link = $user->getLink('user', 'action');

        $this->assertIsString($link);
    }

    /**
     * For some reason login seems to break PHPUnit and populates db with meta fields non stop
     * but it works on the browser
     */

    public function testUnsetDeletesUserFromDatabase() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $user->get('test_handle');
        $user->unset();

        $this->assertNull($user->get('test_handle'));
    }

    public function testValidateHandleReturnsValidStringHandle() {
        $user = new \Mercurio\App\User(new \Mercurio\App\Database);
        $handle = $user->validateHandle('Not a valid handle. ');

        $this->assertIsString($handle);
        $this->assertEquals('notavalidhandle', $handle);
        $this->assertStringEndsNotWith(' ', $handle);
    }

}