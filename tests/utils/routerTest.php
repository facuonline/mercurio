<?php
namespace Mercurio\Test;
class RouterTest extends \PHPUnit\Framework\TestCase {

    public function testIsMaskingOnReturnsBool() {
        $bool = \Mercurio\Utils\Router::isMaskingOn();

        $this->assertIsBool($bool);
    }

    public function testGetLinkMatchesPattern() {
        $url = \Mercurio\Utils\Router::getLink('testPage', 'testTarget', 'testAction');
        if (\Mercurio\Utils\Router::isMaskingOn()) {
            $expected = 'http://localhost/mercurio/tests/testPage/testTarget/testAction';
        } else {
            $expected = 'http://localhost/mercurio/tests/?page=testPage&target=testTarget&action=testAction';
        }

        $this->assertEquals($expected, $url);
    }

    public function testGetUrlParamsReturnsArray() {
        $url = \Mercurio\Utils\Router::getUrlParams();
        
        $this->assertIsIterable($url);
    }

    public function testGetUrlParamsReturnsDefaultOnEmptyQueries() {
        $url = \Mercurio\Utils\Router::getUrlParams();

        $this->assertFalse($url['page']);
        $this->assertNull($url['target']);
        // empty actions are true by default
        $this->assertTrue($url['action']);
    }

    public function testGetUrlParamsReturnsMainPageOnEmptyQuery() {
        $page = \Mercurio\Utils\Router::getUrlParams('/')['page'];

        $this->assertTrue($page);
    }

    /**
     * Utils\Router::setRoute() must successfully route 100 requests in less than a second
     */
    public function testSetRouteSpeed() {
        $start = time();
        $end = time();
        $i = 0;
        while ($i < 99) {
            \Mercurio\Utils\Router::setRoute('/', '', function() use (&$end){
               $end = time();
            });
            $i++;
        }

        if ($i == 99) {
            $result = $start - $end;

            $this->assertEquals(0, $result);
        }
    }

    public function testSetUrlMaskingCreatesHtaccess() {
        try {
            \Mercurio\Utils\Router::setUrlMasking();

            $this->expectException(\Mercurio\Exception\Environment::class);

            $this->assertFileExists('.htaccess');
            unlink('.htaccess');
        } catch (\Mercurio\Exception\Environment $e) {
            $this->assertIsObject($e);
        }
    }

}