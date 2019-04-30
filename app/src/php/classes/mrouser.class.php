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
        if (!$user) $user = mroNoUser();
        if (ctype_digit($user)) {
            $this->GID = $user;
        } else {
            $this->handle = $user;
        }
        $this->load();
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
        // make query
        if ($query) {
            $result = $this->pdo(
                $query->sql(),
                $query->params()
            )->fetchAll()[0];
            $this->info = $result;
            $this->GID = $result['GID'];
            $this->handle = $result['handle'];
            $this->loadMeta();
        }
    }

    private function loadMeta() {
        $query = false;
        // build query
        if ($this->GID) {
            $query = $this->sql()
                ->select()
                ->from('mro_usermeta')
                ->where(field('user')->eq($this->GID))
                ->compile();
        }
        // make query
        if ($query) {
            $result = $this->pdo(
                $query->sql(),
                $query->params()
            )->fetchAll()[0];
            $this->meta = $result;
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
            // build query
            $query = $this->sql()
                ->insert('mro_users', $properties)
                ->compile();
            // make query
            $this->pdo($query->sql());
            // add meta row
            $meta = $this->sql()
                ->insert('mro_usermeta', [
                        'user' => $properties['GID']
                    ])
                ->compile();
            $this->pdo($meta->sql());
            // load this user
            $this->GID = $properties['GID'];
            $this->load();
        // update loaded user
        } elseif (mroValidateSet($properties) && $this->GID) {
            $query = $this->sql()
                ->update('mro_users', $properties)
                ->where(field('GID')->eq($this->GID))
                ->compile();
            $this->pdo(
                $query->sql(),
                $query->params()
            );
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
            $this->pdo(
                $query->sql(),
                $query->params()
            );
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
                $this->pdo(
                    $posts->sql(),
                    $posts->params()
                );
                // delete comments
                $comments = $this->sql()
                    ->delete('mro_posts')
                    ->where(field('author')->eq($this->GID))
                    ->compile();
                $this->pdo(
                    $comments->sql(),
                    $comments->params()
                );
                // delete non collaborative stories
                $storiesImg = $this->sql()
                    ->select('img')
                    ->from('mro_stories')
                    ->where(field('author')->eq($this->GID))
                    ->andWhere(field('open')->eq('0'))
                    ->compile();
                $imgs = $this->pdo(
                    $storiesImg->sql(),
                    $storiesImg->params()
                )->fetchAll();
                foreach($imgs as $key => $value) {
                    mroRemoveImg($value);
                }
                $stories = $this->sql()
                    ->delete('mro_stories')
                    ->where(field('author')->eq($this->GID))
                    ->andWhere(field('open')->eq('0'))
                    ->compile();
                $this->pdo(
                    $stories->sql(),
                    $stories->params()
                );
                $this->deleteUser();
            } else {
                // mro_user
                $user = $this->sql()
                    ->delete('mro_users')
                    ->where(field('GID')->eq($this->GID))
                    ->compile();
                $this->pdo(
                    $user->sql(),
                    $user->params()
                );
                // user img
                mroRemoveImg($this->getImg());
                // mro_usermeta
                $meta = $this->sql()
                    ->delete('mro_usermeta')
                    ->where(field('user')->eq($this->GID))
                    ->compile();
                $this->pdo(
                    $meta->sql(),
                    $meta->params()
                );
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
            return $this->pdo(
                $query->sql(),
                $query->params()
            )->fetchAll();
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
            $this->pdo(
                $query->sql()
            );
        // update
        } elseif (mroValidateSet($properties) && ctype_digit($badge)) {
            $query = $this->sql()
                ->update('mro_userbadges', $properties)
                ->where(field('GID')->eq($badge))
                ->compile();
            $this->pdo(
                $query->sql(),
                $query->params()
            );
        }
    }

    /**
     * Get link to user profile page
     * @return string URL
     * @see MroCVURL class
     * This took way longer than what it looks like, please take a second to appreciate this piece of code
     */
    public function getLink() {
        if ($this->handle) {
            $CVURL = new utils_MroCVURL;
            return $CVURL->buildLink('users', $this->handle);
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
            return $this->pdo(
                $query->sql(),
                $query->params()
            )->fetch()['email'];
        } else {
            throw new Exception("METHOD FAILURE: getEmail can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Get path to user img
     * @todo MroVista class
     * @param bool $full True for full scale img, false for minimg
     * @return string URL
     */
    public function getImg(bool $full = false) {
        $Vista = new utils_MroVista;
        if ($this->info['img'] !== $Vista->default('userImg')) {
            if ($full){
                $img = 'upload_max_'.$this->info['img'];
            } else {
                $img = 'upload_min_'.$this->info['img'];
            }
        } else {
            $img = $Vista->default('userImg');
        }
        return getenv('APP_URL').'app/static/'.$img;
    }

    /**
     * Loads and sets an user to perform a login with setLogin
     * @param string $user User handle or email
     * @return bool
     */
    public function getLogin(string $user) {
        // start session to save login attempts
        $session_factory = new \Aura\Session\SessionFactory;
        $session = $session_factory->newInstance($_COOKIE);
        $segment = $session->getSegment('MroUser');
        $attempts = $segment->get('loginAttempts', 0);
        // build query 
        $query = $this->sql()
            ->select('GID', 'handle', 'password')
            ->from('mro_users')
            ->where(field('handle')->eq($user))
            ->orWhere(field('email')->eq($user))
            ->compile();
        // perform it
        $result = $this->pdo(
            $query->sql(),
            $query->params()
        )->fetchAll()[0];
        if ($result) {
            $segment->set('loginAttempts', 0);
            $this->GID = $result['GID'];
            $this->password = $result['password'];
            return true;
        // protect against bruteforce attacks
        } else {
            $segment->set('loginAttempts', $attempts++);
            if ($attempts > 3) {
                sleep($attempts);
            } elseif ($attempts > 9) {
                sleep($attemps*3);
                $httpRequest = new Nette\Http\UrlScript; $remoteAddress = $httpRequest->getRemoteAddress();
                error_log("SECURITY: Too many (+10) failed login attempts with wrong credentials from ip address: $remoteAddress");
            }
            return false;
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
                    // attach session to user
                    $session_factory = new \Aura\Session\SessionFactory;
                    $session = $session_factory->newInstance($_COOKIE);
                    $segment = $session->getSegment('MroUser');
                    $segment->set('user', $this->info);
                    $segment->set('meta', $this->meta);
                // progressive delay
                } else {
                    $this->setMeta(['login' => $attempts++]);
                    sleep($attempts);
                }
            // lockdown for 5 minutes
            } elseif ($attempts > 9) {
                $this->setMeta(['login' => time()+300]);
                $httpRequest = new Nette\Http\UrlScript; $remoteAddress = $httpRequest->getRemoteAddress();
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