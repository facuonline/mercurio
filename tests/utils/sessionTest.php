<?php
namespace Mercurio;
/**
 * We'll be mocking apache $_SERVER and $_SESSION, shhh, this test does not aim to probe how well does Apache work anyways
 */
$_SERVER['SERVER_NAME'] = 'test';
$_SERVER['REMOTE_ADDR'] = 'localhost';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';
\Mercurio\Utils\Session::start();
class SessionTest extends \PHPUnit\Framework\TestCase {

    public function testGetReturnsArray() {
        $session = \Mercurio\Utils\Session::get();
        $this->assertIsIterable($session);
    }

    public function testSessionArrayHasControlKeys() {
        $session = \Mercurio\Utils\Session::get();
        $this->assertArrayHasKey('UserAgent', $session);
        $this->assertArrayHasKey('IPAddress', $session);
        $this->assertArrayHasKey('CreatedAt', $session);
    }

    public function testGetAssignsFallback() {
        $fallback = \Mercurio\Utils\Session::get('testFallback', 'fallbackDefaultAssigned');
        $this->assertSame('fallbackDefaultAssigned', $fallback);
    }

    public function testSetAssignsValues() {
        \Mercurio\Utils\Session::set('test', ['testKey' => 'testKeyValue'], false);
        $setted = \Mercurio\Utils\Session::get('test');
        $this->assertIsArray($setted);
    }

    public function testIsValidThrowsException() {
        try {
            unset($_SESSION['Mercurio']['IPAddress']);

            \Mercurio\Utils\Session::isValid();
            $this->expectException(\Mercurio\Exception\SessionInvalid::class);
        } catch (\Mercurio\Exception\SessionInvalid $e) {
            $this->assertIsObject($e);
        }
    }

    public function testTimeOutReturnsBool() {
        $this->assertIsBool(\Mercurio\Utils\Session::timeOut());
    }
}
