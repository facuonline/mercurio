<?php

namespace Mercurio\Utils;

/**
 * Data filtering, sanitization and validation made easy with a nice API
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Filter {

    /**
     * Validate boolean value
     * @param $input Variable to be validated as boolean
     * @param $default Default value to be returned if validation fails
     * @param bool $flag Whether or not to apply FILTER_NULL_ON_FAILURE flag
     * @return bool
     */
    public static function isBool($input, $default = false, bool $flag = false) {
        if ($flag) $flag = FILTER_NULL_ON_FAILURE;
        return filter_var($input, FILTER_VALIDATE_BOOLEAN, [
            'options' => [
                'default' => $default
            ],
            'flags' => $flag
        ]);
    }



    /**
     * Validate a domain name \
     * This filter looks at the lenght of the domain name, other strings that aren't domain names might pass the filter
     * @param $input Variable to be validated as a domain name
     * @param $default Default value to be returned if validation fails
     * @param bool $flag Whether or not to apply FILTER_FLAG_HOSTNAME flag
     * @return string|false
     */
    public static function isDomain($input, $default = NULL, bool $flag = false) {
        if ($flag) $flag = FILTER_FLAG_HOSTNAME;

        return filter_var($input, FILTER_VALIDATE_DOMAIN, [
            'options' => [
                'default' => $default
            ],
            'flags' => $flag
        ]);
    }

    /**
     * Validate an email address
     * @param $input Variable to be validated as an email address
     * @param $default Default value to be returned if validation fails
     * @param bool $flag Whether or not to apply FILTER_FLAG_EMAIL_UNICODE flag
     * @return string|false
     */
    public static function isEmail($input, $default = NULL, bool $flag = false) {
        if ($flag) $flag = FILTER_FLAG_EMAIL_UNICODE;

        return filter_var($input, FILTER_VALIDATE_EMAIL, [
            'options' => [
                'default' => $default
            ],
            'flags' => $flag
        ]);
    }

    /**
     * Validate a float number
     * @param $input Variable to be validated as a float
     * @param $default Default value to be returned on failure
     * @param $decimal Decimal option for FILTER_VALIDATE_FLOAT filter
     * @param bool $flag Whether or not to apply FILTER_FLAG_ALLOW_THOUSAND flag
     * @return float|false
     */
    public static function isFloat($input, $default = NULL, $decimal = NULL, bool $flag = false) {
        if ($flag) $flag = FILTER_FLAG_ALLOW_THOUSAND;

        return filter_var($input, FILTER_VALIDATE_FLOAT, [
            'options' => [
                'default' => $default,
                'decimal' => $decimal
            ],
            'flags' => $flag
        ]);
    }

    /**
     * Validate a float number
     * @param $input Variable to be validated as a float
     * @param $default Default value to be returned on failure
     * @param int $min_range Specify minimum valid range
     * @param int $max_range Specify maximum valid range
     * @param bool $allow_octal Whether or not to apply FILTER_FLAG_ALLOW_OCTAL flag
     * @param bool $allow_hex Whether or not to apply FILTER_FLAG_ALLOW_HEX flag
     * @return float|false
     */
    public static function isInt($input, $default = NULL, $min_range = NULL, $max_range = NULL, bool $allow_octal = false, bool $allow_hex) {
        return filter_var($input, FILTER_VALIDATE_INT, [
            'options' => [
                'default' => $default,
                'min_range' => $min_range,
                'max_range' => $max_range
            ],
            'flags' => [
                $allow_octal,
                $allow_hex
            ]
        ]);
    }

    /**
     * Validate an URL
     * @param $input Variable to be validated as an URL
     * @param $default Default value to be returned if validation fails
     * @param bool $scheme Whether or not to apply FILTER_FLAG_SCHEME_REQUIRED flag
     * @param bool $host Whether or not to apply FILTER_FLAG_HOST_REQUIRED flag
     * @param bool $path Whether or not to apply FILTER_FLAG_PATH_REQUIRED flag
     * @param bool $query Whether or not to apply FILTER_FLAG_QUERY_REQUIRED flag
     * @return string|false
     */
    public static function isUrl($input, $default = NULL, bool $scheme = false, bool $host = false, bool $path = false, bool $query = false) {
        if ($scheme) $scheme = FILTER_FLAG_ALLOW_THOUSAND;
        if ($host) $host = FILTER_FLAG_ALLOW_THOUSAND;
        if ($path) $path = FILTER_FLAG_ALLOW_THOUSAND;
        if ($query) $query = FILTER_FLAG_ALLOW_THOUSAND;        

        return filter_var($input, FILTER_VALIDATE_URL, [
            'options' => [
                'default' => $default
            ],
            'flags' => [
                $scheme,
                $host,
                $path,
                $query
            ]
        ]);
    }

    /**
     * Sanitize emails \
     * Remove all characters except letters, digits and !#$%&'*+-=?^_`{|}~@.[]. 
     * @param string $input
     * @return string Sanitized email address
     */
    public static function getEmail(string $input) {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize floats \
     * Remove all characters except digits, +- and optionally .,eE. 
     * @param string $input
     * @param array $flags Filter flags: \
     *  FILTER_FLAG_ALLOW_FRACTION, 
     *  FILTER_FLAG_ALLOW_THOUSAND, 
     *  FILTER_FLAG_ALLOW_SCIENTIFIC 
     * @return int
     */
    public static function getFloat(string $input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => $flags]);
    }

    /**
     * Sanitize integers \
     * Remove all characters except digits, plus and minus sign. 
     * @param string $input
     * @return int
     */
    public static function getInt(string $input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize strings \
     * Strip tags, optionally strip or encode special characters.
     * @param string $input String to be sanitized
     * @param array $flags Filter flags: \
     *  FILTER_FLAG_NO_ENCODE_QUOTES, 
     *  FILTER_FLAG_STRIP_LOW, 
     *  FILTER_FLAG_STRIP_HIGH, 
     *  FILTER_FLAG_STRIP_BACKTICK, 
     *  FILTER_FLAG_ENCODE_LOW, 
     *  FILTER_FLAG_ENCODE_HIGH, 
     *  FILTER_FLAG_ENCODE_AMP
     * @return string Sanitized string
     */
    public static function getString(string $input, array $flags = []) {
        return filter_var($input, FILTER_SANITIZE_STRING, ['flags' => $flags]);
    }

    /**
     * Sanitize URLs \
     * Remove all characters except letters, digits and $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=.
     * @param string $input
     * @return string Sanitized URL
     */
    public static function getUrl(string $input) {
        return filter_var($input, FILTER_SANITIZE_URL);
    }

}
