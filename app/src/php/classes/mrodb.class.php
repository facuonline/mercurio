<?php
/**
 * MroDB class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * @var object $PDO PDO instance with db connection
 */

/**
 * Latitude functions
 * Latitude Query Buider helps developers to build better, safer SQL queries
 * and makes easier migrating to other db models than MySQL
 */
use function Latitude\QueryBuilder\fn;
use function Latitude\QueryBuilder\param;
use function Latitude\QueryBuilder\paramAll;
use function Latitude\QueryBuilder\order;
use function Latitude\QueryBuilder\identify;
use function Latitude\QueryBuilder\identifyAll;
use function Latitude\QueryBuilder\listing;
use function Latitude\QueryBuilder\field;
use function Latitude\QueryBuilder\search;
use function Latitude\QueryBuilder\on;
use function Latitude\QueryBuilder\group;
use function Latitude\QueryBuilder\express;
use function Latitude\QueryBuilder\criteria;
use function Latitude\QueryBuilder\literal;
use function Latitude\QueryBuilder\alias;

class MroDB {
    protected $PDO;

    public function __construct() {
        $this->conn();
    }

    public function __sleep() {
        return [];
    }

    public function __wakeup() {
        $this->PDO;
    }

    /**
     * Stablish connection with db
     * @throws Exception on error with db connection
     */
    protected function conn() {
        // get db envs
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $name = getenv('DB_NAME');
        // config pdo
        $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
		$options = [
	    	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	   		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
        ];
        // make it so
		try {
			$this->PDO = new PDO($dsn, $user, $pass, $options);
		} catch (\PDOException $error) {
			throw new \PDOException($error->getMessage(), $error->getCode());
	    	die();
		}
    }

    /**
     * Latitude Query Buider helps developers to build better, safer SQL queries
     * and makes easier migrating to other db models than MySQL
     * @return object Instance of Latitude with MySQL engine
     */
    protected function sql() {
        $engine = new Latitude\QueryBuilder\Engine\MySqlEngine;
		return new Latitude\QueryBuilder\QueryFactory($engine);
    }

    /**
     * DRY helper function to perform PDO queries with less code
     * @param object Latitude query object
     * @return object PDO object
     */
    protected function pdo(Latitude\QueryBuilder\Query $query) {
        if ($query->params()) {
            $params = $query->params();
        } else {
            $params = NULL;
        }
        $query = $this->PDO->prepare($query->sql());
        $query->execute($params);
        return $query;
    }

    /**
     * Select configuration from mro_config
     * @param string $config Configuration name
     * @return mixed Configuration value or bool
     */
    public function getConfig($config) {
        $query = $this->sql()
            ->select('value')
            ->from('mro_configs')
            ->where(field('name')->eq($config))
            ->compile();
        $result = $this->pdo($query)
            ->fetch()['value'];
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Update or insert configuration
     * @param string $name Config name
     * @param mixed $value Config value
     * @return mixed
     */
    public function setConfig($name, $value) {
        // update
        if ($this->getConfig($name)) {
            $query = $this->sql()
                ->update('mro_configs', [
                        'value' => $value
                    ])
                ->where(field('name')->eq($name))
                ->compile();
        // insert
        } else {
            $query = $this->sql()
                ->insert('mro_configs', [
                        'name' => $name,
                        'value' => $value
                    ])
                ->compile();
        }
        return $this->pdo($query);
    }
}