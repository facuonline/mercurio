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

class util_MroCVURL extends MroDB {
    public $baseUrl, $referrer, $target;
    protected $htacess, $mod_rewrite;

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
     * Get referrer path to something or modify it
     * @param string $path Expected 'users', 'stories', 'posts', 'sections', 'messages', 'search'
     * @param string $value New value of referrer to set
     * @return string URL
     */
    public function referrer(string $path, $value = false) {
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
            $this->setConfig($referrer, $value);
        } elseif ($referrer) {
            return $this->getConfig($referrer);
        }
    }

    /**
     * Builds and return links
     * @param string $referrer Referrer type
     * @param mixed $target Target entity identifier, either handle or GID
     * @return string
     */
    public function link(string $referrer, $target) {
        return $this->baseUrl.$this->referrer($referrer).'/'.$target;
    }

    /**
     * Reads .htaccess file to allow fancy URL masking
     */
    private function readHtaccess() {
        if (file_exists(MROINDEX.'/.htaccess')) {
            $this->htacess = file(MROINDEX.'/.htaccess');
        } else {
            $this->htacess = [];
        }
    }
    /**
     * Starts rewrite engine
     */
    private function startHtaccess() {
        $start = false;
        foreach ($this->htacess as $key => $value) {
            if (!strpos($value, '# Mercurio CVURL')) {
                $start = count($this->htacess)+1;
            } else {
                $start = false;
            }
        }
        if ($start) {
            $this->htacess[$start] = "# Mercurio CVURL \n<IfModule mod_rewrite.c>\nRewriteEngine On\n";
            return true;
        } else {
           return false;
        }
    }
    /**
     * Stops rewrite engine
     */
    private function endHtaccess() {
        $end = count($this->htacess)+1;
        foreach ($this->htacess as $key => $value) {
            if (strpos($value, "</IfModule>\n# CVURL end")) {
                $start = $key+1;
            }
        }
        if ($end) {
            $this->htacess[$end] = "</IfModule>\n# CVURL end";
        }
    }
    /**
     * Writes to htacess
     */
    private function writeHtacess() {
        $this->endHtaccess();
        file_put_contents(MROINDEX.'/.htacess', $this->htacess);
        $this->configReferrers();
    }
    /**
     * Sets up a rewrite mask for referrers and targets
     */
    public function referrerHtaccess() {
        $cond = count($this->htacess)+1;
        foreach ($this->htacess as $key => $value) {
            if (strpos($value, '# Mercurio CVURL')) {
                $cond = $key+3;
            }
        }
        $this->htacess[$cond] = "\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)/(.*)$ ?referrer=$1&target=$2\n";
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