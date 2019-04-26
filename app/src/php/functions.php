<?php
/**
 * functions.php
 * @package Mercurio
 * @package Included functions
 * 
 * Following is a set of functions meant to help developers, assist classes and perform different basic tasks
 * Mostly DRY things
 */

/**
 * Multipurpose
 * This set of functions have very different purposes and perform basic, core tasks
 */

    /**
     * Adds GID and stamp properties to an array for db entities insertation
     * @param array $set Set of properties to be stamped
     * @return array
     */
    function mroStampSet(array $set = []) {
        $set['GID'] = new MroGID;
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
     * DRY helper function 
     * Searches for hints of users id for MroUser class
     * @see MroUser class
     * @return mixed User hints (GID or @handle)
     */
    function mroNoUser() {
        // search user in http request via $_GET
        $httpRequest = new Nette\Http\Request;
        if ($httpRequest->isMethod('GET')) {
            return $httpRequest->getQuery('user');
        // search user attached to $_SESSION
        } elseif (mroSession()) {
            return mroSession()['GID'];
        } else {
            return false;
        }
    }