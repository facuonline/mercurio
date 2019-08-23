<?php

namespace Mercurio\Utils;

/**
 * Form creator
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
     * @param object $nette Dependency injected \Nette\Forms\Form class
     */
    public function __construct(\Nette\Forms\Form $nette) {
        $this->form = $nette;
    }

    /**
     * Add hidden honeypot fields to protect against automated SPAM
     * @param string $key Your own personalized key to avoid spambots from bypassing the filter
     * @return object \Nette\Forms\Form
     */
    public function addSpamProtection(string $key = '') {
        // Add APP url to harden Mercurio specific spam submission
        if (empty($key)) $key = '_'.getenv('APP_URL');

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
