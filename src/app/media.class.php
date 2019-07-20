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
        if (\Mercurio\Utils\URL::getPage() !== 'media'
        && \Mercurio\Utils\URL::getTarget()) return \Mercurio\Utils\URL::getTarget();

        return NULL;
    }

    /**
     * Load a media record from database into instance
     * @param int $hint Media id
     * @param callback $callback Callback function to manipulate media without loading class
     * @param callback $fallback Callback function to execute in case of no media found
     * @return array|false Media info, false on no media found
     */
    public function get($hint = false, callable $callback = NULL, callable $fallback = NULL) {
        if (!$hint) $hint = $this->findHint();
        $media = $this->db()->select('mro_media', '*', [
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
            $this->db()->update('mro_media',
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
            $this->db()->delete('mro_users', ['id' => $media['id']]);
            $this->unsetMeta();
        });
    }

    /**
     * Read media meta
     * @param string $meta Name of meta field or array of, leave blank to get all meta fields
     * @return bool|mixed|array
     */
    public function getMeta($meta = '') {
        return $this->get(false, function($media) use (&$meta) {
            // Get all meta
            if (empty($meta)) return $this->db()->select('mro_meta', '*', [
                'target' => $media['id']
            ])[0];
            // Get specific meta
            // Get from array
            if (is_array($meta)) return $this->db()->select('mro_meta', [
                'value'
            ], [
                'target' => $media['id'],
                'name' => $meta
            ]);
            // Get meta row
            return $this->db()->get('mro_meta', [
                'value'
            ], [
                'target' => $media['id'],
                'name' => $meta
            ])['value'];
        });
    }

    /**
     * Set and update media meta
     * @param array $meta Associative array of meta names and values
     */
    public function setMeta(array $meta) {
        foreach ($meta as $key => $value) {
            if (!is_string($key)) throw new \Mercurio\Exception\Usage\StringKeysRequired('setMeta');

            $this->get(false, function($media) use ($key, $value) {
            if ($this->getMeta($key)) {
                $this->db()->update('mro_meta', [
                    'value' => $value
                ], [
                    'target' => $media['id'],
                    'name' => $key
                ]);
            } else {
                $this->db()->insert('mro_meta', [
                    'id' => \Mercurio\Utils\ID::new(),
                    'name' => $key,
                    'value' => $value,
                    'target' => $media['id'],
                    'stamp' => time() 
                ]);
            }
            });
        }
    }

    /**
     * Deletes media meta from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     */
    public function unsetMeta($meta = '') {
        $this->get(false, function ($media) use (&$meta) {
            // Delete all meta
            if (empty($meta)) $this->db()->delete('mro_meta', ['target' => $media['id']]);
            // Delete specific meta
            $this->db()->delete('mro_meta', [
                'target' => $user['id'],
                'name' => $meta
            ]);
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
        \Mercurio\Utils\System::emptyField($required, $properties);
        $properties = \Mercurio\Utils\System::property(['id', 'stamp'], $properties);

        // Make media
        $this->db()->insert('mro_media', $properties);
        $this->get($properties['id']);
        return $this->info;
    }

    /**
     * Get media numeric id
     * @param bool $string Return handle as string
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
     * @param string $action Optional media action
     * @return string URL
     */
    public function getLink(string $action = '') {
        return $this->get(false, function($media) use (&$action) {
            return (string) \Mercurio\Utils\URL::getLink('media', $media['id'], $action);
        });
    }

    /**
     * Get media channel info
     * @param callback $callback Callback function to manipulate channel data
     * @return array
     */
    public function getChannel(callable $callback = NULL) {
        $this->get(false, function($media) use (&$callback) {
            $channel = $this->db()->select('mro_channels', '*', [
                'id' => $media['channel']
            ]);
            if ($callback !== NULL) return $callback($channel);
            return $channel;
        });
    }

}
