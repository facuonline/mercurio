<?php
namespace Mercurio;
class UrlTest extends \PHPUnit\Framework\TestCase {

    public function testIsMaskingOnReturnsBool() {
        $bool = \Mercurio\Utils\URL::isMaskingOn();

        $this->assertIsBool($bool);
    }

    public function testGetLinkMatchesPattern() {
        $url = \Mercurio\Utils\URL::getLink('testPage', 'testTarget', 'testAction');
        if (\Mercurio\Utils\URL::isMaskingOn()) {
            $expected = 'http://localhost/mercurio/tests/testPage/testTarget/testAction';
        } else {
            $expected = 'http://localhost/mercurio/tests/?page=testPage&target=testTarget&action=testAction';
        }

        $this->assertEquals($expected, $url);
    }

    public function testGetUrlParamsReturnsArray() {
        $url = \Mercurio\Utils\URL::getUrlParams();
        
        $this->assertIsIterable($url);
    }

    public function testGetUrlParamsReturnsMainPageOnEmptyQuery() {
        $page = \Mercurio\Utils\URL::getUrlParams('/')['page'];

        $this->assertEquals('main', $page);
    }

    public function testGetUrlParamsReturnsFalseOnEmptyQueries() {
        $url = \Mercurio\Utils\URL::getUrlParams();

        $this->assertFalse($url['page']);
        $this->assertFalse($url['target']);
        $this->assertFalse($url['action']);
    }

    public function testSetRouteSpeed() {
        $start = time();
        $end = time();
        $i = 0;
        while ($i < 99) {
            \Mercurio\Utils\URL::setRoute('/', '', function() use (&$end){
               $end = time();
            });
            $i++;
        }

        $result = $start - $end;

        $this->assertEquals('0', $result);
    }

    public function testSetUrlMaskingCreatesHtaccess() {
        try {
            \Mercurio\Utils\URL::setUrlMasking();

            $this->expectException(\Mercurio\Exception\Environment::class);

            $this->assertFileExists('.htaccess');
            unlink('.htaccess');
        } catch (\Mercurio\Exception\Environment $e) {
            $this->assertIsObject($e);
        }
    }

}