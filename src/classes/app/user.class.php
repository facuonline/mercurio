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
        if (\Mercurio\Utils\URL::getURLParams()['Referrer'] == 'users'
        && \Mercurio\Utils\URL::getURLParams()['Target']) {
            return \Mercurio\Utils\URL::getURLParams()['Target'];
        }
        if (\Mercurio\Utils\Session::get()['User']) {
            return \Mercurio\Utils\Session::get()['User']['GID'];
        }
        return false;
    }

    /**
     * Load an user from database into instance
     * @param string|int $hint User identifier either string handle or integer GID
     */
    public function get($hint = false) {
        if (!$hint) $hint = $this->findHint();
    }

    /**
     * Attach user info to session
     */
    private function attachSession(){
        \Mercurio\Utils\Session::set([
            $this->info,
        ], 'User');
    }

}