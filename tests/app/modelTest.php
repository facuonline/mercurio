<?php

namespace Mercurio\Test;

include 'modelMockup.php';

class ModelTest extends \PHPUnit\Framework\TestCase {

    public function testMockupHasTableDefined() {
        $model = new \Mercurio\Test\ModelMockup(new \Mercurio\App\Database);
        $this->assertEquals(DB_USERS, $model->getTable());
    }

    public function testGetReturnsClosureDefined() {
        $model = new \Mercurio\Test\ModelMockup(new \Mercurio\App\Database);
        $value = $model->get('vito', function($user) {
            return (int) $user['id'];
        });

        $this->assertIsInt($value);
    }

    public function testGetReturnsBoolOnNoClosure() {
        $model = new \Mercurio\Test\ModelMockup(new \Mercurio\App\Database);
        $value = $model->get('vito');
        
        $this->assertTrue($value);
    }

}
