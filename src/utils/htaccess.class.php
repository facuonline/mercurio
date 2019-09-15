<?php 

namespace Mercurio\Utils;

/**
 * Htaccess utils for Klein router \
 * Read and write htaccess rewrite conditions via Apache's mod_rewrite
 * 
 * @package Mercurio
 * @subpackage Router utils
 * @see https://github.com/klein/klein.php/wiki/Sub-Directory-Installation
 */
class Htaccess {

    /**
     * Check if url masking is on or off
     * @param string $location Path to htaccess file
     * @return bool
     */
    public static function isMaskingOn(string $location = '') : bool {
        if (empty($location)) {
            $location = dirname($_SERVER['SCRIPT_FILENAME'])
                .DIRECTORY_SEPARATOR
                .'.htaccess';
        }

        $htaccess = self::readHtaccess($location);
        foreach ($htaccess as $key => $value) {
            if (strpos($value, "Mercurio URL masking")) return true;
        }
        return false;
    }

    /**
     * Sets up URL masking via .htaccess file
     * @param string $location Path to htaccess file
     * @throws object Usage exception if no path to htaccess specified
     */
    public static function setMasking(string $location = '') {
        if (empty($location)) {
            $location = dirname($_SERVER['SCRIPT_FILENAME'])
            .DIRECTORY_SEPARATOR
            .'.htaccess';
        }

        if (file_exists($location) && !is_readable($location)) throw new \Mercurio\Exception\Runtime("The file located at '$location' could not be accessed or is not readable. URL masking could not be possible.");
        if (!function_exists('apache_get_modules')) throw new \Mercurio\Exception\Environment("Apache seems to not be running or active on this server. URL masking is not possible without Apache.");
        if (!in_array('mod_rewrite', apache_get_modules())) throw new \Mercurio\Exception\Environment("Apache module 'mod_rewrite' is not present. URL masking is not possible without mod_rewrite.");
        
        if (!self::isMaskingOn($location)) {
            $htaccess = self::readHtaccess($location);
            $htaccess = self::startHtaccess($htaccess);
            $htaccess = self::routeHtaccess($htaccess);
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
            if (strpos($value, "# Mercurio URL masking")) {
                $engine = false;
            }
        }

        if ($engine) {
            $htaccess[$engine] = "# Mercurio URL masking\n<IfModule mod_rewrite.c>\nRewriteEngine On\n";
        }
        
        return $htaccess;
    }

    /**
     * Sets up a rewrite mask for referrers and targets
     */
    private static function routeHtaccess($htaccess) {
        $app = APP_PATH;
        $conditions = count($htaccess)+3;
        
        foreach ($htaccess as $key => $value) {
            if (strpos($value, "RewriteBase $app")) {
                $conditions = false;
            }
        }

        if ($conditions) {
            $htaccess[$conditions] = "\nRewriteBase $app\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteRule ^ index.php [L]\n";
        }

        return $htaccess;
    }

    /**
     * Stops rewrite engine
     */
    private static function endHtaccess($htaccess) {
        $end = count($htaccess)+1;
        
        foreach ($htaccess as $key => $value) {
            if (strpos($value, "# URL masking end")) {
                $end = false;
            }
        }

        if ($end) {
            $htaccess[$end] = "\n</IfModule>\n# URL masking end\n";
        }
        
        return $htaccess;
    }

    /**
     * Writes to htacess
     */
    private static function writeHtacess(string $location, $htaccess) {
        file_put_contents($location, $htaccess);
    }

}
