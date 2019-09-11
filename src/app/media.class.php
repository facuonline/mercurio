<?php

namespace Mercurio\App;

/**
 * Media entity class
 * @package Mercurio
 * @subpackage Included classes
 */
class Media {

    /**
     * Associative array of general media info
     */
    public $info;
    
    /**
     * Associative array of media meta properties and values
     */
    public $meta;

    /**
     * Instance of dependency injected Database class
     */
    protected $DB;

    /**
     * SQL query builder
     */
    private $SQL;

    public function __construct(\Mercurio\App\Database $db) {
        $this->info = false;
        $this->meta = [];
        $this->DB = $db;
        $this->SQL = $db->getSQL();
    }

    /**
     * Finds a media hint via $_GET
     * @return null|int
     */
    protected function findHint() {
        if ($this->info) return $this->info['id'];
        // Get media hint from URL query
        if (\Mercurio\Utils\Router::getTarget()) return \Mercurio\Utils\Router::getTarget();

        return NULL;
    }

    /**
     * Load a media record from database into instance
     * @param int $hint Media id
     * @param callable $callback Callback function to manipulate media without loading class
     * function (array $info) :
     * @param callable $fallback Callback function to execute in case of no media found
     * function () :
     * @return array|false Media info, false on no media found
     */
    public function get($hint = false, callable $callback = NULL, callable $fallback = NULL) {
        if (!$hint) $hint = $this->findHint();
        $media = $this->SQL->get(DB_MEDIA, '*', [
            'id' => $hint
        ]);
        // Return data or load instance
        if ($media) {
            if ($callback !== NULL) return $callback($media);

            $this->info = $media;
            return $this->info;
        } elseif ($fallback !== NULL) {
            return $fallback;
        }
    }

    /**
     * Update media properties
     * @param array $properties Associative array of media properties
     */
    public function set(array $properties) {
        $this->get(false, function ($media) use (&$properties) {
            $this->SQL->update(DB_MEDIA, 
                $properties, 
                ['id' => $media['id']]
            );
        });
    }

    /**
     * Deletes media from database and it's associated data
     */
    public function unset() {
        $this->get(false, function ($media) {
            $this->unsetMeta();
            $this->SQL->delete(DB_MEDIA, ['id' => $media['id']]);
        });
    }

    /**
     * Read media meta
     * @param string $meta Name of meta field or array of, leave blank to get all meta fields
     * @param string $grouping Name of meta group
     * @return bool|mixed|array
     */
    public function getMeta($meta = '', string $grouping = '') {
        return $this->get(false, function($media) use (&$meta, $grouping) {
            return $this->DB->dbGetMeta($media['id'], $meta, $grouping);
        });
    }

    /**
     * Set and update media meta
     * @param array $meta Associative array of meta names and values
     * @param string $grouping Name of meta group
     */
    public function setMeta(array $meta, string $grouping = '') {
        $this->get(false, function($media) use (&$meta, $grouping) {
            $this->DB->dbSetMeta($media['id'], $meta, $grouping);
        });
    }

    /**
     * Deletes media meta from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     * @param string $grouping Name of meta group
     */
    public function unsetMeta($meta = '', string $grouping = '') {
        $this->get(false, function ($media) use (&$meta, $grouping) {
            $this->DB->dbUnsetMeta($media['id'], $meta, $grouping);
        });
    }

    /**
     * Set a new media and load into instance 
     * @param array $properties Associative array of media properties
     * @param array $required $properties array keys of required content
     * @return array|false Media info, false on no user 
     * @throws Exception
     */
    public function new(array $properties, array $required = []) {
        // Ensure media properties are valid
        \Mercurio\Utils\System::required(['channel'], $properties, 'new');
        \Mercurio\Utils\System::emptyField($required, $properties);
        $properties = \Mercurio\Utils\System::property($properties);

        // Make media
        $this->SQL->insert(DB_MEDIA, $properties);
        $this->get($properties['id']);
        return $this->info;
    }

    /**
     * Get media numeric id
     * @param bool $string Return id as string
     * @return int|string Media ID
     */
    public function getID(bool $string = false) {
        return $this->get(false, function($media) use (&$string) {
            if ($string) return (string) $media['id'];
            return (int) $media['id'];
        });
    }

    /**
     * Get media content
     * @return mixed Media content
     */
    public function getContent() {
        return $this->get(false, function($media) {
            return $media['content'];
        });
    }

    /**
     * Get absolute link to media
     * @param string $page Media page
     * @param string $action Optional media action
     * @return string URL
     */
    public function getLink(string $page, string $action = '') {
        return $this->get(false, function($media) use (&$page, &$action) {
            return;
            // TODO
        });
    }

    /**
     * Get media channel
     * @return object Channel object instance loaded with media channel
     */
    public function getChannel() {
        return $this->get(false, function($media) {
            $channel = new \Mercurio\App\Channel(new \Mercurio\App\Database);
            $channel->get($media['channel']);
            return $channel;
        });
    }

    /**
     * Get media author
     * @return object User object instance loaded with media author
     */
    public function getAuthor() {
        return $this->get(false, function($media) {
            $author = new \Mercurio\App\User(new \Mercurio\App\Database);
            $author->get($media['author']);
            return $author;
        });
    }

}
