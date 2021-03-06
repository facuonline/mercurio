<?php

namespace Mercurio\App;

/**
 * Database middleware
 * @package Mercurio
 * @subpackage App classes
 */
class Database {

    /**
     * SQL query builder Medoo
     * @see https://github.com/catfan/medoo
     */
    protected $sql;

    /**
     * Instance of last inserted object
     */
    protected $last_insert;

    /**
     * @param array $parameters Database connection arguments,
     * use `Mercurio\App::getDatabase()` if you have configured the database with `App`
     * @see https://medoo.in/api/new
     */
    public function __construct(array $parameters) {
        $this->sql = new \Medoo\Medoo($parameters);
    }

    /**
     * Obtain SQL builder instance
     * @return object
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * Insert new record in database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     */
    public function insert(object $object) {
        // System properties
        $object->data['id'] = \Mercurio\Utils\ID::new();
        $object->data['stamp'] = time();

        $object->id = $object->data['id'];
        $this->last_insert = $object;

        return $this->sql->insert($object->db_table, $object->data);
    }

    /**
     * Select one record from database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object|null Loaded instance of entry object, NULL on failure
     */
    public function get(object $object) {
        $data = $this->sql->get($object->db_table, '*', $object->get_by);
        if (!$data) return NULL;

        // Reassign data to object and return it
        $class = \get_class($object);
        $object = new $class;
        $object->data = $data;
        $object->id = $object->data['id'];

        return $object;
    }

    /**
     * Select multiple records from database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return array|null Array with loaded instances of entry object, NULL on failure
     */
    public function select(object $object) {
        $results = $this->sql->select($object->db_table, '*', $object->get_by);
        if (!$results) return NULL;

        // Reassign data to objects and return array
        $objects = [];
        $class = \get_class($object);
        foreach ($results as $key => $data) {
            $objects[$key] = new $class;
            $objects[$key]->data = $data;
            $objects[$key]->id = $data['id'];
        }

        return $objects;
    }

    /**
     * Update record in database
     * @param object $object Loaded instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     * @throws \Mercurio\Exception\Usage
     */
    public function update(object $object) {
        // Instances not loaded
        if (!$object->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");
        // System properties
        unset($object->data['id']);
        unset($object->data['stamp']);

        return $this->sql->update($object->db_table, $object->data, ['id' => $object->id]);
    }

    /**
     * Delete record in database
     * @param object $object Instance of a `Mercurio\App\*` class
     * @return object PDO Statement
     * @throws \Mercurio\Exception\Usage
     */
    public function delete(object $object) {
        // Instances not loaded
        if (!$object->id) throw new \Mercurio\Exception\Usage("Passed object must be a loaded instance with valid database data.");

        return $this->sql->delete($object->db_table, ['id' => $object->id]);
    }

    /**
     * Return object of latest database insertion using this database instance
     * @return object
     */
    public function lastInsert() {
        return $this->last_insert;
    }

    /**
     * Set database tables and columns \
     * Run to populate your database with required App tables
     * @param string $engine Type of engine for tables
     */
    public  function setTables(string $engine = 'InnoDB') {
        // App persistent configurations
        $this->sql->create(DB_CONF, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY KEY'
            ],
            'name' => [
                'VARCHAR(30)',
                'NOT NULL',
                'UNIQUE'
            ],
            'value' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ], [
            'ENGINE' => $engine,
        ]);

        // App entities meta properties
        $this->sql->create(DB_META, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY KEY'
            ],
            'name' => [
                'VARCHAR(30)',
                'NOT NULL'
            ],
            'grouping' => [
                'VARCHAR(30)',
            ],
            'value' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'target' => [
                'BIGINT',
                'NOT NULL'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ], [
            'ENGINE' => $engine,
        ]);

        // App users
        $this->sql->create(DB_USERS, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
                'UNIQUE'
            ],
            'email' => [
                'VARCHAR(255)',
                'UNIQUE'
            ],
            'nickname' => [
                'VARCHAR(255)'
            ],
            'password' => [
                'VARCHAR(255)',
                'NOT NULL'
            ],
            'img' => [
                'VARCHAR(255)'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ]
        ], [
            'ENGINE' => $engine,
        ]);
        
        // App channels
        $this->sql->create(DB_CHANNELS, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY KEY'
            ],
            'handle' => [
                'VARCHAR(26)',
                'UNIQUE'
            ],
            'author' => [
                'BIGINT',
            ],
            'channel' => [
                'BIGINT'
            ],
            'body' => [
                'VARCHAR(4000)'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ],
            'FULLTEXT KEY (<body>)'
        ], [
            'ENGINE' => $engine,
        ]);
        
        // App medias
        $this->sql->create(DB_MEDIA, [
            'id' => [
                'BIGINT',
                'NOT NULL',
                'PRIMARY KEY'
            ],
            'author' => [
                'BIGINT',
            ],
            'channel' => [
                'BIGINT',
            ],
            'body' => [
                'TEXT'
            ],
            'stamp' => [
                'BIGINT',
                'NOT NULL'
            ],
            'FULLTEXT KEY (<body>)'
        ], [
            'ENGINE' => $engine,
        ]);
        
    }

}
