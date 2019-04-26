<?php
/**
 * MroDB class
 * @package Mercurio
 * @subpackage Included classes
 * 
 * All of the following are determined by environmental variables
 * @var string $host Server address
 * @var string $user Username of database
 * @var string $pass User password
 * @var string $name Database name in server 
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
    private $host, $user, $pass, $name;
    protected $PDO;

    public function __construct() {
        $this->host = getenv('DB_HOST');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');
        $this->name = getenv('DB_NAME');
        $this->conn();
    }

    /**
     * Stablish connection with db
     * @throws Exception on error with db connection
     */
    protected function conn() {
        $dsn = "mysql:host=$this->host;dbname=$this->name;charset=utf8mb4";
		$options = [
	    	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	   		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		try {
			$this->PDO = new PDO($dsn, $this->user, $this->pass, $options);
		} catch (\PDOException $error) {
			throw new \PDOException($error->getMessage(), (int)$error->getCode());
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
     * @param mixed $statement SQL statement
     * @param array $placeholders SQL statement placeholders
     * @return object PDO object
     */
    protected function pdo($statement, array $placeholders = []) {
        $query = $this->PDO->prepare($statement);
        $query->execute($placeholders);
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
            ->from('mro_config')
            ->where(field('name')->eq($config))
            ->compile();
        $result = $this->pdo(
            $query->sql(),
            $query->params()
        )
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
                ->update('mro_config', [
                        'value' => $value
                    ])
                ->where(field('name')->eq($name))
                ->compile();
        // insert
        } else {
            $query = $this->sql()
                ->insert('mro_config', [
                    'name' => $name,
                    'value' => $value
                ])
                ->compile();
        }
        return $this->pdo(
            $query->sql()
        );
    }
}