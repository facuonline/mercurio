<?php

namespace Mercurio\Utils;

/**
 * Form creator
 * 
 * Extended from  nette/forms
 * @see https://doc.nette.org/en/3.0/forms
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Form extends \Nette\Forms\Form {

    /**
     * Build new form with auto anti spam
     */
    public function __construct() {
        parent::__construct();
        $this->addHidden('url_website_pot')
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->addHidden('name_title_pot')
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->addHidden('form_stamp')
            ->setDefaultValue(time() + 3)
            ->addRule(\Nette\Forms\Form::MIN, 'Please try again.', time());
    }

}
