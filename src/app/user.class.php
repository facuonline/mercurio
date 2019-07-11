<?php
/**
 * User class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var array $info Associative array with general user info
 * @var array $meta Associative array of meta values attached to user
 * 
 */
namespace Mercurio\App;
class User extends \Mercurio\App\Database {

    public $info, $meta;
    private $email;

    public function __construct() {
        $this->info = false;
        $this->meta = [];
    }

    /**
     * Finds an user hint either in $_GET or in $_SESSION
     * @return false|string|int
     */
    private function findHint() {
        if ($this->info) return $this->info['id'];
        if (\Mercurio\Utils\URL::getURLParams()['Referrer'] == 'users'
        && \Mercurio\Utils\URL::getURLParams()['Target']) {
            return \Mercurio\Utils\URL::getURLParams()['Target'];
        }
        if (\Mercurio\Utils\Session::get('User')) {
            return \Mercurio\Utils\Session::get('User')['id'];
        }
        return false;
    }

    /**
     * Load an user from database into instance
     * @param string|int $hint User identifier either string handle or integer id
     * @param callback $callback Callback function to manipulate user data without loading class
     * @param callback $fallback Callback function to execute in case of no user found
     * @return array|false User info, false on no user found
     */
    public function get($hint = false, callable $callback = NULL, callable $fallback = NULL) {
        if (!$hint) $hint = $this->findHint();
        $user = false;
        $user = $this->db()->get('mro_users', [
            'id',
            'handle',
            'nickname',
            'img',
            'stamp'
        ], [
        'OR' => [
            'id' => $hint,
            'handle' => $hint,
            'email' => $hint
        ]]);
        if ($user) {
            if ($callback !== NULL) return $callback($user);
            $this->info = $user;
            return $this->info;
        } elseif ($fallback !== NULL) {
            $fallback();
        }
    }

    /**
     * Update user properties
     * @param array $properties Associative array of user properties
     */
    public function set(array $properties) {
        $this->get(false, function($user) {
            $this->db()->update('mro_users', 
                $properties,
                $user['id']
            );
        });
    }

    /**
     * Read user meta
     * @param string|array $meta Name of meta field, leave blank to get all meta fields
     * @return bool|mixed 
     */
    public function getMeta($meta = '') {
        return $this->get(false, function($user) use ($meta) {
            if (empty($meta)) {
                return $this->db()->select('mro_meta', '*', [
                    'target' => $this->info['id']
                ])[0];
            } else {
                if (is_array($meta)) {
                    return $this->db()->get('mro_meta', [
                        'value'
                    ], [
                        'target' => $this->info['id'],
                        'name' => $meta
                    ]);
                } else {
                    return $this->db()->get('mro_meta', [
                        'value'
                    ], [
                        'target' => $this->info['id'],
                        'name' => $meta
                    ])['value'];
                }
            }
        });
    }

    /**
     * Set and update user meta
     * @param array $meta Associative array of meta names and values
     */
    public function setMeta(array $meta) {
        $this->get(false, function($user) use ($meta) {
            foreach ($meta as $key => $value) {
            if (!is_string($key)) throw new \Mercurio\Exception\Usage\StringKeysRequired('setMeta');
                if ($this->getMeta($key)) {
                    $this->db()->update('mro_meta', [
                        'value' => $value
                    ], [
                        'AND' => [
                            'target' => $user['id'],
                            'name' => $key
                        ]
                    ]);
                } else {
                    $this->db()->insert('mro_meta', [
                        'id' => \Mercurio\Utils\ID::new(),
                        'name' => $key,
                        'value' => $value,
                        'target' => $user['id'],
                        'stamp' => time() 
                    ]);
                }
            }
        });
    }

    /**
     * Load user from session into instance
     * @param callback $callback Callback function to manipulate user data without loading class
     * @return array|false User info, false on no user found
     */
    public function getSession(callable $callback = NULL) {
        $user = \Mercurio\Utils\Session::get('User', $this->get());
        if ($callback !== NULL) return $callback($user);
        $this->info = $user;
        return $this->info;
    }

