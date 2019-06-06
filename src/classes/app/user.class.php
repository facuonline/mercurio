<?php
/**
 * User class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * 
 */
namespace Mercurio\App;
class User extends \Mercurio\App\Database {

    public $info, $meta;
    private $email, $password;

    /**
     * Finds an user hint either in $_GET or in $_SESSION
     * @return false|string|int
     */
    private function findHint() {
        $URL = new \Mercurio\Utils\URL;
        $Session = new \Mercurio\Utils\Session;
        if ($URL['referrer'] == 'users' && $URL['target']) {
            return $URL['target'];
        } elseif ($Session['user']) {
            return $Session['user'];
        } else {
            return false;
        }
    }

    /**
     * Load an user from database into instance
     * @param string|int $hint User identifier either string handle or integer GID
     */
    public function get($hint = false) {
        if (!$hint) $hint = $this->findHint();
    }

}