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
     * Validate submission of form against SPAM
     * @param string $name Form listener to listen for
     * @param string $method Method of form submissions
     * @param int $minTime Minimum number of seconds expected for completion
     * @return bool True on SPAM detection, false on no SPAM
     */
    public static function spam(string $name, string $method = 'POST', int $minTime = 5) : bool {
        if ($_SERVER['REQUEST_METHOD'] === $method
        && isset($_POST[$name])) {
            $key = \Mercurio\App::getApp('url');
            if (!empty($_POST[$key.'-email-url-website'])) return true;
            if (!empty($_POST[$key.'-comment-name-body'])) return true;
            if ((time() - $_POST['formGeneratedAt']) < $minTime) return true;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Generate honeypot and timestamped inputs for spam control
     * @return array
     */
    private static function honeypot() {
        // antispam honeypot and control
        $time = time(); $key = \Mercurio\App::getApp('key');
        $pot = "<!--These inputs are not for humans--><!--If you are human do not fill in next inputs--><input type='text' name='$key-email-url-website' style='display:none;'><input type='text' name='$key-comment-name-body' style='display: none;'><input type='text' name='formGeneratedAt' value='$time' style='display:none;'><!--End of non human inputs-->";
        return $pot;
    }

    /**
     * Create new form
     * @param string $method Form method
     * @param string $listener Form listener and trigger
     * @param array $given Form properties
     */
    public static function new(string $method, string $listener, array $given = []) {
        $attributes = ''; $honeypot = '';
        foreach ($given as $key => $value) {
            $attributes .= "$key='$value'";
        }
        $honeypot = self::honeypot();
        echo "<form method='$method' $attributes>\n<input type='hidden' name='$listener' value='formSubmitted'>\n$honeypot";
    }

    /**
     * Terminate form
     */
    public static function end() {
        echo "</form>";
    }

}