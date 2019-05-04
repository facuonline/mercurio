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
 * @var array $session MroUser segment of Aura Session
 */

use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\order;
class MroUser extends MroDB {
    public $info, $meta, $GID, $handle;
    private $password;
    protected $session;

    /**
     * @param mixed $user User locator, either GID or handle
     */
    public function __construct($user = false) {
        if ($user) {
            $this->getUser($user);
        } else {
            $this->GID = false;
            $this->handle = false;
            $this->password = false;
        }
        $session = AuraSession();
        $segment = $session->getSegment('MroUser');
        $this->session = $segment;
    }

    /**
     * Select and initialize user into instance
     * @param mixed $user User locator, either GID or handle
     * @return bool
     */
    public function getUser($user = false) {
        if (!$user) {
            // search user in http request via $_GET
            $URL = new MroUtils\URLHandler;
            if ($URL->getUrl()['referrer'] === 'users'
            && $URL->getUrl()['target']) {
                $this->handle = ltrim($URL->getUrl()['target'], '@');
                $this->load();
                return true;
            // search user attached to $_SESSION
            } elseif ($this->session->get('User')) {
                $this->info = $this->session->get('User')['info'];
                $this->meta = $this->session->get('User')['meta'];
                $this->GID = $this->session->get('User')['GID'];
                $this->handle = $this->session->get('User')['handle'];
                return true;
            } else {
                return false;
            }
        } elseif (ctype_digit($user)) {
            $this->GID = $user;
            return true;
        } elseif (is_string($user)) {
            $this->handle = ltrim($user, '@');
            return true;
        }
    }

    private function load() {
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
        $result = $this->pdo($query)->fetch();
        if ($result) {
            $this->info = $result;
            $this->GID = $result['GID'];
            $this->handle = $result['handle'];
            $this->loadMeta();
        }
    }

