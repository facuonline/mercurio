<?php
/**
 * Media class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var array $info Associative array with general media info
 * @var array $meta Associative array of meta values attached to media
 */
namespace Mercurio\App;
class Media extends \Mercurio\App\Database {

    public $info, $meta;

    public function __construct() {
        $this->info = false;
        $this->meta = [];
    }

    /**
     * Finds a media hint via $_GET
     * @return null|int
     */
    protected function findHint() {
        if ($this->info) return $this->info['id'];
        // Get media hint from URL query
        if (\Mercurio\Utils\URL::getTarget()) return \Mercurio\Utils\URL::getTarget();

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
        $media = $this->db()->get(DB_MEDIA, '*', [
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
            $this->db()->update(DB_MEDIA,
                $properties,
                $media['id']
            );
        });
    }

    /**
     * Deletes media from database and it's associated data
     */
    public function unset() {
        $this->get(false, function ($media) {
            $this->db()->delete(DB_MEDIA, ['id' => $media['id']]);
            $this->unsetMeta();
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
            return $this->dbGetMeta($media['id'], $meta, $grouping);
        });
    }

    /**
     * Set and update media meta
     * @param array $meta Associative array of meta names and values
     * @param string $grouping Name of meta group
     */
    public function setMeta(array $meta, string $grouping = '') {
        $this->get(false, function($media) use (&$meta, $grouping) {
            $this->dbSetMeta($media['id'], $meta, $grouping);
        });
    }

    /**
     * Deletes media meta from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     * @param string $grouping Name of meta group
     */
    public function unsetMeta($meta = '', string $grouping = '') {
        $this->get(false, function ($media) use (&$meta, $grouping) {
            $this->dbUnsetMeta($media['id'], $meta, $grouping);
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
        $this->db()->insert(DB_MEDIA, $properties);
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
     * Get absolute link to media
     * @param string $page Media page
     * @param string $action Optional media action
     * @return string URL
     */
    public function getLink(string $page, string $action = '') {
        return $this->get(false, function($media) use (&$page, &$action) {
            return (string) \Mercurio\Utils\URL::getLink($page, $media['id'], $action);
        });
    }

    /**
     * Get media channel info
     * @param callable $callback Callback function to manipulate channel data
     * function (array $channel) :
     * @return array
     */
    public function getChannel(callable $callback = NULL) {
        $this->get(false, function($media) use (&$callback) {
            $channel = $this->db()->select(DB_CHANNELS, '*', [
                'id' => $media['channel']
            ]);
            if ($callback !== NULL) return $callback($channel);
            return $channel;
        });
    }

}
