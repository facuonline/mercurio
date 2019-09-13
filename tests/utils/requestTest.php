<?php
namespace Mercurio\Test;
class RequestTest extends \PHPUnit\Framework\TestCase {

    public function testNewRequest() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/mercurio/tests/new/request/';
        $request = new \Mercurio\Utils\Request;

        $this->assertEquals('new/request', $request->route);
    }

    public function testEmptyGet() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '';
        $request = new \Mercurio\Utils\Request;

        $this->assertEquals('/', $request->route);
    }

}