    /**
     * Set a new user and load into instance 
     * @param array $properties Associative array of user properties
     * @param array $required $properties array keys of required content
     * 'handle' and 'password' are required by default
     * @return array|false User info, false on no user 
     * @throws Exception
     */
    public function new(array $properties, array $required = []) {
        // Ensure user properties are valid
        \Mercurio\Utils\System::required(['handle', 'password'], $properties, 'new');
        \Mercurio\Utils\System::emptyField($required, $properties);
        $properties = \Mercurio\Utils\System::property(['id', 'timestamp'], $properties);
        // handle
        $properties['handle'] = $this->validateHandle($properties['handle']);
        // password
        $properties['password'] = password_hash($properties['password'], PASSWORD_DEFAULT);
        // email
        if (array_key_exists('email', $properties)
        && $this->get('email')) throw new \Mercurio\Exception\User\ExistingEmail;
        // Make user
        $this->db()->insert('mro_users',
            $properties,
        );
        $this->get($properties['id']);
        // Make basic meta
        $this->setMeta([
            'login_attempt' => 0,
            'login_lastin' => time(),
            'login_lastout' => '',
            'login_blocked' => 0,
        ]);
        return $this->info;
    }

    /**
     * Get user numeric id
     * @return int User ID
     */
    public function getID() {
        return $this->get(false, function($user) {
            return (int) $user['id'];
        });
    }

    /**
     * Get user handle
     * @return string User handle with @
     */
    public function getHandle() {
        return $this->get(false, function($user) {
            return '@'.$user['handle'];
        });
    }

    /**
     * Get user email
     * @return string|false User email, false on no email
     */
    public function getEmail() {
        return $this->get(false, function($user) {
            return $this->email;
        });
    }

    /**
     * Perform a login
     * @param string $credential User identifier: handle or email
     * @param string $password User password
     * @param string $redirect Destination after successful login
     * @return bool
     * @throws object Exception\User\WrongLoginCredential | LoginBlocked or Exception\User\EmptyField
     */
    public function login(string $credential, string $password, string $redirect = '') {
        if ($this->getSession()) $this->logout();
        \Mercurio\Utils\System::emptyField(['credential', 'password'], [
            'credential' => $credential,
            'password' => $password
        ]);
        // session enforced bruteforce protection for wrong credential
        if (!$this->get($credential)) {
            $attempts = \Mercurio\Utils\Session::get('loginAttempts', 0);
            sleep($attempts++);
            \Mercurio\Utils\Session::set($attempts, 'loginAttempts');
            throw new \Mercurio\Exception\User\WrongLoginCredential;
        // database enforced bruteforce protection for wrong password
        } else {
            $lock = $this->getMeta('login_blocked');
            if ($lock !== 0 || $lock === NULL) {
                $attempts = $this->getMeta('login_attempt');
                $hash = $this->db()->get('mro_users', [
                    'password'
                ], [
                    'id' => $this->info['id']
                ])['password'];
                if (!password_verify($password, $hash)) {
                    sleep($attempts++);
                    $this->setMeta(['login_attempt' => $attempts]);
                    throw new \Mercurio\Exception\User\WrongLoginCredential;
                    if ($attempts > 9) {
                        $this->setMeta(['login_blocked' => time() + 300]);
                        throw new \Mercurio\Exception\User\LoginBlocked;
                    }
                // Attach session and redirect user
                } else {
                    \Mercurio\Utils\Session::regenerate(0);
                    \Mercurio\Utils\Session::set($this->info, 'User', true);
                    $this->setMeta(['login_lastin' => time()]);
                    header('Location: '.$redirect);
                }
            } elseif (time() - $lock > 300) {
                $this->setMeta(['login_blocked' => 0]);
                $this->login($credential, $password, $redirect);
            }
        }

    }

    /**
     * Perform a logout
     * @param string $redirect Destination after logout
     */
    public function logout(string $redirect = '') {
        $this->setMeta(['login_lastout' => time()]);
        \Mercurio\Utils\Session::unset('User');
        \Mercurio\Utils\Session::regenerate();
        if (empty($redirect)) $redirect = getenv('APP_URL');
        header('Location: '.$redirect);
    }

    /**
     * Check validity and availability of an user handle and transform it into a valid one
     * @param string $handle
     * @return string Valid handle
     */
    public function validateHandle(string $handle) {
        if ($this->get($handle)) throw new \Mercurio\Exception\User\ExistingHandle;
        $handle = strtolower($handle);
        $handle = preg_replace('/[^a-z0-9_]/', '', $handle);
        return $handle;
    }

}
