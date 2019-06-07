<?php
/**
 * Session
 * @package Mercurio
 * @subpackage Utilitary classes
 * 
 * Improved Session management
 * Not as improved as other solutions out there
 * 
 * @var array $session
 * @var array $segment
 * 
 */
namespace Mercurio\Utils;
class SessionTest extends \PHPUnit\Framework\TestCase{

    /**
     * Returns the memory segment for Mercurio in $_SESSION
     * @return array
     */
    public static function testGetReturnsArray() {
        if (!isset($_SESSION['Mercurio'])) {
            $_SESSION['Mercurio'] = [
                'CreatedAt' => time(),
                'User' => false,
            ];
        }
        self::assertIsArray($_SESSION['Mercurio']);
        self::assertArrayHasKey('CreatedAt', $_SESSION['Mercurio']);
        self::assertFalse($_SESSION['Mercurio']['User']);
    }

}