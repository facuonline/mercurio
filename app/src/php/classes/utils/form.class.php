<?php
/**
 * Form class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * Form management and security
 */

namespace Mercurio\Utils;
class Form {

    /**
     * Get sanitized values from submitted POST requests
     * @param string $name Name of key from $_POST array
     * @param string $expected Type of expected content to get
     * @return mixed
     */
    public static function get(string $name, string $expected = '') {
        if (!empty($expected)) {
            if ($expected = 'string') {
                $filter = FILTER_SANITIZE_STRING;
            } elseif ($expected = 'int') {
                $filter = FILTER_SANITIZE_NUMBER_INT;
            } elseif ($expected = 'email') {
                $filter = FILTER_SANITIZE_EMAIL;
            } elseif ($expected = 'url') {
                $filter = FILTER_SANITIZE_URL;
            } else {
                $filter = FILTER_SANITIZE_SPECIAL_CHARS;
            }
        } else {
            $filter = FILTER_SANITIZE_SPECIAL_CHARS;
        }
        $_POST = filter_input_array(INPUT_POST, [
            $name => $filter
        ]);
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
    }

    /**
     * Validate submission of form
     * @param string $name Form name to listen for
     * @param string $method Method of form submissions
     * @param int $time Minimun number of seconds expected for completion
     * @return bool
     */
    public static function submission(string $name, string $method = 'POST', int $time = 5) {
        if ($_SERVER['REQUEST_METHOD'] === $method
        && isset($_POST[$name])) {
            if (!empty($_POST['inputPot-email-url-website'])) return false;
            if ((time() - $_POST['formGeneratedAt']) < $time) return false;
            return true;
        }
    }

    /**
     * Generate honeypot and timestamped inputs
     * @return array
     */
    private static function honeypot() {
        // antispam honeypot and control
        $time = time(); 
        $pot[] = "<!--This inputs are not for humans-->";
        $pot[] = "<input type='text' name='inputPot-email-url-website' style='display:none;'>";
        $pot[] = "<input type='text' name='formGeneratedAt' value='$time' style='display:none;'>";
        $pot[] = "<!--End of non human inputs-->";
        return $pot;
    }

    /**
     * Create new form
     * @param array $form
     */
    public static function new(array $given) {
        // method
        if (!array_key_exists('method', $given)) {
            throw new \Exception\Runtime("MISSING ARRAY KEY: compose() expects a 'method' key index in given array.");
        }
        // listener
        if (!array_key_exists('listener', $given)) {
            throw new \Exception\Runtime("MISSING ARRAY KEY: compose() expects a 'listener' key index in given array.");
        } else {
            $listener = $given['listener'];
            unset($given['listener']);
        }
        $attributes = ''; $honeypot = '';
        foreach ($given as $key => $value) {
            $attributes .= "$key='$value'";
        }
        foreach (self::honeypot() as $key => $value) {
            $honeypot .= $value;
        }
        echo "<form $attributes>\n<input type='hidden' name='$listener'>\n$honeypot";
    }

}