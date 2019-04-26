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

class MroUser extends MroDB {
    public $info, $meta, $GID, $handle;
    private $password;

    public function __construct() {
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
        return $user;
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
            )->fetchAll();
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
            )->fetchAll();
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
        } elseif (mroValidateSet($properties)) {
            $query = $this->sql()
                ->update('mro_users', $properties)
                ->where(field('GID')->eq($this->GID))
                ->compile();
            $this->pdo(
                $query->sql(),
                $query->params()
            );
        }
    }

    /**
     * Updates user meta info
     * @param array $meta Associative array of user meta info
     * @see setUser to learn how to insert a new mro_usermeta row
     */
    public function setMeta(array $meta = []) {
        $query = $this->sql()
            ->update('mro_usermeta', $meta)
            ->where(field('user')->eq($this->GID))
            ->compile();
        $this->pdo(
            $query->sql(),
            $query->params()
        );
    }

    /**
     * Deletes an user from database and app
     * @param bool $likeItNeverExisted True to delete user generated content
     */
    public function deleteUser(bool $likeItNeverExisted = false) {
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
                unlink(MROSTATIC.$value);
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
            $this->delete();
        } else {
            // mro_user
            $user = $this->sql()
                ->delete('mro_users')
                ->where('GID', $this->GID)
                ->compile();
            $this->pdo(
                $user->sql(),
                $user->params()
            );
            // user img
            if (strstr($this->getImg(), 'upload_')) {
                unlink($this->getImg());
            }
            // mro_usermeta
            $meta = $this->sql()
                ->delete('mro_usermeta')
                ->where('user', $this->GID)
                ->compile();
            $this->pdo(
                $meta->sql(),
                $meta->params()
            );
        }
    }

    public function getBadges() {

    }

    public function setBadge() {

    }

    public function getLink() {

    }

    public function getEmail() {

    }

    public function getImg() {

    }

    public function getLogin() {

    }

    public function setLogin() {

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