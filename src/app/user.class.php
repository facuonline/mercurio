<?php

namespace Mercurio\App;

/**
 * User entity class
 * @package Mercurio
 * @subpackage Included classes
 */
class User extends Model {

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
     * Users table in database
     */
    protected $DBTABLE = DB_USERS;
    
    public function __construct(\Mercurio\App\Database $db) {
        $this->info = ['id', 'handle', 'nickname', 'img', 'stamp'];
        $this->meta = [];
        $this->DB = $db->getSQL();
    }

    /**
     * Select an user from database by their ID and load into instance
     * @param int $id
     * @param callable $closure Closure function to directly access user basic info
     * @param callable $fallback Closure function to execute in case of no user found
     * @return bool
     * @throws Mercurio\Exception\InvalidDataType 
     */
    public function getById(int $id, callable $closure = NULL, callable $fallback = NUll) {
        if (!\Mercurio\Utils\Filter::isInt($id)) throw new Mercurio\Exception\InvalidDataType;

        return $this->get(['id' => $id], $closure, $fallback, $this->info);
    }

    /**
     * Select an user from database by their handle and load into instance
     * @param string $handle
     * @param callable $closure Closure function to directly access user basic info
     * @param callable $fallback Closure function to execute in case of no user found
     * @return bool
     * @throws Mercurio\Exception\InvalidDataType 
     */
    public function getByHandle(string $handle, callable $closure = Null, callable $fallback = NULL) {
        if (!\Mercurio\Utils\Filter::isString($id)) throw new Mercurio\Exception\InvalidDataType;

        return $this->get(['handle' => $handle], $closure, $fallback, $this->info);
    }

    /**
     * Select an user from database by their email and load into instance
     * @param string $email
     * @param callable $closure Closure function to directly access user basic info
     * @param callable $fallback Closure function to execute in case of no user found
     * @return bool
     * @throws Mercurio\Exception\InvalidDataType 
     */
    public function getByEmail(string $email, callable $closure = Null, callable $fallback = NULL) {
        if (!\Mercurio\Utils\Filter::isEmail($id)) throw new Mercurio\Exception\InvalidDataType;

        return $this->get(['email' => $email], $closure, $fallback, $this->info);
    }

