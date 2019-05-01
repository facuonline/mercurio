<?php
/**
 * MroUser class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var array $info User basic info
 * @var array $info User metainfo
 * @var int $GID User generated incremental discriminator
 * @var string $handle User hexadecimal locator
 * @var string $password
 */

use function Latitude\QueryBuilder\field;
class MroUser extends MroDB {
    public $info, $meta, $GID, $handle;
    private $password;

    public function __construct() {
        $this->conn();
        $this->GID = false;
        $this->handle = false;
    }

    /**
     * Select and initialize user into class
     * @param mixed $user
     */
    public function getUser($user = false) {
        // search user in http request via $_GET
        $URL = new MroUtils\URLHandler;
        if ($URL->getUrl()['referrer'] === 'users') {
            $this->handle = $URL->getUrl()['target'];
            $this->load();
        // search user attached to $_SESSION
        } elseif (mroSession()) {
            $session =  mroSession();
            $this->info = $session->info;
            $this->meta = $session->meta;
            $this->GID = $session->GID;
            $this->handle = $session->handle;
        }
    }

    private function load() {
        $query = false;
        // build query
        if ($this->GID) {
            $query = $this->sql()
                ->select('GID', 'handle', 'nick', 'img', 'stamp')
                ->from('mro_users')
                ->where(field('GID')->eq($this->GID))
                ->compile();
        } elseif($this->handle) {
            $query = $this->sql()
                ->select('GID', 'handle', 'nick', 'img', 'stamp')
                ->from('mro_users')
                ->where(field('handle')->eq($this->handle))
                ->compile();
        }
        if ($query) {
            // make query
            $result = $this->pdo($query)->fetch();
            if ($result) {
                $this->info = $result;
                $this->GID = $result['GID'];
                $this->handle = $result['handle'];
                $this->loadMeta();
            }
        }
    }

    private function loadMeta() {
        if ($this->GID) {
            // build query
            $query = $this->sql()
                ->select()
                ->from('mro_usermeta')
                ->where(field('user')->eq($this->GID))
                ->compile();
            // make query
            $result = $this->pdo($query)->fetch();
            if ($result) {
                $this->meta = $result;
            }
        }
    }

