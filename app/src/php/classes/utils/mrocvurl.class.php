<?php
/**
 * MroCVURL class
 * @package Mercurio
 * @package Included classes
 * 
 * Custom Vanity URL handler and worker,
 * not only does rewrites but also manages path to things
 * 
 * @var string $baseUrl site URL address
 * @var string $referrer site referrer to content types
 * @var string $target Destination object identifier query
 * @var array $htacess .htacesss file into an array
 * @var bool $mod_rewrite State of mod_rewrite module, can't make vanities without it
 */

class utils_MroCVURL extends MroDB {
    public $baseUrl, $referrer, $target;
    protected $htaccess, $mod_rewrite;

    public function __construct() {
        $this->conn();
        $this->baseUrl = getenv('APP_URL');
        // check mod_rewrite availability
        if (in_array('mod_rewrite', apache_get_modules())) {
            $this->mod_rewrite = true;
            if ($this->readHtaccess()
            && $this->startHtaccess()) {
                $this->referrerHtaccess();
                $this->writeHtacess();
            } else {
                $this->htaccess = false;
            }
		} else {
            $this->mod_rewrite = false;
        }
    }

    /**
     * Get referrer path to something or modify it
     * @param string $path Expected 'users', 'stories', 'posts', 'sections', 'messages', 'search'
     * @param string $value New value of referrer to set
     * @return string URL
     */
    public function referrer(string $path, $value = false) {
        if ($this->htaccess) {
            if ($path == 'users') {
                $referrer = 'refrrUsers';
            } elseif ($path == 'stories') {
                $referrer = 'refrrStories';
            } elseif ($path == 'posts') {
                $referrer = 'refrrPosts';
            } elseif ($path == 'sections') {
                $referrer = 'refrrSections';
            } elseif ($path == 'messages') {
                $referrer = 'refrrMessages';
            }  elseif ($path == 'search') {
                $referrer = 'refrrSearch';
            }  elseif ($path == 'admin') {
                $referrer = 'refrrAdmin';
            } else {
                throw new Exception("Unable to locate path referrer to <<$path>>", 1);
            }
            if ($value && $referrer) {
                $this->setConfig($referrer, rtrim($value, '/').'/');
            } elseif ($referrer) {
                return $this->getConfig($referrer);
            }
        } else {
            return '?referrer='.$path;
        }
    }

    public function target($target) {
        if ($this->htaccess) {
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
    public function buildLink(string $referrer, $target) {
        return $this->baseUrl
            .$this->referrer($referrer)
            .$this->target($target);
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
            return true;
        } else {
            return false;
        }
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
            'refrrUsers' => 'user/',
            'refrrStories' => 'story/',
            'refrrPosts' => 'post/',
            'refrrSections' => 'section/',
            'refrrMessages' => 'message/',
            'refrrSearch' => 'search/',
            'refrrAdmin' => 'admin/'
        ];
        foreach ($referrers as $key => $value) {
            if (!$this->getConfig($key)) {
                $this->setConfig($key, $value);
            }
        }
    }
}