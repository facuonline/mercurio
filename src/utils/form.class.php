<?php

namespace Mercurio\Utils;

/**
 * Form creator \
 * Non static Util
 * 
 * Extended from nette/forms \
 * @see https://doc.nette.org/en/3.0/forms
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Form {

    /**
     * Instance of \Nette\Forms\Form
     */
    public $form;

    /**
     * Start new form object
     * @param string $key Unique personalized key to avoid spambots from bypassing the filter
     */
    public function __construct(string $key = '') {
        $this->form = new \Nette\Forms\Form;

        $this->form->onSuccess[] = $this->form->isSuccess();
        $this->addSpamProtection($key);
    }

    /**
     * Add hidden honeypot fields to protect against automated SPAM
     * @param string $key Your own personalized key to avoid spambots from bypassing the filter
     * @return object \Nette\Forms\Form
     */
    public function addSpamProtection(string $key = '') {
        $this->form->addHidden('url_website_pot'.$key)
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->form->addHidden('name_title_pot'.$key)
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->form->addHidden('form_stamp'.$key)
            ->setDefaultValue(time() + 3)
            ->addRule(\Nette\Forms\Form::MIN, 'Please try again.', time());
    }

    /**
     * Obtain Form object instance
     * @return object \Nette\Forms\Form
     */
    public function getForm() {
        return $this->form;
    }

}
