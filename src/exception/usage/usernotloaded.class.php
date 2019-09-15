<?php
namespace Mercurio\Exception\Usage;
/**
 * These types of exceptions are triggered on third party developers failure \
 * e.g When not calling User->get before using another method
 * @package Mercurio
 * @subpackage Extended Exceptions classes
 */
class UserNotLoaded extends \Mercurio\Exception\Model {
    public function __construct() {
        $message = "Class method can only be called on instances loaded with an existing user data. Use <strong>\Mercurio\App\Database->get()</strong> to load an user into instance.";
        return parent::__construct($message);
    }
}