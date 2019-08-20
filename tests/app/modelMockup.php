<?php

namespace Mercurio\Test;

class ModelMockup extends \Mercurio\App\Model {
    protected $DBTABLE = DB_USERS;

    public function getTable() {
        return $this->DBTABLE;
    }
}