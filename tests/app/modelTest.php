<?php

namespace Mercurio\Test;

class ModelTest extends \PHPUnit\Framework\TestCase {

    public function testGetReturnsClosureDefined() {
        $model = new \Mercurio\App\Model(new \Mercurio\App\Database);
        $value = $model->get('vito', function($user) {
            return (int) $user['id'];
        });

        $this->assertIsInt($value);
    }

    public function testGetReturnsBoolOnNoClosure() {
        $model = new \Mercurio\App\Model(new \Mercurio\App\Database);
        $value = $model->get('vito');
        
        $this->assertIsBool($value);
    }

}
