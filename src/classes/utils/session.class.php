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
class Session {

    public $session;

    public function __construct() {
        if (!isset($_SESSION['Mercurio'])) {
            $this->start();
            $_SESSION['Mercurio'] = [
                'UserAgent' => $_SERVER ['HTTP_USER_AGENT'],
                'CreatedAt' => time(),
                'User' => false,
            ];
            $this->session = $_SESSION['Mercurio'];
        } else {
            return $this->session;
        }
    }

    /**
     * Start session
     * @param array $options Options to be set for session_start
     */
    public function start(array $options = []) {
        if (empty($options)) {
            $options = [
                'cookie_domain' => $_SERVER['SERVER_NAME'],
                'cookie_secure' => (isset($_SERVER['HTTPS']) ? true : false),
                'sid_length' => '32',
            ];
        }
        session_start($options);
    }

    /**
     * Check if the session has run out of time
     * @param int $time Number of seconds after session was created to perform the timeout
     * @return bool True if out of time, false if not
     */
    public function timeOut(int $time = 900) {
        if ($this->session['CreatedAt'] < (time() - $time)) {
           return true;
        } else {
            return false;
        }
    }
    
    /**
     * Store a value inside session
     * @param string $segment String segment identifier
     * @param mixed $value
     */
    public function set($segment = 'Session', $value, $regenerate = true) {
        $_SESSION['Mercurio'][$segment] = $value;
        if ($regenerate) {
            session_regenerate_id(true);
        }
    }

    /**
     * Destroy a session variable or all of them if none specified
     * @param mixed $value
     */
    public function unset($value = false, $regenerate = true) {
        if ($value) {
            unset($_SESSION['Mercurio'][$value]);
        } else {
            session_unset();
        }
        if ($regenerate) {
            session_regenerate_id(true);
        }
    }

}