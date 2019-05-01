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
        // delete only user uploaded things
        $db = new MroDB;
        if (strstr($img, $db->getConfig('uploadPrefix'))) {
            unlink(MROSTATIC.'/'.$img);
        }
    }

/**
 * User and Session
 * Perform various user and session tasks
 */

    /**
     * DRY helper function 
     * Searches for hints of users id for MroUser class
     * @see MroUser class
     * @return mixed User hints (GID or @handle)
     */
    function mroNoUser() {
        // search user in http request via $_GET
        $URL = new MroUtils\URLHandler;
        if ($URL->getUrl()['referrer'] === 'users') {
            return $URL->getUrl()['target'];
        // search user attached to $_SESSION
        } elseif (mroSession()) {
            return mroSession()['GID'];
        } else {
            return false;
        }
    }

/**
 * Multipurpose
 * This set of functions have very different purposes and perform basic, core tasks
 */

    function mroTracy($todump) {
        echo "<pre>\n";
        var_dump($todump);
        echo "</pre>";
    }

    /**
     * Aura Session DRY helper
     * @return object Aura Session Factory instance
     */
    function AuraSession() {
        $session = new \Aura\Session\SessionFactory;
        return $session->newInstance($_COOKIE);
    }

    function mroReport() {

    }