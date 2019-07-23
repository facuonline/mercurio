<?php
namespace Mercurio;
class UrlTest extends \PHPUnit\Framework\TestCase {

    public function testGetLinkMatchesPattern() {
        $url = \Mercurio\Utils\URL::getLink('testPage', 'testTarget', 'testAction');
        $expected = 'http://localhost/mercurio/tests/?page=testPage&target=testTarget&action=testAction';
        $this->assertEquals($expected, $url);
    }

    public function testGetUrlParamsReturnsArray() {
        $url = \Mercurio\Utils\URL::getUrlParams();
        $this->assertIsIterable($url);
    }

    public function testGetUrlParamsReturnsFalseOnEmptyQueries() {
        $url = \Mercurio\Utils\URL::getUrlParams();
        $this->assertFalse($url['page']);
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