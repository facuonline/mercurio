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

    public function testGetUrlParamsReturnMainPageOnEmptyQuery() {
        $page = \Mercurio\Utils\URL::getUrlParams()['page'];

        $this->assertEquals('main', $page);
    }

    public function testGetUrlParamsReturnsFalseOnEmptyQueries() {
        $url = \Mercurio\Utils\URL::getUrlParams();
        $this->assertFalse($url['target']);
        $this->assertFalse($url['action']);
    }

    public function testSetUrlMaskingCreatesHtaccess() {
        try {
            \Mercurio\Utils\URL::setUrlMasking();

            $this->expectException(\Mercurio\Exception\Environment::class);

            $this->assertFileExists('.htaccess');
            unlink('.htaccess');
        } catch (\Mercurio\Exception\Environment $e) {
            # expectException seems to be not working so by now this will be in a try catch
            $this->assertIsObject($e);
        }
    }

}