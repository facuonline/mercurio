<?php
/**
 * URL
 * @package Mercurio
 * @package Utilitary classes
 * 
 * URL handler and worker
 * not only does rewrites but also manages paths to things and other cool things
 * 
 * @var array $htacess .htacesss file into an array
 * @var bool $mod_rewrite State of mod_rewrite module, can't make vanities without it
 */

namespace Mercurio\Utils;
class URL extends \Mercurio\App\Database {

    protected $htaccess, $mod_rewrite;

    /**
     * Determine wether a provided string is a valid referrer type
     * @param string $fixed Fixed referrer name. Expected 'users', 'stories', 'posts', 'sections', 'messages', 'search', 'admin'
     * @return string
     * @throws object Runtime class Exception if condition not met
     */
    private static function referrerFixed(string $fixed) {
        if ($path == 'users') {
            return 'refrrUsers';
        } elseif ($path == 'stories') {
            return 'refrrStories';
        } elseif ($path == 'posts') {
            return 'refrrPosts';
        } elseif ($path == 'sections') {
            return 'refrrSections';
        } elseif ($path == 'messages') {
            return 'refrrMessages';
        }  elseif ($path == 'search') {
            return 'refrrSearch';
        }  elseif ($path == 'admin') {
            return 'refrrAdmin';
        } else {
            throw new \Exception\Runtime("Unable to locate referrer of '$fixed', expected 'users', 'stories', 'posts', 'sections', 'messages', 'search', 'admin'", 400);
        }
    }

    /**
     * Get preset referrer path to something \
     * To read the referrer in a given URL use getUrlParams()
     * @param string $fixed Expected 'users', 'collections', 'media', 'messages', 'search', 'admin'
     * @param bool $link Is it for a link?
     * @return string URL
     */
    public static function getReferrer(string $fixed,  bool $link = true) {
        if ($link) {
            if (self::isMaskingOn()) {
                $referrer = self::referrerFixed($fixed);
                return self::getConfig($referrer).'/';
            } else {
                return '?referrer='.$fixed;
            }
        } else {
            return $fixed;
        }
    }

    /**
     * Get an associative array of referrers with their fixed name and their set up masked one
     * @return array
     */
    public static function getReferrerList() {
        return [
            'users' => self::getReferrer('users', false),
            'collections' => self::getReferrer('collections', false),
            'media' => self::getReferrer('media', false),
            'messages' => self::getReferrer('messages', false),
            'search' => self::getReferrer('search', false),
            'admin' => self::getReferrer('admin', false)
        ];
    }

    /**
     * Sets up a referrer value
     * @param string $fixed Referrer fixed name
     * @param string $value Referrer new masking value
     */
    public static function setReferrer(string $fixed, string $value) {
        $referrer = self::referrerFixed($fixed);
        self::setConfig($referrer, $value);
    }

    /**
     * Return proper target query syntax based on state of url masking
     * @param mixed $target ID or handle of target Database object
     * @param bool $link Is it for a link?
     * @return string
     */
    private static function getTarget($target, bool $link) {
        if (self::isMaskingOn()) {
            return $target;
        } else {
            return $target;
        }
    }

    /**
     * Builds and return links for specified targets
     * @param string $referrer Referrer type
     * @param mixed $target Target entity identifier, either handle or GID
     * @return string
     */
    public static function getLink(string $referrer, $target) {
        return urlencode(\Mercurio\App::getApp('URL')
        .self::getReferrer($referrer)
        .self::getTarget($target));
    }

    /**
     * Filter, read and return GET query params
     * @return array 
     */
    public static function getUrlParams() {
        $params = [];
        if (isset($_GET['referrer'])
        && !empty($_GET['referrer'])) {
            $referrer = filter_input(INPUT_GET, 'referrer', FILTER_SANITIZE_STRING);
            if ($referrer == self::getConfig('refrrUsers')) {
                $params['Referrer'] = 'users';
            } elseif ($referrer == self::getConfig('refrrStories')) {
                $params['Referrer'] = 'stories';
            } elseif ($referrer == self::getConfig('refrrPosts')) {
                $params['Referrer'] = 'posts';
            } elseif ($referrer == self::getConfig('refrrSections')) {
                $params['Referrer'] = 'sections';
            } elseif ($referrer == self::getConfig('refrrMessages')) {
                $params['Referrer'] = 'messages';
            } elseif ($referrer == self::getConfig('refrrSearch')) {
                $params['Referrer'] = 'search';
            } elseif ($referrer == self::getConfig('refrrAdmin')) {
                $params['Referrer'] = 'admin';
            } else {
                $params['Referrer'] = false;
            }
        } else {
            $params['Referrer'] = false;
        }
        if (isset($_GET['action'])
        && !empty($_GET['action'])) {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
            $params['Action'] = $action;
        } else {
            $params['Action'] = false;
        }
        if (isset($_GET['target'])
        && !empty($_GET['target'])) {
            $target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
            $params['Target'] = $target;
        } else {
            $params['Target'] = false;
        }
        return $params;
    }

