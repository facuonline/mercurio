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
 */
namespace Mercurio\Utils;
class URL extends \Mercurio\App\Database {

    /**
     * Return proper page query syntax based on state of url masking
     * @param string $page Fixed page name
     * @return string
     */
    private static function maskPage($page) {
        if (!self::isMaskingOn()) {
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
        if (empty($target)) $target = '0';
        if (!self::isMaskingOn()) {
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
        if (!self::isMaskingOn()) {
            return '&action='.$action;
        } else {
            return '/'.$action;
        }
    }

    /**
     * Builds and return links for specified targets
     * @param string $page Page name 
     * Specify '/' as a page for main page
     * @param mixed $target Target entity identifier, either handle or id
     * Specify '0' as a target for no target declared
     * @param string $action Target action name 
     * @return string
     */
    public static function getLink(string $page, $target = '', string $action = '') {
        if ($page === '/') return \Mercurio\App::getApp('URL');

        $page = self::maskPage($page);
        $target = self::maskTarget($target);
        $action = self::maskAction($action);
        return \Mercurio\App::getApp('URL')
            .$page
            .$target
            .$action;
    }

    /**
     * Filter, read and return GET query params
     * @param array $pages Expected pages, array of values
     * @return array 
     */
    public static function getUrlParams(array $pages = []) {
        $params = [];

        if (isset($_GET['page'])
        && !empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
            if (!empty($pages) 
            && in_array($page, $pages)) {
                $params['page'] = trim($page);
            } else {
                $params['page'] = false;
            }
        } else {
            $params['page'] = false;
        }

        if (isset($_GET['action'])
        && !empty($_GET['action'])) {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
            $params['action'] = trim($action);
        } else {
            $params['action'] = false;
        }
        
        if (isset($_GET['target'])
        && !empty($_GET['target'])
        && $_GET['target'] !== '0') {
            $target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_STRING);
            $params['target'] = trim($target);
        } else {
            $params['target'] = false;
        }

        return $params;
    }

    /**
     * Obtain page from URL query
     * @param array $pages Expected pages, array of values
     * @param callable $callback Callback function to execute on page retrieval
     * function (string $page) :
     * @param callable $fallback Callback function to execute if no page specified
     * function () :
     * @return string|bool Name of called page or false if page does not exist
     */
    public static function getPage(array $pages, callable $callback = NULL, callable $fallback = NULL) {
        $page = self::getUrlParams($pages)['page'];
        if ($page && $callback !== NULL) {
            return $callback($page);
        } elseif ($fallback !== NULL) {
            return $fallback();
        }
        return $page;
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
     * Check if url masking is on or off
     * @return bool
     */
    public static function isMaskingOn() : bool {
        // Solution for non database cases
        if (!getenv('DB_NAME')) {
            $location = dirname($_SERVER['SCRIPT_FILENAME'])
                .DIRECTORY_SEPARATOR
                .'.htaccess';
            $htaccess = self::readHtaccess();
            foreach ($htaccess as $key => $value) {
                if (strpos($value, "Mercurio URL masking")) return true;
            }
            return false;
        }

        return self::getConfig('urlmasking');
    }

    /**
     * Sets up URL masking via .htaccess file
     * @throws object Usage exception if no path to htaccess specified
     */
    public static function setUrlMasking() {
        $location = dirname($_SERVER['SCRIPT_FILENAME'])
            .DIRECTORY_SEPARATOR
            .'.htaccess';
        if (file_exists($location) && !is_readable($location)) throw new \Mercurio\Exception\Runtime("The file located at '$location' could not be accessed or is not readable. URL masking could not be possible.");
        if (!function_exists('apache_get_modules')) throw new \Mercurio\Exception\Environment("Apache seems to not be running or active on this server. URL masking is not possible without Apache.");
        if (!in_array('mod_rewrite', apache_get_modules())) throw new \Mercurio\Exception\Environment("Apache module 'mod_rewrite' is not present. URL masking is not possible without mod_rewrite.");

        if (!self::isMaskingOn()) {
            $htaccess = self::readHtaccess($location);
            $htaccess = self::startHtaccess($htaccess);
            $htaccess = self::referrerHtaccess($htaccess);
            $htaccess = self::endHtaccess($htaccess);
            self::writeHtacess($location, $htaccess);
        }
    }

    /**
     * Reads .htaccess file to allow fancy URL masking
     */
    private static function readHtaccess(string $location) {
        if (file_exists($location)) {
            return file($location);
        } else {
            return [""];
        }
    }

    /**
     * Starts rewrite engine
     */
    private static function startHtaccess($htaccess) {
        $engine = count($htaccess)+2;
        foreach ($htaccess as $key => $value) {
            if (strpos($value, "Mercurio URL masking")) {
                $engine = false;
            }
        }
        if ($engine) {
            $htaccess[$engine] = "# Mercurio URL masking \n<IfModule mod_rewrite.c>\nRewriteEngine On";
        }
        return $htaccess;
    }

    /**
     * Stops rewrite engine
     */
    private static function endHtaccess($htaccess) {
        $end = count($htaccess)+1;
        foreach ($htaccess as $key => $value) {
            if (strpos($value, "</IfModule>\n# URL masking end")) {
                $start = $key+1;
            }
        }
        if ($end) {
            $htaccess[$end] = "</IfModule>\n# URL masking end";
        }
        return $htaccess;
    }

    /**
     * Sets up a rewrite mask for referrers and targets
     */
    private static function referrerHtaccess($htaccess) {
        $cond = count($htaccess);
        foreach ($htaccess as $key => $value) {
            if (strpos($value, '# Mercurio URL masking ')) {
                $cond = $key+3;
            }
        }
        $htaccess[$cond] = "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)/(.*)/(.*)$ ?page=$1&target=$2&action=$3\n";
        return $htaccess;
    }

    /**
     * Writes to htacess
     */
    private static function writeHtacess(string $location, $htaccess) {
        file_put_contents($location, $htaccess);
        self::setConfig('urlmasking', true);
    }

}