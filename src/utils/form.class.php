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
     * @param string $listener Form listener to listen for
     * @param callback $callback Callback function to access submitted data, returned parameters are: $_POST, $_FILES
     * @param callback $fallback Callback function to be execute in case of submission being spam, returned parameters are: $_POST, $_SESSION
     * @param string $method Method of form submissions
     * @param int $minTime Minimum number of seconds expected for completion
     */
    public static function submit(string $listener, callable $callback, callable $fallback = NULL, string $method = 'POST', int $minTime = 5) {
        if ($_SERVER['REQUEST_METHOD'] === $method
        && isset($_POST[$listener])) {
            $key = \Mercurio\App::getApp('url');
            if (empty($_POST[$key.'-email-url-website'])
            && empty($_POST[$key.'-comment-name-body'])
            && (time() - $_POST['formGeneratedAt']) > $minTime) {
                $callback(filter_input_array(INPUT_POST), $_FILES);
            } else {
                $fallback(filter_input_array(INPUT_POST), \Mercurio\Utils\Session::get());
            }
        }
    }

    /**
     * Generate honeypot and timestamped inputs for spam control
     * @return array
     */
    private static function honeypot() {
        // antispam honeypot and control
        $time = time(); $key = \Mercurio\App::getApp('url');
        $pot = "<!--These inputs are not for humans--><!--If you are human do not fill in next inputs--><input type='text' name='$key-email-url-website' style='display:none;'><input type='text' name='$key-comment-name-body' style='display: none;'><input type='text' name='formGeneratedAt' value='$time' style='display:none;'><!--End of non human inputs-->";
        return $pot;
    }

    /**
     * Create new form
     * @param string $method Form method
     * @param string $listener Form listener and trigger
     * @param array $given Form properties
     */
    public static function new(string $listener, string $method = 'POST', array $given = []) {
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