<?php
namespace Mercurio\Test;
class FormTest extends \PHPUnit\Framework\TestCase {

    public function testSetSpamProtection() {
        $form = new \Mercurio\Utils\Form(new \Nette\Forms\Form);
        $form->setSpamProtection('test');
        $test = $form->getForm();

        $this->assertIsObject($test['url_website_pot_test']);
        $this->assertIsObject($test['name_title_pot_test']);
        $this->assertIsObject($test['form_stamp_test']);
    }

}
