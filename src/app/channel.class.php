<?php
/**
 * Channel class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var array $info Associative array with general channel info
 * @var array $meta Associative array of meta values attached to channel
 */
namespace Mercurio\App;
class Channel extends \Mercurio\App\Database {

    public $info, $meta;

    public function __construct() {
        $this->info = false;
        $this->meta = [];
    }

    /**
     * Finds a channel hint via $_GET
     * @return null|string|int
     */
    protected function findHint() {
        if ($this->info) return $this->info['id'];
        // Get media hint from URL query
        if (\Mercurio\Utils\URL::getTarget()) return \Mercurio\Utils\URL::getTarget();

        return NULL;
    }

    /**
     * Load a channel from database into instance
     * @param string|int $hint Channel identifier either string handle or integer id
     * @param callable $callback Callback function to manipulate channel data without loading class
     * function (array $info) :
     * @param callable $fallback Callback function to execute in case of no channel found
     * function () :
     * @return array|false Channel info, false on no user found
     */
    public function get($hint = false, callable $callback = NULL, callable $fallback = NULL) {
        if (!$hint) $hint = $this->findHint();
        $channel = $this->db()->get(DB_CHANNELS, '*', [
            'OR' => [
                'id' => $hint,
                'handle' => $hint
            ]
        ]);
        // Return data or load instance
        if ($channel) {
            if ($callback !== NULL) return $callback($channel);

            $this->info = $channel;
            return $this->info;
        } elseif ($fallback !== NULL) {
            return $fallback();
        }
    }

    /**
     * Update channel properties
     * @param array $properties Associative array of channel properties
     */
    public function set(array $properties) {
        $this->get(false, function ($channel) use (&$properties) {
            $this->db()->update(DB_CHANNELS,
                $properties,
                $channel['id']
            );
        });
    }

    /**
     * Deletes channel from database and it's associated data
     */
    public function unset() {
        $this->get(false, function ($channel) {
            $this->db()->delete(DB_CHANNELS, ['id' => $channel['id']]);
            $this->unsetMeta();
        });
    }

    /**
     * Read channel meta
     * @param string $meta Name of meta field or array of, leave blank to get all meta fields
     * @param string $grouping Name of meta group
     * @return bool|mixed|array
     */
    public function getMeta($meta = '', string $grouping = '') {
        return $this->get(false, function($channel) use (&$meta, $grouping) {
            return $this->dbGetMeta($channel['id'], $meta, $grouping);
        });
    }

    /**
     * Set and update media meta
     * @param array $meta Associative array of meta names and values
     * @param string $grouping Name of meta group
     */
    public function setMeta(array $meta, string $grouping = '') {
        $this->get(false, function($channel) use (&$meta, $grouping) {
            $this->dbSetMeta($channel['id'], $meta, $grouping);
        });
    }

    /**
     * Deletes media meta from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     * @param string $grouping Name of meta group
     */
    public function unsetMeta($meta = '', string $grouping = '') {
        $this->get(false, function ($channel) use (&$meta, $grouping) {
            $this->dbUnsetMeta($channel['id'], $meta, $grouping);
        });
    }

    /**
     * Set a new channel and load into instance 
     * @param array $properties Associative array of channel properties
     * @param array $required $properties array keys of required content
     * @return array|false Channel info, false on no user 
     * @throws Exception
     */
    public function new(array $properties, array $required = []) {
        // Ensure media properties are valid
        \Mercurio\Utils\System::emptyField($required, $properties);
        $properties = \Mercurio\Utils\System::property($properties);

        // Make channel
        $this->db()->insert(DB_CHANNELS, $properties);
        $this->get($properties['id']);
        return $this->info;
    }

    /**
     * Get channel numeric id
     * @param bool $string Return id as string
     * @return int|string Channel ID
     */
    public function getID(bool $string = false) {
        return $this->get(false, function($channel) use (&$string) {
            if ($string) return (string) $channel['id'];
            return (int) $channel['id'];
        });
    }

    /**
     * Get absolute link to channel
     * @param string $page Channels page
     * @param string $action Optional channel action
     * @return string URL
     */
    public function getLink(string $action = '') {
        return $this->get(false, function($channel) use (&$page, &$action) {
            return (string) \Mercurio\Utils\URL::getLink($page, $channel['handle'], $action);
        });
    }

    /**
     * Get channel author
     * @return object User object instance loaded with channel author
     */
    public function getAuthor() {
        return $this->get(false, function($channel) {
            $author = new \Mercurio\App\User;
            $author->get($channel['author']);
            return $author;
        });
    }

    /**
     * Get channel media elements
     * @param callable $callback Callback function to manipulate media elements
     * function (array $media) :
     * @return array Array of media elements id
     */
    public function getMedias(callable $callback = NULL) {
        return $this->get(false, function($channel) use ($callback) {
            $media = $this->db()->select(DB_MEDIA, '*', [
                'channel' => $channel['id']
            ]);
            if ($callback !== NULL) return $callback($media);
            return $media;
        });
    }

}