    private function loadMeta() {
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

    /**
     * Update user or set new one
     * @param array $properties Associative array of user properties, 
     * mro_user table only, to setup mro_usermeta use setMeta
     * @param bool $new True to insert new record with specified properties, false to update loaded user
     * @throws object Runtime class Exception if condition not met
     */
    public function setUser(array $properties = [], bool $new = false) {
        // check image
        if (array_key_exists('img', $properties)) {
            throw new MroException\Runtime("METHOD FAILURE: User img property can only be set with setImg method.", 301);
        } 
        // hash password
        if (array_key_exists('password', $properties)) {
            $properties['password'] = password_hash($properties['password'], PASSWORD_DEFAULT);
        }
        // set up new user
        if ($new) {
            // check password
            if (!array_key_exists('password', $properties)) {
                throw new MroException\Runtime("setUser method exception. New users must include a password key in properties array.", 301);
            }
            $properties = mroStampSet($properties);
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
            throw new MroException\Runtime("METHOD FAILURE: setUser was called but object of class MroUser has not been loaded with an existing user. Use getUser method first.", 301);
        }
    }

    /**
     * Updates user meta info
     * @param array $meta Associative array of user meta info
     * @throws object Runtime class Exception if condition not met
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
            throw new MroException\Runtime("METHOD FAILURE: setMeta can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Deletes an user from database and app
     * @param bool $likeItNeverExisted True to delete user generated content
     * @throws object Runtime class Exception if condition not met
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
                // remove images
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
            throw new MroException\Runtime("METHOD FAILURE: deleteUser can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Get user GID
     * @return int User GID
     * @throws object Runtime class Exception if condition not met
     */
    public function getGID() {
        if ($this->GID) {
            return (int) $this->GID;
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getGID can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Get user handle
     * @return string User handle
     * @throws object Runtime class Exception if condition not met
     */
    public function getHandle() {
        if ($this->GID) {
            return (string) '@'.$this->handle;
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getHandle can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Select user badges
     * @return array
     * @throws object Runtime class Exception if condition not met
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
            throw new MroException\Runtime("METHOD FAILURE: getBadges can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Update or insert a new user badge
     * @param array $properties Associative array for badge properties
     * @param bool|int $badge False to insert new badge, Badge GID to update it
     * @throws object Runtime class Exception if condition not met
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
        } else {
            throw new MroException\Runtime("METHOD FAILURE: setBadge expects an array of properties to setup a new badge or update an specified one for the loaded user.");
        }
    }

    /**
     * Get link to user profile page
     * @return string URL
     * @throws object Runtime class Exception if condition not met
     * @see URLHandler class
     * This took way longer than what it looks like, 
     * please take a second to appreciate this piece of code
     */
    public function getLink() {
        if ($this->handle) {
            $CVURL = new MroUtils\URLHandler;
            return $CVURL->linkMaker('users', $this->handle);
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getLink can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * This method will cost you a db query, do not waste it
     * @return string Email
     * @throws object Runtime class Exception if condition not met
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
            throw new MroException\Runtime("METHOD FAILURE: getEmail can only be called if object of class MroUser has been loaded with an existing user. Use getUser method first.", 1);
        }
    }

    /**
     * Get path to user img
     * @return string Image URL
     * @throws object Runtime class Exception if condition not met
     * @see Vista class
     * This also took way longer than expected and what it looks like, 
     * please take a second to appreciate this piece of code
     */
    public function getImg(string $size = 'max') {
        if ($this->GID) {
            if (!$this->info['img']) {
                return MroVista::getVistaUrl()
                    .MroVista::default('img', 'user');
            } else {
                if ($size = 'min') {
                    return getenv('APP_URL')
                    .'app/static/upload_min'.$this->info['img'];
                } else {
                    return getenv('APP_URL')
                    .'app/static/upload_'.$this->info['img'];
                }
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getImg can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.");
        }
    }

    /**
     * Set and upload user image
     * @param string $input Name of file type input field where image is loaded
     * @param int $width Width in pixels of image
     * @param int $height Height in pixels of image
     * @return string Image URL
     * @throws object Runtime class Exception if condition not met
     * @see MroUtils\IMG
     */
    public function setImg(string $input, int $width = 400, int $height = 400) {
        if ($this->GID) {
            $image = new MroUtils\IMG;
            $image->new($input, MROSTATIC, $width, false, 200);
            // store image hash
            $query = $this->sql()
                ->update('mro_users', [
                    'img' => $image->hash['sha']
                ])->where(field('GID')->eq($this->GID))
                ->compile();
            $this->pdo($query);
        } else {
            throw new MroException\Runtime("METHOD FAILURE: setImg can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.");
        }
    }

    /**
     * Loads and sets an user to perform a login with setLogin
     * @param string $user User handle or email
     * @return bool
     */
    public function getLogin(string $user) {
        $attempts = $this->session->get('loginAttempts', 0);
        // check if credential is a valid type
        if (!is_string($user)
        || empty($user)) {
            return false;
        }
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
            $this->session->set('loginAttempts', 0);
            $this->GID = $result['GID'];
            $this->password = $result['password'];
            $this->loadMeta();
            return true;
        // progressive delay for wrong credentials
        } else {
            $this->session->set('loginAttempts', $attempts++);
            if ($attempts > 3) {
                sleep($attempts);
            } elseif ($attempts > 9) {
                sleep($attempts*3);
            }
            return false;
        }
    }

    /**
     * Perform a password check and a login
     * @param string $password User password
     * @return bool
     * @throws object Runtime class Exception if condition not met
     */
    public function setLogin(string $password) {
        if ($this->password) {
            // check login attempts on db
            $attempts = $this->meta['login'];
            // check provided password
            if (empty($password)) {
                return false;
            }
            if ($attempts < time()) {
                if (password_verify($password, $this->password)) {
                    $this->load();
                    $this->setMeta([
                        'login' => 0,
                        'lastlogin' => time()
                    ]);
                    // attach user object to session
                    $user['info'] = $this->info;
                    $user['meta'] = $this->meta;
                    $user['GID'] = $this->GID;
                    $user['handle'] = $this->handle;
                    $this->session->set('User', $user);
                    return true;
                // progressive delay
                } else {
                    $this->setMeta(['login' => $attempts++]);
                    sleep($attempts);
                    return false;
                }
            // lockdown for 5 minutes
            } elseif ($attempts > 9) {
                $this->setMeta(['login' => time()+300]);
                $remoteAddress = $_SERVER['REMOTE_ADDR'];
                error_log("SECURITY: Too many (+10) failed login attempts with wrong password for user @$this->handle ($this->GID) from ip address: $remoteAddress");
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: setLogin can only be called if object of class MroUser has been loaded with an existing user. Use getLogin first.", 1);
        }
    }

    /**
     * Detaches an user from the current session
     * @throws object Runtime class Exception if condition not met
     */
    public function logout() {
        if ($this->GID) {
            $this->setMeta([
                'lastlogout' => time()
            ]);
            $this->session->set('User', false);
            $session = AuraSession();
            $session->destroy();
            $session->regenerateId();
        } else {
            throw new MroException\Runtime("METHOD FAILURE: logout can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }

    /**
     * Check if loaded user is in session
     * @return bool
     * @throws object Runtime class Exception if condition not met
     */
    public function isInSession() {
        if ($this->GID) {
            if (array_key_exists('GID', $this->session->get('User', []))) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: isInSession can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }

    /**
     * Get stories of current user
     * @param string $criteria Name of property to sort by
     * @param string $order Sort order
     * @return array|false Array with GIDs of stories entities
     * @throws object Runtime class Exception if condition not met
     */
    public function getStories(string $criteria = 'GID', string $order = 'DESC') {
        if ($this->GID) {
            // build query
            $query = $this->sql()
                ->select('GID')
                ->from('mro_stories')
                ->where(field('author')->eq($this->GID))
                ->order($criteria, $order)
                ->compile();
            $result = $this->pdo($query)->fetchAll();
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getStories can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }

    /**
     * Get posts of current user
     * @param string $criteria Name of property to sort by
     * @param string $order Sort order
     * @return array|false Array with GIDs of posts
     * @throws object Runtime class Exception if condition not met
     */
    public function getPosts(string $criteria = 'GID', string $order = 'DESC') {
        if ($this->GID) {
            // build query
            $query = $this->sql()
                ->select('GID')
                ->from('mro_posts')
                ->where(field('author')->eq($this->GID))
                ->order($criteria, $order)
                ->compile();
            $result = $this->pdo($query)->fetchAll();
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getPosts can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }

    /**
     * Get comments made by current user
     * @return array|false Array with GIDs of comments
     * @throws object Runtime class Exception if condition not met
     */
    public function getComments() {
        if ($this->GID) {
            // build query
            $query = $this->sql()
                ->select('GID')
                ->from('mro_comments')
                ->where(field('author')->eq($this->GID))
                ->compile();
            $result = $this->pdo($query)->fetchAll();
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getComments can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }

    /**
     * Get posts with stars given by current user
     * @return array|false Array with GIDs of posts
     * @throws object Runtime class Exception if condition not met
     */
    public function getStars() {
        if ($this->GID) {
            // build query
            $query = $this->sql()
                ->select('post')
                ->from('mro_stars')
                ->where(field('author')->eq($this->GID))
                ->compile();
            $result = $this->pdo($query)->fetchAll();
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            throw new MroException\Runtime("METHOD FAILURE: getStars can only be called if object of class MroUser has been loaded with an existing user. Use getUser first.", 1);
        }
    }
}