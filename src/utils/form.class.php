<?php

namespace Mercurio\Utils;

/**
 * Form creator \
 * Non static Util
 * 
 * Extended from `nette/forms`
 * @see https://doc.nette.org/en/3.0/forms
 * @package Mercurio
 * @subpackage Utilitary classes
 */
class Form {

    /**
     * Instance of `\Nette\Forms\Form`
     */
    public $form;

    /**
     * Start new form object
     * @param string $key Unique personalized key to avoid spambots from bypassing the filter
     */
    public function __construct(string $key = '') {
        $this->form = new \Nette\Forms\Form;

        $this->form->onSuccess[] = $this->form->isSuccess();
        $this->addProtection($key);
    }

    /**
     * Add hidden honeypot fields to protect against automated SPAM
     * @param string $key Your own personalized key to avoid spambots from bypassing the filter
     */
    public function addProtection(string $key = '') {
        // Anti CSRF
        \Mercurio\Utils\Session::set('Csrf', \Mercurio\App::randomKey());
        $this->form->addHidden('_csrftoken')
            ->addRule(\Nette\Forms\Form::EQUAL, \Mercurio\Utils\Session::get('Csrf'));
        // Anti SPAM
        $this->form->addHidden('url_website_pot'.$key)
            ->addRule(\Nette\Forms\Form::BLANK);
        $this->form->addHidden('name_title_pot'.$key)
            ->addRule(\Nette\Forms\Form::BLANK);
        // Time check
        $this->form->addHidden('form_stamp'.$key)
            ->setDefaultValue(time() + 3)
            ->addRule(\Nette\Forms\Form::MIN, 'Please try again.', time());
    }

    /**
     * Build a basic login form ready to match with `\Mercurio\App\User` login methods
     * @param string $credential_label Label for 'credential' form field
     * @param string $credential_placeholder Placeholder for 'credential' input
     * @param string $password_label Label for 'password' form field
     * @param string $password_placeholder Placeholder for 'password' input
     * @param string $submit Value of submit button
     * @return \Nette\Form\Form 
     */
    public function login(string $credential_label = 'Username or password:', string $credential_placeholder = 'john@example.com', $password_label = 'Password:', $password_placeholder = 'My password', $submit = 'login') {
        $this->form->addText('credential', $credential_label)
            ->addHtmlAttribute('placeholder', $credential_placeholder)
            ->setRequired(true);
        $this->form->addPassword('password', $password_label)
            ->addHtmlAttribute('placeholder', $password_placeholder)
            ->setRequired(true);
        $this->form->addSubmit($submit);
        
        return $this->getForm();
    }

    /**
     * Obtain Form object instance
     * @return \Nette\Forms\Form
     */
    public function getForm() {
        return $this->form;
    }

}
