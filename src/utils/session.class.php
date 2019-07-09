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

    /**
     * Returns the memory segment for Mercurio in $_SESSION
     * @param string $key Session segment key
     * @param mixed $fallback Value to assign to key if key does not exists
     * @return array|mixed Full Mercurio session array or key value
     */
    public static function get(string $key = '', $fallback = []) {
        if (!isset($_SESSION['Mercurio'])) {
            $_SESSION['Mercurio'] = [
                'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
                'IPAddress' => $_SERVER['REMOTE_ADDR'],
                'CreatedAt' => time(),
                'User' => false,
            ];
        }
        if (empty($key)) {
            return $_SESSION['Mercurio'];
        } else {
            if (!array_key_exists($key, $_SESSION['Mercurio'])) $_SESSION['Mercurio'][$key] = $fallback;
            return $_SESSION['Mercurio'][$key];
        }
    }

    /**
     * Start session
     * @param int $expirancy Numbers of seconds to check for timeout on every session
     * @param array $options Options to be set for session_start
     */
    public static function start($expirancy = false, array $options = []) {
        if (empty($options)) {
            $options = [
                'cookie_domain' => $_SERVER['SERVER_NAME'],
                'cookie_secure' => (isset($_SERVER['HTTPS']) ? true : false),
                'sid_length' => '32',
            ];
        }
        session_start($options);
        // check for expired sessions
        if (isset($_SESSION['Mercurio']['Session']['Expiry']) 
        && $_SESSION['Mercurio']['Session']['Expiry'] < time()) {
            self::unset();
        }
        if ($expirancy) {
            if ((is_int($expirancy) ? self::timeOut($expirancy) : self::timeOut())) {
                self::unset();
            }
        }
    }

    /**
     * Regenerates a session
     * @param int $time Number of seconds to destroy the old session values
     */
    public static function regenerate(int $time = 10) {
        // flag old session
        self::set(time() + $time, 'Expiry');
        session_regenerate_id(false);
        // start new one
        $newSession = session_id();
        session_write_close();
        session_id($newSession);
        self::start();
        self::unset('Expiry');
    }

    /**
     * Check session credentials, will destroy a session on error
     * @throws object SessionInvalid exception
     */
    public static function isValid() {
        $Session = self::get();
        if (!isset($Session['IPAddress']) 
        || !isset($Session['UserAgent'])) {
            session_destroy();
            throw new \Mercurio\Exception\SessionInvalid("Session User agent and IP address do not match. Method ended under suspicion of session hijacking.", 1);
        }
        if ($Session['IPAddress'] !== substr($_SERVER['REMOTE_ADDR'], 0, 7)
        && $Session['UserAgent'] !== $_SERVER['HTTP_USER_AGENT']) {
            session_destroy();
            throw new \Mercurio\Exception\SessionInvalid("Session User agent and IP address do not match. Method ended under suspicion of session hijacking.", 1);
        }
    } 

    /**
     * Check if the session has run out of time
     * @param int $time Number of seconds after session was created to perform the timeout
     * @return bool True if out of time, false if not
     */
    public static function timeOut(int $time = 900) {
        $Session = self::get();
        if ($Session['CreatedAt'] < (time() - $time)) {
           return true;
        } else {
            return false;
        }
    }
    
    /**
     * Store a value inside session
     * @param mixed $value Data to be stored
     * @param string $segment Session key under which to store data
     * @param bool $regenerate Regenerates session id
     */
    public static function set($value, string $segment, bool $regenerate = true) {
        self::isValid();
        $_SESSION['Mercurio'][$segment] = $value;
        if ($regenerate) session_regenerate_id(true);
    }

    /**
     * Destroy a session variable or all of them if none specified
     * @param string $segment Session key of data
     * @param bool $regenerate Regenerates session id
     */
    public function unset(string $segment, bool $regenerate = true) {
        self::isValid();
        if ($value) {
            unset($_SESSION['Mercurio'][$segment]);
        } else {
            session_unset();
        }
        if ($regenerate) session_regenerate_id(true);
    }

}