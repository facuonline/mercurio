<?php
/**
 * functions.php
 * @package Mercurio
 * @subpackage Included functions
 * 
 * Following is a set of functions meant to help developers, 
 * assist classes and perform different basic tasks
 * Mostly DRY things
 */

/**
 * Database transactions
 * Help MroDB and child classes to perform db queries and tasks
 */

    /**
     * Adds GID and stamp properties to an array for db entities insertation
     * @param array $set Set of properties to be stamped
     * @return array
     */
    function mroStampSet(array $set = []) {
        $set['GID'] = MroUtils\GID::new();
        $set['stamp'] = time();
        return $set;
    }

    /**
     * Validates that an array of properties for db entities has no GID and stamp properties
     * These values are given by Mercurio and can't be modified or given otherwise
     * @see mroStampSet
     * @param array $set Set of properties to be validated
     * @return mixed array of values or false
     */
    function mroValidateSet(array $set = []) {
        if (!array_key_exists('GID', $set)
        && !array_key_exists('stamp', $set)) {
            return $set;
        } else {
            return false;
        }
    }

/**
 * Static content
 * Manipulate and work with content in /static/ folder
 */

    /**
     * Delete user uploaded images
     * @param string $img Img hash name
     */
    function mroRemoveImg(string $img) {
        if (strstr($img, $db->getConfig('upload_'))) {
            unlink(MROSTATIC.'/'.$img);
        }
    }

/**
 * User and Session
 * Perform various user and session tasks
 */

    /**
     * Aura Session DRY helper
     * @return object Aura Session Factory instance
     */
    function AuraSession() {
        $session = new \Aura\Session\SessionFactory;
        return $session->newInstance($_COOKIE);
    }

    /**
     * Session retriever, check if there is an user object attached to the session
     * @return false|object
     */
    function mroSession() {
        $session = AuraSession();
        if ($session->get('GID')) {
            return $session->get('User');
        } else {
            return false;
        }
    }

/**
 * Multipurpose
 * This set of functions have very different purposes and perform basic, core tasks
 */

    /**
     * @todo Delete this function once finished dev,
     * should probably get a real debugger or even bother to use vscode built in
     */
    function mroTracy($todump) {
        echo "<pre>\n";
        var_dump($todump);
        echo "</pre>";
    }

    /**
     * Generate sha256 hashes to be used as strong keys
     * @return string
     */
    function mroKeyGen(){
        $lame[] = microtime();
        $lame[] = mt_rand(1111, 9999);
        $lame[] = getenv('APP_URL');
        $lame[] = openssl_random_pseudo_bytes(16);
        $glue = base64_encode(random_bytes(4));
        shuffle($lame);
        return hash('sha256', implode($glue, $lame));
    }

    function mroReport() {

    }