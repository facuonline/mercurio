<?php
/**
 * Messages class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * Messages are normal media grouped under special message type channels
 * @see Mercurio\App\Channels class
 */
namespace Mercurio\App;
class Messages extends \Mercurio\App\Channel {

    protected function findHint() {
        if ($this->info) return $this->info['id'];
        // Get media hint from URL query
        if (\Mercurio\Utils\URL::getPage() == 'messages'
        && \Mercurio\Utils\URL::getTarget()) return \Mercurio\Utils\URL::getTarget();

        return NULL;
    }

    /**
     * Get all media in the messages channel
     * @return array
     */
    public function getMessages() {
        return $this->get(false, function ($channel) {
            return $this->db()->select('mro_media', '*', ['channel' => $channel['id']]);
        });
    }

    /**
     * Get id of all users in the messages channel
     * @param callable $callback Callback function to manipulate user ids
     * function (array $users) :
     * @return array
     */
    public function getUsers(callable $callback = NULL) {
        return $this->get(false, function($channel) use (&$callback) {
            $users = $this->db()->select('mro_meta', ['value'], [
                'name' => 'message_channel_user',
                'target' => $channel['id']
            ])[0];

            if ($callback !== NULL) return $callback($users);
            return $users;
        });
    }

    /**
     * Adds an user to the channel
     * @param int|string $hint User id or handle
     */
    public function setUser($hint) {
        $user = new \Mercurio\App\User;
        if ($user->get($hint)) $hint = $user->getID();

        $this->setMeta([
            'message_channel_user' => $hint
        ]);
    }

    /**
     * Removes an user from the channel
     * @param int|string $hint User id or handle
     */
    public function unsetUser($hint) {
        $user = new \Mercurio\App\User;
        if ($user->get($hint)) $hint = $user->getID();

        $this->unsetMeta([
            'message_channel_user' => $hint
        ]);
    }

    /**
     * Get absolute link to message channel
     * @return string
     */
    public function getLink() {
        return $this->get(false, function($channel) use (&$action) {
            return (string) \Mercurio\Utils\URL::getLink('messages', $channel['id'], $action);
        });
    }

}
