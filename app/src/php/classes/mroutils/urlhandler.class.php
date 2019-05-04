<?php
/**
 * URLHandler
 * @package Mercurio
 * @package Included classes
 * 
 * URL handler and worker
 * not only does rewrites but also manages path to things
 * 
 * @var string $baseUrl site URL address
 * @var string $referrer site referrer to content types
 * @var string $target Destination object identifier query
 * @var array $htacess .htacesss file into an array
 * @var bool $mod_rewrite State of mod_rewrite module, can't make vanities without it
 */

namespace MroUtils;
use MroDB;
class URLHandler extends MroDB {
    public $baseUrl, $referrer, $target;
    protected $htaccess, $mod_rewrite;

    public function __construct() {
        $this->conn();
        $this->baseUrl = getenv('APP_URL');
        // check mod_rewrite availability
        if (in_array('mod_rewrite', apache_get_modules())) {
            $this->mod_rewrite = true;
            $this->readHtaccess();
            if ($this->startHtaccess()) {
                $this->referrerHtaccess();
                $this->writeHtacess();
            }
		} else {
            $this->mod_rewrite = false;
        }
    }

    /**
     * Determine wether a provided path is a valid referrer type
     * @param string $path Expected 'users', 'stories', 'posts', 'sections', 'messages', 'search'
     * @return string
     * @throws object Runtime class Exception if condition not met
     */
    private function referrerPath(string $path) {
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
            throw new MroException\Runtime("Unable to locate path referrer to <<$path>>", 400);
        }
    }

    /**
     * Get preset referrer path to something.
     * To get the referrer in a given URL use getUrl()['referrer']
     * @param string $path Expected 'users', 'stories', 'posts', 'sections', 'messages', 'search'
     * @return string URL
     */
    public function getReferrer(string $path) {
        if ($this->mod_rewrite) {
            $referrer = $this->referrerPath($path);
            return $this->getConfig($referrer).'/';
        } else {
            return '?referrer='.$path;
        }
    }

    /**
     * Sets up a referrer value
     * @param string $path Referrer name
     * @param string $value Referrer new value
     */
    public function setReferrer(string $path, string $value) {
        $referrer = $this->referrerPath($path);
        $this->setConfig($referrer, $value);
    }

    public function getTarget($target) {
        if ($this->mod_rewrite) {
            return $target;
        } else {
            return '&target='.$target;
        }
    }

    /**
     * Builds and return links for specified targets
     * @param string $referrer Referrer type
     * @param mixed $target Target entity identifier, either handle or GID
     * @return string
     */
    public function linkMaker(string $referrer, $target) {
        return $this->baseUrl
            .$this->getReferrer($referrer)
            .$this->getTarget($target);
    }

    /**
     * Filter, read and return GET query params
     * @return array 
     */
    public function getUrl() {
        $params = [];
        if (isset($_GET['referrer'])
        && !empty($_GET['referrer'])) {
            $referrer = filter_input(INPUT_GET, 'referrer', FILTER_SANITIZE_STRING);
            if ($referrer == $this->getConfig('refrrUsers')) {
                $params['referrer'] = 'users';
            } elseif ($referrer == $this->getConfig('refrrStories')) {
                $params['referrer'] = 'stories';
            } elseif ($referrer == $this->getConfig('refrrPosts')) {
                $params['referrer'] = 'posts';
            } elseif ($referrer == $this->getConfig('refrrSections')) {
                $params['referrer'] = 'sections';
            } elseif ($referrer == $this->getConfig('refrrMessages')) {
                $params['referrer'] = 'messages';
            } elseif ($referrer == $this->getConfig('refrrSearch')) {
                $params['referrer'] = 'search';
            } elseif ($referrer == $this->getConfig('refrrAdmin')) {
                $params['referrer'] = 'admin';
            } else {
                $params['referrer'] = false;
            }
        } else {
            $params['referrer'] = false;
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
     * Reads .htaccess file to allow fancy URL masking
     */
    private function readHtaccess() {
        if (file_exists(MROINDEX.'/.htaccess')) {
            $this->htaccess = file(MROINDEX.'/.htaccess');
        } else {
            $this->htaccess = [""];
        }
    }
    /**
     * Starts rewrite engine
     */
    private function startHtaccess() {
        $engine = count($this->htaccess)+2;
        foreach ($this->htaccess as $key => $value) {
            if (strpos($value, "Mercurio CVURL")) {
                $engine = false;
            }
        }
        if ($engine) {
            $this->htaccess[$engine] = "# Mercurio CVURL \n<IfModule mod_rewrite.c>\nRewriteEngine On";
        }
        return $engine;
    }
    /**
     * Stops rewrite engine
     */
    private function endHtaccess() {
        $end = count($this->htaccess)+1;
        foreach ($this->htaccess as $key => $value) {
            if (strpos($value, "</IfModule>\n# CVURL end")) {
                $start = $key+1;
            }
        }
        if ($end) {
            $this->htaccess[$end] = "</IfModule>\n# CVURL end";
        }
    }
    /**
     * Sets up a rewrite mask for referrers and targets
     */
    public function referrerHtaccess() {
        $cond = count($this->htaccess);
        foreach ($this->htaccess as $key => $value) {
            if (strpos($value, '# Mercurio CVURL ')) {
                $cond = $key+3;
            }
        }
        $this->htaccess[$cond] = "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)/(.*)$ ?referrer=$1&target=$2\n";
    }
    /**
     * Writes to htacess
     */
    private function writeHtacess() {
        $this->endHtaccess();
        file_put_contents(MROINDEX.'/.htaccess', $this->htaccess);
        $this->configReferrers();
    }

    /**
     * Guarantees that referrers are safe
     */
    private function configReferrers() {
        // make sure referrers are always there
        $referrers = [
            'refrrUsers' => 'user',
            'refrrStories' => 'story',
            'refrrPosts' => 'post',
            'refrrSections' => 'section',
            'refrrMessages' => 'message',
            'refrrSearch' => 'search',
            'refrrAdmin' => 'admin'
        ];
        foreach ($referrers as $key => $value) {
            if (!$this->getConfig($key)) {
                $this->setConfig($key, $value);
            }
        }
    }
}