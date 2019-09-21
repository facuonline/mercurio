<?php

namespace Mercurio\App;

/**
 * Medias model
 * @package Mercurio
 * @subpackage App classes
 */
class Media extends \Mercurio\App\Model {

    public $data = [
        'id' => NULL, 
        'author' => NULL, 
        'channel' => NULL,
        'body' => NULL,
        'stamp' => NULL
    ];

    public $db_table = DB_MEDIA;

    /**
     * Prepare medias to be selected by author
     * @param \Mercurio\App\User $author Loaded instance of class `Mercurio\App\User`
     */
    public function selectByAuthor(\Mercurio\App\User $author) {
        if (!$author->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->get_by = ['author' => $author->id];
    }

    /**
     * Prepare medias to be selected by thei channel
     * @param \Mercurio\App\Channel $channel Loaded instance of class `Mercurio\App\Channel`
     */
    public function selectByChannel(\Mercurio\App\Channel $channel) {
        if (!$channel->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->get_by = ['channel' => $channel->id];
    }

    /**
     * Prepare media to be selected by numeric ID
     * @param int $id Numeric ID
     */
    public function getById(int $id) {
        $this->get_by = ['id' => $id];
    }

    /**
     * Return media numeric ID
     * @param bool $as_string Returns the ID as a string
     * @return int|string
     */
    public function getId(bool $as_string = false) {
        if ($as_string) return (string) $this->data['id'];
        return (int) $this->data['id'];
    }

    /**
     * Return media author ID
     * @return int
     */
    public function getAuthor() {
        return $this->data['author'];
    }

    /**
     * Update media author
     * @param \Mercurio\App\User $author Loaded instance of class `Mercurio\App\User`
     * @return object Self instance
     */
    public function setAuthor(\Mercurio\App\User $author) {
        if (!$author->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->data['author'] = $author->id;
        return $this;
    }

    /**
     * Return media channel ID
     * @return int
     */
    public function getChannel() {
        return $this->data['channel'];
    }

    /**
     * Update media channel
     * @param \Mercurio\App\Channel $channel Loaded instance of class `Mercurio\App\Channel`
     * @return object Self instance
     */
    public function setChannel(\Mercurio\App\Channel $channel) {
        if (!$channel->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        $this->data['channel'] = $channel->id;
        return $this;
    }

    /**
     * Return media content body
     * @return string
     */
    public function getBody() {
        return $this->data['body'];
    }

    /**
     * Update media content body
     * @param string $body
     * @return object Self instance
     */
    public function setBody(string $body) {
        $this->data['body'] = $body;
        return $this;
    }

}