    /**
     * Update user or set new one
     * @param array $properties Associative array of user properties, 
     * mro_user table only, to setup mro_usermeta use setMeta
     * @param bool $new True to insert new record with specified properties, false to update loaded user
     */
    public function setUser(array $properties = [], bool $new = false) {
        // set up new user
        if ($new) {
            $properties = mroStampSet($properties);
            // password
            if (array_key_exists('password', $properties)) {
                $properties['password'] = password_hash($properties['password'], PASSWORD_DEFAULT);
            }
            // image
            if (array_key_exists('img', $properties)) {
                throw new Exception("setUser method exception. User img property can only be set with setImg method.");
            }
            // build query
            $query = $this->sql()
                ->insert('mro_users', $properties)
                ->compile();
            // make query
            $this->pdo($query);
            // add meta row
            $meta = $this->sql()
                ->insert('mro_usermeta', [
                        'user' => $properties['GID']
                    ])
                ->compile();
            $this->pdo($meta);
            // load this user
            $this->GID = $properties['GID'];
            $this->load();
        // update loaded user
        } elseif (mroValidateSet($properties) && $this->GID) {
            $query = $this->sql()
                ->update('mro_users', $properties)
                ->where(field('GID')->eq($this->GID))
                ->compile();
            $this->pdo($query);
        } else {
            throw new Exception("METHOD FAILURE: setUser was called but object of class MroUser has not been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Updates user meta info
     * @param array $meta Associative array of user meta info
     * @see setUser to learn how to insert a new mro_usermeta row
     */
    public function setMeta(array $meta = []) {
        if ($this->GID) {
            $query = $this->sql()
                ->update('mro_usermeta', $meta)
                ->where(field('user')->eq($this->GID))
                ->compile();
            $this->pdo($query);
        } else {
            throw new Exception("METHOD FAILURE: setMeta can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Deletes an user from database and app
     * @param bool $likeItNeverExisted True to delete user generated content
     */
    public function deleteUser(bool $likeItNeverExisted = false) {
        if ($this->GID) {
            if ($likeItNeverExisted) {
                // delete posts
                $posts = $this->sql()
                    ->delete('mro_posts')
                    ->where(field('author')->eq($this->GID))
                    ->compile();
                $this->pdo($posts);
                // delete comments
                $comments = $this->sql()
                    ->delete('mro_posts')
                    ->where(field('author')->eq($this->GID))
                    ->compile();
                $this->pdo($comments);
                // delete non collaborative stories
                $storiesImg = $this->sql()
                    ->select('img')
                    ->from('mro_stories')
                    ->where(field('author')->eq($this->GID))
                    ->andWhere(field('open')->eq('0'))
                    ->compile();
                $imgs = $this->pdo($storiesImg)
                    ->fetchAll();
                foreach($imgs as $key => $value) {
                    mroRemoveImg($value);
                }
                $stories = $this->sql()
                    ->delete('mro_stories')
                    ->where(field('author')->eq($this->GID))
                    ->andWhere(field('open')->eq('0'))
                    ->compile();
                $this->pdo($stories);
                $this->deleteUser();
            } else {
                // mro_usermeta
                $meta = $this->sql()
                    ->delete('mro_usermeta')
                    ->where(field('user')->eq($this->GID))
                    ->compile();
                $this->pdo($meta);
                // mro_user
                $user = $this->sql()
                    ->delete('mro_users')
                    ->where(field('GID')->eq($this->GID))
                    ->compile();
                $this->pdo($user);
                // user img
                mroRemoveImg($this->getImg());
            }
        } else {
            throw new Exception("METHOD FAILURE: deleteUser can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Select user badges
     * @return array
     */
    public function getBadges() {
        if ($this->GID) {
            $query = $this->sql()
                ->select()
                ->from('mro_userbadges')
                ->where(field('user')->eq($this->GID))
                ->compile();
            return $this->pdo($query)
                ->fetchAll();
        } else {
            throw new Exception("METHOD FAILURE: getBadges can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Update or insert a new user badge
     * @param array $properties Associative array for badge properties
     * @param bool|int $badge False to insert new badge, Badge GID to update it
     */
    public function setBadge(array $properties = [], $badge = false) {
        // new badge
        if (!$badge && $this->GID) {
            $properties = mroStampSet(); 
            $properties['user'] = $this->GID;
            $query = $this->sql()
                ->insert('mro_userbadges', $properties)
                ->compile();
            $this->pdo($query);
        // update
        } elseif (mroValidateSet($properties) && ctype_digit($badge)) {
            $query = $this->sql()
                ->update('mro_userbadges', $properties)
                ->where(field('GID')->eq($badge))
                ->compile();
            $this->pdo($query);
        }
    }

    /**
     * Get link to user profile page
     * @return string URL
     * @see URLHandler class
     * This took way longer than what it looks like, 
     * please take a second to appreciate this piece of code
     */
    public function getLink() {
        if ($this->handle) {
            $CVURL = new MroUtils\URLHandler;
            return $CVURL->linkMaker('users', $this->handle);
        } else {
            throw new Exception("METHOD FAILURE: getLink can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * This method will cost you a db query, do not waste it
     */
    public function getEmail() {
        if ($this->GID) {
            $query = $this->sql()
                ->select('email')
                ->from('mro_users')
                ->where(field('GID')->eq($this->GID))
                ->compile();
            return $this->pdo($query)
                ->fetch()['email'];
        } else {
            throw new Exception("METHOD FAILURE: getEmail can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Get path to user img
     * @return string Image URL
     * @see Vista class
     * This also took way longer than expected and what it looks like, 
     * please take a second to appreciate this piece of code
     */
    public function getImg() {
        if (!$this->info['img']) {
            return MroVista::getVistaUrl()
                .'/'.MroVista::default('img', 'user');
        } else {
            return getenv('APP_URL')
                .'app/static/'.$this->info['img'];
        }
    }

    /**
     * Set and upload user image
     * @param string $input Name of file type input field where image is loaded
     * @param int $width Width in pixels of image
     * @param int $height Height in pixels of image
     * @return string Image URL
     * @see https://github.com/samayo/bulletproof
     * @todo
     */
    public function setImg(string $input, int $width = 400, int $height = 400) {

    }

    /**
     * Loads and sets an user to perform a login with setLogin
     * @param string $user User handle or email
     * @return bool
     */
    public function getLogin(string $user) {
        // start error checker
        $error = false;
        // start session to store login attempts
        $session = AuraSession();
        $segment = $session->getSegment('MroUser');
        $attempts = $segment->get('loginAttempts', 0);
        // check if credential is a valid type
        if (!is_string($user)
        && !filter_var($user, FILTER_VALIDATE_EMAIL)) {
            $error = true;
        }
        if (!$error) {
            // build query
            $query = $this->sql()
                ->select('GID', 'password')
                ->from('mro_users')
                ->where(field('handle')->eq($user))
                ->orWhere(field('email')->eq($user))
                ->compile();
            $result = $this->pdo($query)->fetch();
            // succesful login
            if ($result) {
                $segment->set('loginAttempts', 0);
                $this->GID = $result['GID'];
                $this->password = $result['password'];
            // progressive delay for wrong credentials
            } else {
                $segment->set('loginAttempts', $attempts++);
                if ($attempts > 3) {
                    sleep($attempts);
                } elseif ($attempts > 9) {
                    sleep($attempts*3);
                }
            }
        }
    }

    /**
     * Perform a password check and a login
     * @param string $password User password
     * @return bool
     */
    public function setLogin(string $password) {
        if ($this->GID) {
            if (!$this->meta) {
                $this->loadMeta();
            }
            // check login attempts on db
            $attempts = $this->meta['login'];
            if ($attempts < time()) {
                if (password_verify($password, $this->password)) {
                    $this->load();
                    // attach user object to session
                    $segment = AuraSession()->getSegment('MroUser');
                    $segment->set('User', $this);
                // progressive delay
                } else {
                    $this->setMeta(['login' => $attempts++]);
                    sleep($attempts);
                }
            // lockdown for 5 minutes
            } elseif ($attempts > 9) {
                $this->setMeta(['login' => time()+300]);
                $remoteAddress = NetteHttpUrl()->getRemoteAddress();
                error_log("SECURITY: Too many (+10) failed login attempts with wrong password for user $this->handle $this->GID from ip address: $remoteAddress");
            }
        } else {
            throw new Exception("METHOD FAILURE: setLogin can only be called if object of class MroUser has been loaded with an existing user.", 1);
        }
    }

    public function getStories() {

    }

    public function getPosts() {

    }

    public function getComments() {

    }

    public function getStars() {
        
    }
}