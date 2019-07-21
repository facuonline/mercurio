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
     * @param callable $callback Callback function to access submitted data
     * function (array $_POST, array $_FILES)
     * @param callable $fallback Callback function to be execute in case of submission being spam
     * function (array $_POST, array $_SESSION)
     * @param int $minTime Minimum number of seconds expected for completion
     */
    public static function submit(string $listener, callable $callback, callable $fallback = NULL, int $minTime = 5) {
        // Listen for submission, only POST as GET is prone to vulnerabilities
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST[$listener])) {
            $key = \Mercurio\App::getApp('url');
            // Check submission control fields
            if (empty($_POST[$key.'-email-url-website'])
            && empty($_POST[$key.'-comment-name-body'])
            && (time() - $_POST['formGeneratedAt']) > $minTime) {
                $_POST = filter_input_array(INPUT_POST);
                // Unset submission control variables
                $_POST = array_diff_key($_POST, [
                    $listener => '',
                    $key.'-email-url-website' => '',
                    $key.'-comment-name-body' => '',
                    'formGeneratedAt' => ''
                ]);
                
                $callback($_POST, $_FILES);
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