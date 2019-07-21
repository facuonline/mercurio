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
        $channel = $this->db()->get('mro_channels', '*', [
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
            $this->db()->update('mro_channels',
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
            $this->db()->delete('mro_channels', ['id' => $channel['id']]);
            $this->unsetMeta();
        });
    }

    /**
     * Read channel meta
     * @param string $meta Name of meta field or array of, leave blank to get all meta fields
     * @return bool|mixed|array
     */
    public function getMeta($meta = '') {
        return $this->get(false, function($channel) use (&$meta) {
            // Get all meta
            if (empty($meta)) return $this->db()->select('mro_meta', '*', [
                'target' => $channel['id']
            ])[0];
            // Get specific meta
            // Get from array
            if (is_array($meta)) return $this->db()->select('mro_meta', [
                'value'
            ], [
                'target' => $channel['id'],
                'name' => $meta
            ]);
            // Get meta row
            return $this->db()->get('mro_meta', [
                'value'
            ], [
                'target' => $channel['id'],
                'name' => $meta
            ])['value'];
        });
    }

    /**
     * Set and update channel meta
     * @param array $meta Associative array of meta names and values
     */
    public function setMeta(array $meta) {
        foreach ($meta as $key => $value) {
            if (!is_string($key)) throw new \Mercurio\Exception\Usage\StringKeysRequired('setMeta');

            $this->get(false, function($channel) use ($key, $value) {
            if ($this->getMeta($key)) {
                $this->db()->update('mro_meta', [
                    'value' => $value
                ], [
                    'target' => $channel['id'],
                    'name' => $key
                ]);
            } else {
                $this->db()->insert('mro_meta', [
                    'id' => \Mercurio\Utils\ID::new(),
                    'name' => $key,
                    'value' => $value,
                    'target' => $channel['id'],
                    'stamp' => time() 
                ]);
            }
            });
        }
    }

    /**
     * Deletes channel meta from database
     * @param string|array $meta Name of meta field or array of, leave blank to delete all meta
     */
    public function unsetMeta($meta = '') {
        $this->get(false, function ($channel) use (&$meta) {
            // Delete all meta
            if (empty($meta)) $this->db()->delete('mro_meta', ['target' => $channel['id']]);
            // Delete specific meta
            $this->db()->delete('mro_meta', [
                'target' => $channel['id'],
                'name' => $meta
            ]);
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
        $properties = \Mercurio\Utils\System::property(['id', 'stamp'], $properties);

        // Make channel
        $this->db()->insert('mro_channels', $properties);
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
     * @param string $action Optional channel action
     * @return string URL
     */
    public function getLink(string $action = '') {
        return $this->get(false, function($channel) use (&$action) {
            return (string) \Mercurio\Utils\URL::getLink('channel', $channel['id'], $action);
        });
    }

}
