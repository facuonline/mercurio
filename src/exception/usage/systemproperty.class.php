<?php
namespace Mercurio\Exception\Usage;
/**
 * These types of exceptions are triggered on third party developers failure \
 * e.g When trying to modify a system set property
 * @package Mercurio
 * @subpackage Extended Exception classes
 */
class SystemProperty extends \Mercurio\Exception\Model {
    /**
     * @param string $property Name of system property
     */
    public function __construct(string $property) {
        $message = "Property <strong>'$property'</strong> can't be manipulated by other than the system.";
        return parent::__construct($message);
    }
}