<?php
/**
 * App *Mercurio main class*
 * @package Mercurio
 * @subpackage App class
 */
namespace Mercurio;
class AppTest extends \PHPUnit\Framework\TestCase {

    /**
     * Returns an App setting set by setApp
     * @param string $key
     * @return mixed
     * @throws object Runtime exception if setting not found
     */
    public function testGetAppThrowsExceptionError(string $key = 'URL') {
        if (getenv('APP_'.$key)) {
            if ($key == 'URL') return rtrim(getenv('APP_URL'), '/').'/';
            return getenv('APP_'.$key);
        } else {
            $exception = new \Exception("getApp could not find '$key' setting.", 400);
            $this->assertIsObject($exception);
        }
    }

    /**
     * Returns a random, safe sha256 hash to be used as app key
     * @param mixed $entropy Optional additional entropy
     * @return string
     */
    public function testGetRandomKeyReturnsString($entropy = 'b05') {
        $lame[] = microtime();
        $lame[] = mt_rand(1111, 9999);
        $lame[] = getenv('APP_URL');
        $lame[] = openssl_random_pseudo_bytes(16);
        $lame[] = $entropy;
        $glue = base64_encode(random_bytes(4));
        shuffle($lame);
        $this->assertIsString(hash('sha256', implode($glue, $lame)), 'Received a non string');
    }

}