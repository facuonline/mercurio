<?php
/**
 * Form class
 * @package Mercurio
 * @subpackage Utilitary classes
 * 
 * Extended \Nette\Forms
 * @see https://doc.nette.org/en/3.0/forms
 */
namespace Mercurio\Utils;
class Form {

    public $form;

    /**
     * Build new form with auto anti spam
     */
    public function __construct() {
        $this->form = new \Nette\Forms\Form;
        $this->form->addHidden('url-website')
            ->addRule(Form::BLANK);
        return $this->$form;
    }

}