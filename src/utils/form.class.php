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
class Form extends \Nette\Forms\Form {

    public $form;

    /**
     * Build new form with auto anti spam
     */
    public function __construct() {
        $this->form = $this;
        $this->form->addHidden('url_website')
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->form->addHidden('form_stamp')
            ->setDefaultValue(time() + 3)
            ->addRule(\Nette\Forms\Form::MIN, 'Please try again.', time());
        return $this->form;
    }

}