    /**
     * Load user from session into instance \
     * This will load the info from the session and not from the database
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
     * @param bool $regenerate Regenerate or not session id
     */
    public function setSession(bool $regenerate = false) {
        $this->getById($this->info['id'], function($user) use (&$regenerate) {
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
        if ($string) return (string) $this->info['id'];
        return (int) $this->info['id'];
    }

    /**
     * Get user handle
     * @param bool $arroba Return handle with or without @
     * @return string User handle
     */
    public function getHandle(bool $arroba = false) {
        if ($arroba) return '@'.$this->info['handle'];
        return $this->info['handle'];
    }

    /**
     * Update user handle
     * @param string $handle
     * @return void
     * @throws Mercurio\Exception\InvalidDataType
     * @throws Mercurio\Exception\User\ExistingHandle
     * @throws Mercurio\Exception\User\InvalidHandle
     */
    public function setHandle(string $handle) {
        if (!\Mercurio\Utils\Filter::isString($id)) throw new Mercurio\Exception\InvalidDataType;
        $this->validateHandle($handle);

        $this->set(['handle' => $handle]);
    }

    /**
     * Get user public name
     * @return string User nickname
     */
    public function getNickname() {
        return $this->info['nickname'];
    }

    /**
     * Update user public name
     * @param string $nickname
     * @return void
     * @throws Mercurio\Exception\InvalidDataType
     */
    public function setNickname(string $nickname) {
        if (!\Mercurio\Utils\Filter::isString($id)) throw new Mercurio\Exception\InvalidDataType;

        $this->set(['nickname' => $nickname]);
    }

    /**
     * Get user email \
     * Makes a database call
     * @return string User email
     */
    public function getEmail() {
        return $this->get(['id' => $this->info['id']], function($user) {
            return (string) $user['email'];
        }, NULL, ['email']);
    }

    /**
     * Update user email
     * @param string $email
     * @return void
     * @throws Mercurio\Exception\InvalidDataType
     * @throws Mercurio\Exception\User\ExistingEmail
     */
    public function setEmail(string $email) {
        if (!\Mercurio\Utils\Filter::isEmail($id)) throw new Mercurio\Exception\InvalidDataType;
        if ($this->getByEmail($email)) throw new Mercurio\Exception\User\ExistingEmail;

        $this->set(['email' => $email]);
    }

    /**
     * Get user img property as a full absolute path
     * @return string Link to user image
     */
    public function getImg() {
        if (!empty($this->info['img'])
        && file_exists(APP_USERSTATIC.$this->info['img'])) return APP_USERSTATIC_ABS.$this->info['img'];
        return false;
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
            .$this->info['img']
        );
        // Upload new
        $image = new \Mercurio\Utils\Image;
        $image->new($file, APP_USERSTATIC, $width, $ratio);
        $this->set(['img' => $image->hash]);
    }

    /**
     * Deletes user img from database and related file from statics
     */
    public function unsetImg() {
        if ($this->getImg()) unlink(
            APP_USERSTATIC
            .$this->info['img']
        );
        $this->set(['img' => '']);
    }

    /**
     * Get absolute link to user profile
     * @todo Refactor to communicate with new Router util
     */
    public function getLink() {

    }

    /**
     * Get channel elements by user
     * @param callable $callback Callback function to manipulate channel
     * function (array $channels) :
     * @return array
     */
    public function getChannels(callable $callback = NULL) {
        $channels = $this->DB->select(DB_CHANNELS, '*', [
            'author' => $this->info['id']
        ]);

        if ($callback !== NULL) return $callback($channels);

        return $channels;
    }

    /**
     * Get media elements by user
     * @param callable $callback Callback function to manipulate media
     * function (array $medias) :
     * @return array
     */
    public function getMedias(callable $callback = NULL) {
        $medias = $this->SQL->select(DB_MEDIA, '*', [
            'author' => $this->info['id']
        ]);

        if ($callback !== NULL) return $callback($medias);

        return $medias;
    }

    /**
     * Perform a login
     * @param string $credential User identifier: handle or email
     * @param string $password User password, plain text
     * @param callable $callback Action to perform after successful login
     * function () :
     * @param callable $fallback Action to perform after login failure and before automatic enforced delay
     * function () :
     * @throws Mercurio\Exception\User\WrongLoginCredential
     * @throws Mercurio\Exception\User\LoginBlocked
     * @throws Mercurio\Exception\User\EmptyField
     */
    public function login(string $credential, string $password, callable $callback = NULL, callable $fallback = NULL) {
        // ensure environment is ready for a login
        if ($this->getSession()) $this->logout();
        \Mercurio\Utils\System::emptyField(['credential', 'password'], [
            'credential' => $credential,
            'password' => $password
        ]);
        $credential = trim($credential);
        $credential = ltrim($credential, '@');

        // session enforced bruteforce protection for wrong credential
        if (!$this->get([
            'OR' => [
                'handle' => $credential, 
                'email' => $credential
            ]
        ], NULL, NULL, ['id', 'password'])) {
            if ($fallback !== NULL) $fallback();

            $attempts = \Mercurio\Utils\Session::get('loginAttempts', 0);
            sleep($attempts++);
            \Mercurio\Utils\Session::set($attempts, 'loginAttempts');
            throw new \Mercurio\Exception\User\WrongLoginCredential;

        // database enforced bruteforce protection for wrong password
        } else {
            // Get block value
            $block = $this->getMeta('login_blocked')['value'];
            if ($block === 0) {
                $attempts = $this->getMeta('login_attempt')['value'];

                // Increase block number and start script delay
                if (!password_verify($password, $this->info['password'])) {
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
                    $this->getById($this->info['id']);
                    $this->setSession(true);

                    $this->setMeta([
                        'login_lastin' => time(),
                        'login_attempt' => 0,
                        'login_blocked' => 0,
                    ]);

                    if ($callback !== NULL) $callback();
                }
            // Login is blocked
            } elseif (time() - $block > 300) {
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

        if ($callback == NULL) {
            header('Location: '.getenv('APP_URL'));
            return;
        }
        $callback();
    }

    /**
     * Check validity and availability of an user handle and transform it into a valid one
     * @param string $handle
     * @return string Valid handle
     */
    public function validateHandle(string $handle) : string {
        $this->get(['handle' => $handle], function() {
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
