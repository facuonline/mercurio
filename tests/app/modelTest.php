<?php

namespace Mercurio\Test;

include 'modelMockup.php';

class ModelTest extends \PHPUnit\Framework\TestCase {

    protected $model;

    protected function setUp(): void {
        $this->model = new \Mercurio\Test\ModelMockup(new \Mercurio\App\Database);
    }

    public function testMockupHasTableDefined() {
        $this->assertEquals(DB_USERS, $this->model->getTable());
    }

    public function testGetReturnsClosureDefined() {
        $test = $this->model->get(['handle' => 'vito'], function($user) {
            return (int) $user['id'];
        });

        $this->assertIsInt($test);
    }

    public function testGetReturnsBoolOnNoClosure() {
        $test = $this->model->get(['handle' => 'vito']);
        
        $this->assertTrue($test);
    }

}
