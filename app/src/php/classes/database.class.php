<?php
/**
 * DB class
 * @package Mercurio
 * @subpackage Included classes
 */

namespace Mercurio;
class Database {
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
	    	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
	   		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES => false,
        ];
        // make it so
		try {
			return new \PDO($dsn, $user, $pass, $options);
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
        $engine = new \Latitude\QueryBuilder\Engine\MySqlEngine;
		return new \Latitude\QueryBuilder\QueryFactory($engine);
    }

    /**
     * DRY helper function to perform PDO queries with less code
     * @param object Latitude query object
     * @return object PDO object
     */
    protected function pdo(\Latitude\QueryBuilder\Query $query) {
        if ($query->params()) {
            $params = $query->params();
        } else {
            $params = NULL;
        }
        $PDO = $this->conn();
        $query = $PDO->prepare($query->sql());
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
            ->where(\Latitude\QueryBuilder\field('name')->eq($config))
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
                ->where(\Latitude\QueryBuilder\field('name')->eq($name))
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