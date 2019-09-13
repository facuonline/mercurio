<?php
namespace Mercurio\Test;
class FormTest extends \PHPUnit\Framework\TestCase {

    public function testSetSpamProtection() {
        $form = new \Mercurio\Utils\Form('test');
        $form = $form->getForm();

        $this->assertIsObject($form['url_website_pottest']);
        $this->assertIsObject($form['name_title_pottest']);
        $this->assertIsObject($form['form_stamptest']);
    }

}
