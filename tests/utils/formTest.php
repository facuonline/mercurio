<?php
namespace Mercurio;
class FormTest extends \PHPUnit\Framework\TestCase {

    public function testConstructorAddsSPAMFilter() {
        $form = new \Mercurio\Utils\Form;

        $this->assertIsObject($form['url_website_pot']);
        $this->assertIsObject($form['name_title_pot']);
        $this->assertIsObject($form['form_stamp']);
    }

}