    /**
     * Turn a string into something you can use in an URL
     * @param string $input Initial string
     * @param bool $sign Add a pseudo unique discriminator
     * @param array $replace Target characters to be replaced
     * @param string $delimiter Glue of returned string
     * @return string
     */
    public static function slugify(string $input, bool $sign = false, array $replace = [], $delimiter = ' ') {
        // Reset the locale
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        // Make it a valid slug
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $input);
        $clean = strtolower($clean);
        if (!empty($replace)) $clean = str_replace($replace, $delimiter, $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        if ($sign) $clean .= '-'.base_convert(time(), 10, 16);
        $clean = trim($clean, $delimiter);
        setlocale(LC_ALL, $oldLocale);
        return urlencode($clean);
    }

    /**
     * Check if url masking is on or off
     * @return bool
     */
    protected static function isMaskingOn() {
        if (self::getConfig('urlmasking')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets up URL masking via .htaccess file
     * @param string $htaccess Absolute path to htaccess file
     * @throws object Usage exception if no path to htaccess specified
     */
    public static function setURLMasking(string $htaccess) {
        if (!is_string($htaccess)) throw new \Mercurio\Exception\Usage("setURLMasking expects a string parameter. This parameter must be the absolute path to .htaccess file.");
        if (file_exists($htaccess) && !is_readable($htaccess)) throw new \Mercurio\Exception\Runtime("The file located at '$htaccess' could not be accessed or is not readable. URL masking could not be possible.");

        if (array_key_exists('mod_rewrite', apache_get_modules())) {
            self::readHtaccess($htaccess);
            if (self::startHtaccess()) {
                self::referrerHtaccess();
                self::writeHtacess($htaccess);
            }
        }
    }

    /**
     * Reads .htaccess file to allow fancy URL masking
     */
    private static function readHtaccess(string $htaccess) {
        if (file_exists($htaccess)) {
            self::$htaccess = file($htaccess);
        } else {
            self::$htaccess = [""];
        }
    }
    /**
     * Starts rewrite engine
     */
    private static function startHtaccess() {
        $engine = count(self::$htaccess)+2;
        foreach (self::$htaccess as $key => $value) {
            if (strpos($value, "Mercurio CVURL")) {
                $engine = false;
            }
        }
        if ($engine) {
            self::$htaccess[$engine] = "# Mercurio CVURL \n<IfModule mod_rewrite.c>\nRewriteEngine On";
        }
        return $engine;
    }
    /**
     * Stops rewrite engine
     */
    private static function endHtaccess() {
        $end = count(self::$htaccess)+1;
        foreach (self::$htaccess as $key => $value) {
            if (strpos($value, "</IfModule>\n# CVURL end")) {
                $start = $key+1;
            }
        }
        if ($end) {
            self::$htaccess[$end] = "</IfModule>\n# CVURL end";
        }
    }
    /**
     * Sets up a rewrite mask for referrers and targets
     */
    private static function referrerHtaccess() {
        $cond = count(self::$htaccess);
        foreach (self::$htaccess as $key => $value) {
            if (strpos($value, '# Mercurio CVURL ')) {
                $cond = $key+3;
            }
        }
        self::$htaccess[$cond] = "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)/(.*)$ ?referrer=$1&target=$2\n";
    }
    /**
     * Writes to htacess
     */
    private static function writeHtacess(string $htaccess) {
        self::endHtaccess();
        file_put_contents($htaccess, self::$htaccess);
        self::setConfig('urlmasking', true);
    }

    /**
     * Guarantees that referrers are safe
     */
    public static function configReferrers() {
        // make sure referrers are always there
        $referrers = [
            'refrrUsers' => 'user',
            'refrrCollections' => 'collection',
            'refrrMedia' => 'media',
            'refrrMessages' => 'message',
            'refrrSearch' => 'search',
            'refrrAdmin' => 'admin'
        ];
        foreach ($referrers as $key => $value) {
            if (!self::getConfig($key)) {
                self::setConfig($key, $value);
            }
        }
    }
}