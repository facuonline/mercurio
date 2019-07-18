<?php
/**
 * URL
 * @package Mercurio
 * @package Utilitary classes
 * 
 * URL handler and worker \
 * not only does rewrites but also manages paths to things and other cool things \
 * Not to be confused with parse_url()
 * 
 * @var array $htacess .htacesss file into an array
 * @var bool $mod_rewrite State of mod_rewrite module, can't make vanities without it
 */

namespace Mercurio\Utils;
class URL extends \Mercurio\App\Database {

    protected $htaccess, $mod_rewrite;

    /**
     * Return proper page query syntax based on state of url masking
     * @param string $page Fixed page name
     * @return string
     */
    private static function maskPage($page) {
        if (self::isMaskingOn()) {
            return '?page='.$page;
        } else {
            return $page;
        }
    }

    /**
     * Return proper target query syntax based on state of url masking
     * @param mixed $target ID or handle of target Database object
     * @return string
     */
    private static function maskTarget($target) {
        if (empty($target)) return '';
        if (self::isMaskingOn()) {
            return '&target='.$target;
        } else {
            return '/'.$target;
        }
    }

    /**
     * Return proper action query syntax based on state of url masking
     * @param mixed $action Action name
     * @return string
     */
    private static function maskAction($action) {
        if (empty($action)) return '';
        if (self::isMaskingOn()) {
            return '&action='.$action;
        } else {
            return '/'.$action;
        }
    }

    /**
     * Builds and return links for specified targets
     * @param string $page Page name
     * @param mixed $target Target entity identifier, either handle or id
     * @param string $action Target action name \ 
     * Specify '+' as a target for page specific actions
     * @return string
     */
    public static function getLink(string $page, $target = '', string $action = '') {
        $link = [
            'page' => self::maskPage($page),
            'target' => self::maskTarget($target),
            'action' => self::maskAction($action)
        ];
        return \Mercurio\App::getApp('URL').urlencode(implode('', $link));
    }

    /**
     * Filter, read and return GET query params
     * @return array 
     */
    public static function getUrlParams() {
        $params = [];
        if (isset($_GET['page'])
        && !empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
            if (array_key_exists($page, [
                'user' => NULL,
                'message' => NULL,
                'media' => NULL,
                'collection' => NULL,
                'search' => NULL,
                'admin' => NULL
            ])) {
                $params['page'] = $page;
            }
        } else {
            $params['page'] = false;
        }
        if (isset($_GET['action'])
        && !empty($_GET['action'])) {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
            $params['action'] = $action;
        } else {
            $params['action'] = false;
        }
        if (isset($_GET['target'])
        && !empty($_GET['target'])) {
            $target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
            $params['target'] = $target;
        } else {
            $params['target'] = false;
        }
        return $params;
    }

    /**
     * Obtain page from URL query
     * @return string|bool Name of called page or false if page does not exist
     */
    public static function getPage() {
        return self::getUrlParams()['page'];
    }

    /**
     * Obtain target from URL query
     * @return mixed|bool Target ID or handle or false if target is not specified
     */
    public static function getTarget() {
        return self::getUrlParams()['target'];
    }

    /**
     * Obtain action from URL query
     * @return string|bool Action name or false if no action specified
     */
    public static function getAction() {
        return self::getUrlParams()['action'];
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
            if (strpos($value, "Mercurio URL masking")) {
                $engine = false;
            }
        }
        if ($engine) {
            self::$htaccess[$engine] = "# Mercurio URL masking \n<IfModule mod_rewrite.c>\nRewriteEngine On";
        }
        return $engine;
    }

    /**
     * Stops rewrite engine
     */
    private static function endHtaccess() {
        $end = count(self::$htaccess)+1;
        foreach (self::$htaccess as $key => $value) {
            if (strpos($value, "</IfModule>\n# URL masking end")) {
                $start = $key+1;
            }
        }
        if ($end) {
            self::$htaccess[$end] = "</IfModule>\n# URL masking end";
        }
    }

    /**
     * Sets up a rewrite mask for referrers and targets
     */
    private static function referrerHtaccess() {
        $cond = count(self::$htaccess);
        foreach (self::$htaccess as $key => $value) {
            if (strpos($value, '# Mercurio URL masking ')) {
                $cond = $key+3;
            }
        }
        self::$htaccess[$cond] = "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)/(.*)/(.*)$ ?page=$1&target=$2&action=$3\n";
    }

    /**
     * Writes to htacess
     */
    private static function writeHtacess(string $htaccess) {
        self::endHtaccess();
        file_put_contents($htaccess, self::$htaccess);
        self::setConfig('urlmasking', true);
    }

}