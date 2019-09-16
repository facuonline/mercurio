<?php

namespace Mercurio\App;

/**
 * Channels model
 * @package Mercurio
 * @subpackage App classes
 */
class Channel extends \Mercurio\App\Model {

    public $data = [
        'id' => NULL, 
        'handle' => NULL, 
        'author' => NULL,
        'channel' => NULL,
        'body' => NULL,
        'stamp' => NULL
    ];

    public $db_table = DB_CHANNELS;

    /**
     * Prepare channels to be selected by author
     * @param Mercurio\App\User $author Loaded instance of class `Mercurio\App\User`
     */
    public function selectByAuthor(Mercurio\App\User $author) {
        if (!$author->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->get_by = ['author' => $author->id];
    }

    /**
     * Prepare channels to be selected by their parent channel
     * @param Mercurio\App\Channel $channel Loaded instance of class `Mercurio\App\Channel`
     */
    public function selectByChannel(Mercurio\App\Channel $channel) {
        if (!$channel->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->get_by = ['channel' => $channel->id];
    }

    /**
     * Prepare channel to be selected by numeric ID
     * @param int $id Numeric ID
     */
    public function getById(int $id) {
        $this->get_by = ['id' => $id];
    }

    /**
     * Prepare channel to be selected by handle
     * @param string $handle Alphanumeric handle
     */
    public function getByHandle(string $handle) {
        $this->get_by = ['handle' => $handle];
    }

    /**
     * Return channel numeric ID
     * @param bool $as_string Returns the ID as a string
     * @return int|string
     */
    public function getId(bool $as_string = false) {
        if ($as_string) return (string) $this->data['id'];
        return (int) $this->data['id'];
    }

    /**
     * Return channel alphanumeric handle
     * @param bool $hashtag Returns the handle with the symbol '#' prepended
     * @return string
     */
    public function getHandle(bool $arroba) {
        if ($arroba) return '#' . $this->data['handle'];
        return $this->data['handle'];
    }

    /**
     * Update channel alphanumeric handle
     * @param string $handle New user handle
     */
    public function setHandle(string $handle) {
        $this->data['handle'] = $handle;
    }

    /**
     * Return channel author ID
     * @return int
     */
    public function getAuthor() {
        return $this->data['author'];
    }

    /**
     * Update channel author
     * @param Mercurio\App\User $author Loaded instance of class `Mercurio\App\User`
     */
    public function setAuthor(\Mercurio\App\User $author) {
        if (!$author->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->data['author'] = $author->id;
    }

    /**
     * Return channel parent channel ID
     * @return int
     */
    public function getChannel() {
        return $this->data['channel'];
    }

    /**
     * Update channel parent channel
     * @param Mercurio\App\Channel $channel Loaded instance of class `Mercurio\App\Channel`
     */
    public function setChannel(\Mercurio\App\Channel $channel) {
        if (!$channel->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->data['channel'] = $channel->id;
    }

    /**
     * Return channel message body
     * @return string
     */
    public function getBody() {
        return $this->data['body'];
    }

    /**
     * Update channel message body
     * @param string $body
     */
    public function setBody(string $body) {
        $this->data['body'] = $body;
    }

}
