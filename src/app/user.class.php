<?php

namespace Mercurio\App;

/**
 * User class and model
 * @package Mercurio
 * @subpackage App classes
 */
class User extends \Mercurio\App\Model {

    public $data = [
        'id' => NULL,
        'handle' => NULL,
        'email' => NULL,
        'nickname' => NULL,
        'password' => NULL,
        'img' => NULL,
        'stamp' => NULL
    ];

    public $db_table = DB_USERS;

    /**
     * Unhashed user password
     * Meant to temporarily store user password during logins
     */
    private $password;

    /**
     * Load user from session instead of from database
     * @return array|false 
     */
    public function getFromSession() {
        $session = \Mercurio\Utils\Session::get('User', false);

        if ($session) {
            $this->data = $session;
            $this->id = $session['id'];
        }
        return $session;
    }

    /**
     * Prepare user to be selected by ID
     * @param int $id User numeric ID
     */
    public function getById(int $id) {
        $this->get_by = ['id' => $id];
    }

    /**
     * Prepare user to be selected by handle
     * @param string $handle User alphanumeric handle
     * @param bool $arroba Handle is in '@' format
     */
    public function getByHandle(string $handle, bool $arroba = false) {
        if ($arroba) $handle = ltrim($handle, '@');
        
        $this->get_by = ['handle' => $handle];
    }

    /**
     * Prepare user to be selected by email
     * @param string $email User email address
     */
    public function getByEmail(string $email) {
        $this->get_by = ['email' => $email];
    }

    /**
     * Return user numeric ID
     * @param bool $as_string Returns the ID as an string
     * @return int|string
     */
    public function getId(bool $as_string = false) {
        if ($as_string) return (string) $this->data['id']; 
        return (int) $this->data['id'];
    }

    /**
     * Return user alphanumeric handle
     * @param bool $arroba Returns the handle with the symbol '@' prepended
     * @return string
     */
    public function getHandle(bool $arroba = false) {
        if ($arroba) return '@' . $this->data['handle'];
        return $this->data['handle'];
    }

    /**
     * Update user alphanumeric handle
     * @param string $handle New user handle
     * This value will be regex compared to strip whitespace, '@' and everything non 'a-z', '0-9' and '_'
     * @param string $replacement Replacement value for regex comparison
     * @throws \Mercurio\Exception\User\HandleInvalid if processed handle turns out blank
     */
    public function setHandle(string $handle, string $replacement = '') {
        $handle = strtolower($handle);
        $handle = preg_replace('/[^a-z0-9_]/', $replacement, $handle);
        if ($handle === '') throw new \Mercurio\Exception\User\HandleInvalid;
        
        $this->data['handle'] = $handle;
    }

    /**
     * Return user email address
     * @return string
     */
    public function getEmail() {
        return $this->data['email'];
    }

    /**
     * Update user email address
     * @param string $email New user email address
     */
    public function setEmail(string $email) {
        $this->data['email'] = $email;
    }

    /**
     * Return user nickname
     * @return string
     */
    public function getNickname() {
        return $this->data['nickname'];
    }

    /**
     * Update user nickname
     * @param string $nickname New user nickname
     */
    public function setNickname(string $nickname) {
        $this->data['nickname'] = $nickname;
    }

    /**
     * Return user image
     * @return string|null
     */
    public function getImage() {
        if (!empty($this->data['img'])) {
            return $this->data['img'];
        }

        return NULL;
    }

    /**
     * Update user image
     * @param string $image Image filename inside of Mercurio User's statics \
     * Use constant APP_USERSTATIC to access this folder
     * @see \Mercurio\Utils\Image;
     */
    public function setImage(string $image) {
        $this->data['img'] = $image;
    }

    /**
     * Return user data in session, if user in instance is the one in session
     * @return array|false
     */
    public function getSession() {
        if ($this->id !== \Mercurio\Utils\Session::get('User', ['id' => false])['id']) {
            return false;
        }

        return \Mercurio\Utils\Session::get('User');
    }

    /**
     * Update user data in session, overriding the data \
     * Will update the session regardless of the instance
     * @param bool $regenerate Will regenerate the session id
     */
    public function setSession(bool $regenerate = true) {
        \Mercurio\Utils\Session::set('User', $this->data, $regenerate);
    }

    /**
     * Update user password
     * @param string $password User new password
     * Plain text, Mercurio will do the encryption
     */
    public function setPassword(string $password) {
        $this->data['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Prepare user login
     * @param string $credential User handle or email
     * @param string $password User password, plain text
     */
    public function getByLogin(string $credential, string $password) {
        $this->get_by = ['OR' => [
            'handle' => $credential,
            'email' => $credential
        ]];

        $this->password = $password;
    }

    /**
     * Validate login
     * @param $database Result of Database class after passing self instance for selection
     * @param bool $delay On true will apply a delay on every failed attempt to prevent bruteforce \
     * Session enforced protection
     * @param int $block If more than 3 it will apply a login block of 5 minutes after specified failed  attempts \
     * Cookies enforced protection
     * @throws \Mercurio\Exception\User\LoginFailed
     * @return bool
     */
    public function setLogin($database, bool $delay = false, int $block = 0) {
        $password = $this->password;
        $this->password = NULL;

        if (!$database || !password_verify($password, $database->data['password']) || !$this->loginBlock($block)) {
            if ($delay) $this->loginDelay();
            throw new \Mercurio\Exception\User\LoginFailed;
            return false;
        }

        $this->data = $database->data;
        \Mercurio\Utils\Session::set('User', $this->data, true);
        return true;
    }

    /**
     * Delays script execution in a progressive way
     */
    protected function loginDelay() {
        $delay = \Mercurio\Utils\Session::get('login', 1);

        sleep($delay);
        \Mercurio\Utils\Session::set('login', $delay+1, false);
    }

    /**
     * Avoids the performance of new logins using cookies
     */
    protected function loginBlock($block) {
        // Start cookie attempts counter
        $attempt = (array_key_exists('loginTokenVal', $_COOKIE) ? $_COOKIE['loginTokenVal'] : 0);
        setcookie('loginTokenVal', $attempt++, time() + 3000, '/', APP_URL);
        
        // Avoid login during cookie block
        if (array_key_exists('loginToken', $_COOKIE)) return false;

        if ($block > 3 && $attempt > $block + 1) {
            setcookie('loginToken', 'Verification cookie. Do not delete', time() + 300, '/', APP_URL);
        }

        return true;
    }

}
