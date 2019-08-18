<?php

namespace Mercurio\App;

/**
 * User entity class
 * @package Mercurio
 * @subpackage Included classes
 */
class User {

    /**
     * Associative array with general user info
     */
    public $info;

    /**
     * Associative array with user meta properties and values
     */
    public $meta;

    /**
     * User private email, use method getEmail
     */
    private $email;
    
    /**
     * Instance of dependency injected Database class
     */
    protected $DB;

    /**
     * SQL query builder
     */
    private $SQL;
    
    public function __construct(\Mercurio\App\Database $db) {
        $this->info = false;
        $this->meta = [];
        $this->DB = $db;
        $this->SQL = $db->getSQL();
    }

    /**
     * Finds an user hint either in $_GET or in $_SESSION
     * @return null|string|int
     */
    protected function findHint() {
        if ($this->info) return $this->info['id'];
        // Get user hint from URL query
        if (\Mercurio\Utils\Router::getTarget()) return ltrim(\Mercurio\Utils\Router::getTarget(), '@');
        // Get user hint from session
        if (\Mercurio\Utils\Session::get('User', false)) return \Mercurio\Utils\Session::get('User')['id'];
        
        return NULL;
    }

    /**
     * Load an user from database into instance
     * @param string|int $hint User identifier either string handle or integer id
     * @param callable $callback Callback function to manipulate user data without loading class
     * function (array $info) :
     * @param callable $fallback Callback function to execute in case of no user found
     * function () :
     * @return array|false User info, false on no user found
     */
    public function get($hint = false, callable $callback = NULL, callable $fallback = NULL) {
        if (!$hint) $hint = $this->findHint();
        $user = $this->SQL->get(DB_USERS, [
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
            ]
        ]);
        // Return data or load instance
        if ($user) {
            if ($callback !== NULL) return $callback($user);

            $this->info = $user;
            return $this->info;
        } elseif ($fallback !== NULL) {
            return $fallback();
        }
    }

    /**
     * Update user properties
     * @param array $properties Associative array of user properties
     */
    public function set(array $properties) {
        $this->get(false, function($user) use (&$properties) {
            $this->SQL->update(DB_USERS, 
                $properties,
                ['id' => $user['id']]
            );
        });
    }

    /**
     * Deletes user from database and it's associated data
     * @param array $tables Array of database tables to delete rows from where user is author
     */
    public function unset(array $tables = []) {
        $this->get(false, function ($user) use (&$tables) {
            $this->unsetImg();
            $this->unsetMeta();
            $this->SQL->delete(DB_USERS, ['id' => $user['id']]);

            if (!empty($tables)) foreach ($tables as $key => $value) {
                $this->SQL->delete($value, ['author' => $user['id']]);
            }
        });
    }

    /**
     * Read user meta
     * @param string $meta Name of meta field or array of, leave blank to get all meta fields
     * @param string $grouping Name of meta group
     * @return bool|mixed|array
     */
    public function getMeta($meta = '', string $grouping = '') {
        return $this->get(false, function($user) use (&$meta, $grouping) {
            return $this->DB->dbGetMeta($user['id'], $meta, $grouping);
        });
    }

    /**
     * Set and update user meta
     * @param array $meta Associative array of meta names and values
     * @param string $grouping Name of meta group
     */
    public function setMeta(array $meta, string $grouping = '') {
        $this->get(false, function($user) use (&$meta, $grouping) {
            $this->DB->dbSetMeta($user['id'], $meta, $grouping);
        });
    }

    /**
     * Deletes user media from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     * @param string $grouping Name of meta group
     */
    public function unsetMeta($meta = '', string $grouping = '') {
        $this->get(false, function ($user) use (&$meta, $grouping) {
            $this->DB->dbUnsetMeta($user['id'], $meta, $grouping);
        });
    }

    /**
     * Get user img property as a full absolute path
     * @return string User img
     */
    public function getImg() {
        return $this->get(false, function($user) {
            if (!empty($user['img'])
            && file_exists(APP_USERSTATIC.$user['img'])) return APP_USERSTATIC_ABS.$user['img'];
            return false;
        });
    }

    /**
     * Upload and set a file image as user img
     * @param array $file $_FILES array key
     * @param int $width Desired output width of the image
     * @param int|bool $ratio Tells the method wether to calc the output height based on the new width or use the defined height (will crop the image), if left to true will crop the image with an aspect ratio based height
     */
    public function setImg(array $file, int $width, $ratio = false) {
        // Delete previous
        if ($this->getImg()) unlink(
            APP_USERSTATIC
            .$this->get(false, function($user) {
                return $user['img'];
            })
        );
        // Upload new
        $image = new \Mercurio\Utils\Img;
        $image->new($file, APP_USERSTATIC, $width, $ratio);
        $this->set(['img' => $image->hash]);
    }

    /**
     * Deletes user img from database and related file from statics
     */
    public function unsetImg() {
        if ($this->getImg()) unlink(
            APP_USERSTATIC
            .$this->get(false, function($user) {
                return $user['img'];
            })
        );
        $this->set(['img' => '']);
    }

    /**
     * Load user from session into instance
     * @param callable $callback Callback function to manipulate user data without loading class
     * function (array $info) :
     * @return array|false User info, false on no user found
     */
    public function getSession(callable $callback = NULL) {
        $user = \Mercurio\Utils\Session::get('User', false);

        if ($callback !== NULL) return $callback($user);

        $this->info = $user;
        return $this->info;
    }

    /**
     * Load user from instance to session
     * @param bool|int|string $hint User hint
     * @param bool $regenerate Regenerate or not session id
     */
    public function setSession($hint = false, bool $regenerate = false) {
        $this->get($hint, function($user) use (&$regenerate) {
            \Mercurio\Utils\Session::set('User', $user, $regenerate);
        });
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
        $properties = \Mercurio\Utils\System::property($properties);

        // handle
        $properties['handle'] = $this->validateHandle($properties['handle']);
        // password
        $properties['password'] = password_hash($properties['password'], PASSWORD_DEFAULT);
        // email
        if (array_key_exists('email', $properties)
        && $this->get($properties['email'])) throw new \Mercurio\Exception\User\ExistingEmail;

        // Make user
        $this->SQL->insert(DB_USERS, $properties);
        $this->get($properties['id']);
        // Make basic meta
        $this->setMeta([
            'login_attempt' => 0,
            'login_lastin' => time(),
            'login_lastout' => '',
            'login_blocked' => 0,
        ], 'mrologin');
        return $this->info;
    }

    /**
     * Get user numeric id
     * @param bool $string Return id as string
     * @return int|string User ID
     */
    public function getID(bool $string = false) {
        return $this->get(false, function($user) use (&$string) {
            if ($string) return (string) $user['id'];
            return (int) $user['id'];
        });
    }

    /**
     * Get user handle
     * @param bool $arroba Return handle with or without @
     * @return string User handle
     */
    public function getHandle(bool $arroba = false) {
        return $this->get(false, function($user) use (&$arroba) {
            if ($arroba) return (string) '@'.$user['handle'];
            return (string) $user['handle'];
        });
    }

    /**
     * Get user public name
     * @return string User nickname
     */
    public function getNickname() {
        return $this->get(false, function($user) {
            return (string) $user['nickname'];
        });
    }

    /**
     * Get user email
     * @return string User email
     */
    public function getEmail() {
        return $this->get(false, function($user) {
            return (string) $this->SQL->get(DB_USERS, 
                ['email'], 
                ['id' => $user['id']]
            )['email'];
        });
    }

    /**
     * Get absolute link to user profile
     * @param string $page Users page
     * @param string $action Optional user action
     * @return string URL
     */
    public function getLink(string $page, string $action = '') {
        return $this->get(false, function($user) use (&$page, &$action) {
            return (string) \Mercurio\Utils\Router::getLink($page, $user['handle'], $action);
        });
    }

    /**
     * Get channel elements by user
     * @param callable $callback Callback function to manipulate channel
     * function (array $channels) :
     * @return array
     */
    public function getChannels(callable $callback = NULL) {
        return $this->get(false, function($user) use (&$callback) {
            $channels = $this->SQL->select(DB_CHANNELS, '*', [
                'author' => $user['id']
            ]);
            if ($callback !== NULL) return $callback($channels);
            return $channels;
        });
    }

    /**
     * Get media elements by user
     * @param callable $callback Callback function to manipulate media
     * function (array $medias) :
     * @return array
     */
    public function getMedias(callable $callback = NULL) {
        return $this->get(false, function($user) use (&$media) {
            $medias = $this->SQL->select(DB_MEDIA, '*', [
                'author' => $user['id']
            ]);
            if ($callback !== NULL) return $callback($medias);
            return $medias;
        });
    }

    /**
     * Perform a login
     * @param string $credential User identifier: handle or email (also ID will work)
     * @param string $password User password, plain text
     * @param callable $callback Action to perform after successfull login
     * function () :
     * @param callable $fallback Action to perform after login failure
     * function () :
     * @throws object Exception\User\WrongLoginCredential | LoginBlocked or Exception\User\EmptyField
     */
    public function login(string $credential, string $password, callable $callback = NULL, callable $fallback = NULL) {
        // ensure environment is ready for a login
        if ($this->getSession()) $this->logout();
        \Mercurio\Utils\System::emptyField(['credential', 'password'], [
            'credential' => $credential,
            'password' => $password
        ]);
        $credential = ltrim($credential, '@');

        // session enforced bruteforce protection for wrong credential
        if (!$this->get($credential)) {
            if ($fallback !== NULL) $fallback();

            $attempts = \Mercurio\Utils\Session::get('loginAttempts', 0);
            sleep($attempts++);
            \Mercurio\Utils\Session::set($attempts, 'loginAttempts');
            throw new \Mercurio\Exception\User\WrongLoginCredential;

        // database enforced bruteforce protection for wrong password
        } else {
            // Get lock value
            $lock = $this->getMeta('login_blocked')['value'];
            if ($lock === NULL) {
                $attempts = $this->getMeta('login_attempt')['value'];
                $hash = $this->SQL->get(DB_USERS, 
                    ['password'], 
                    ['id' => $this->info['id']]
                )['password'];

                // Increase lock number and start script delay
                if (!password_verify($password, $hash)) {
                    if ($fallback !== NULL) $fallback();
                    
                    sleep($attempts++);
                    $this->setMeta(['login_attempt' => $attempts]);
                    throw new \Mercurio\Exception\User\WrongLoginCredential;

                    if ($attempts > 9) {
                        $this->setMeta(['login_blocked' => time() + 300]);
                        throw new \Mercurio\Exception\User\LoginBlocked;
                    }

                // Attach session, save info and redirect user
                } else {
                    \Mercurio\Utils\Session::regenerate(0);
                    $this->setSession($credential, true);

                    $this->setMeta([
                        'login_lastin' => time(),
                        'login_attempt' => 0,
                        'login_blocked' => NULL,
                    ]);

                    if ($callback !== NULL) $callback();
                }
            // Login is locked
            } elseif (time() - $lock > 300) {
                $this->setMeta(['login_blocked' => 0]);
                $this->login($credential, $password, $callback, $fallback);
            }
        }

    }

    /**
     * Perform a logout
     * @param callable $callback Action to perform after logout
     * function () :
     */
    public function logout(callable $callback = NULL) {
        $this->setMeta(['login_lastout' => time()]);
        \Mercurio\Utils\Session::unset('User');
        \Mercurio\Utils\Session::regenerate();

        if ($callback == NULL) header('Location: '.getenv('APP_URL'));
        $callback();
    }

    /**
     * Check validity and availability of an user handle and transform it into a valid one
     * @param string $handle
     * @return string Valid handle
     */
    public function validateHandle(string $handle) : string {
        $this->get($handle, function() {
            throw new \Mercurio\Exception\User\ExistingHandle;
        });
        if (ctype_digit($handle)) throw new \Mercurio\Exception\User\InvalidHandle;

        $handle = trim($handle);
        $handle = ltrim($handle, '@');
        $handle = strtolower($handle);
        $handle = preg_replace('/[^a-z0-9_]/', '', $handle);
        return $handle;
    }

}
