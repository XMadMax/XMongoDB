<?php

/**
 * XMongoDB Library
 *
 * A query builder library to interact with MongoDB database.
 * http://www.mongodb.org
 *
 * @package		MongoDB
 * @author		Xavier Perez | xperez@4amics.com
 * @copyright           Copyright (c) 2012, Alessandro Arnodo.
 * @copyright           Copyright (c) 2015, Xavier Perez.
 * @note                Thanks to original script from Alessandro Arnodo
 * @license		http://www.opensource.org/licenses/mit-license.php
 * @link
 * @version		Version 1.0.0
 */
/**
 * Thanks to Alessandro Arnodo
 */

namespace XMongoDB\lib;

use XMongoDB\lib\XMongoDBConfig;

/**
 * XMongoDBDriver
 * @uses This base class provide a connection object to interact with MongoDB
 * @since v1.0.0
 *
 */
class XMongoDBDriver
{

    protected $connection;
    protected $db;
    private $connection_string;
    private $host;
    private $port;
    private $user;
    private $pass;
    protected $dbname;
    protected $query_safety;
    protected $selects = array();
    protected $wheres = array();
    protected $sorts = array();
    protected $updates = array();
    protected $limit = FALSE;
    protected $offset = FALSE;

    /**
     * Connect to MongoDB
     *
     * @since v1.0.0
     */
    public function __construct($config)
    {
        if (!class_exists('Mongo')) {
            throw new \Exception("The MongoDB PECL extension has not been installed or enabled", 1021, $e);
        }
        $this->connection_string($config);
        $this->connect();
    }

    /**
     * Switch DB
     *
     * @since v1.0.0
     */
    public function switch_db($database = '')
    {
        if (empty($database)) {
            throw new \Exception("To switch MongoDB databases, a new database name must be specified", 1022, $e);
        }
        $this->dbname = $database;
        try {
            $this->db = $this->connection->{$this->dbname};
            return (TRUE);
        } catch (Exception $e) {
            throw new \Exception("Unable to switch Mongo Databases: {$e->getMessage()}", 1023, $e);
        }
    }

    /**
     * Drop DB
     *
     * @since v1.0.0
     */
    public function drop_db($database = '')
    {
        if (empty($database)) {
            throw new \Exception('Failed to drop MongoDB database because name is empty', 1024, $e);
        } else {
            try {
                $this->connection->{$database}->drop();
                return TRUE;
            } catch (Exception $e) {
                throw new \Exception("Unable to drop Mongo database `{$database}`: {$e->getMessage()}", 1025, $e);
            }
        }
    }

    /**
     * Drop collection
     *
     * @since v1.0.0
     */
    public function drop_collection($db = "", $col = "")
    {
        if (empty($db)) {
            throw new \Exception('Failed to drop MongoDB collection because database name is empty', 1026, $e);
        }
        if (empty($col)) {
            throw new \Exception('Failed to drop MongoDB collection because collection name is empty', 1027, $e);
        } else {
            try {
                $this->connection->{$db}->{$col}->drop();
                return TRUE;
            } catch (Exception $e) {
                throw new \Exception("Unable to drop Mongo collection '$col': {$e->getMessage()}", 1028, $e);
            }
        }

        return $this;
    }

    /**
     * Copy collection
     * 
     * @param type $fromCollection
     * @param type $toCollection
     */
    public function copy_collection($fromCollection, $toCollection, $timeout = -1)
    {
        $this->db->command(array(
            "eval" => new \MongoCode("function(){
                    db['" . $fromCollection . "'].copyTo('" . $toCollection . "')
                };"
            )
                ), array('socketTimeoutMS' => $timeout));
    }

    public function rename_collection($fromCollection, $toCollection, $timeout = -1)
    {
        $this->db->command(array(
            "eval" => new \MongoCode("function(){
                    db['" . $fromCollection . "'].renameCollection('" . $toCollection . "',true)
                };"
            )
                ), array('socketTimeoutMS' => $timeout));
    }

    /**
     * Connect to MongoDB
     *
     * @since v1.0.0
     */
    private function connect()
    {
        $options = $this->options;

        $tries = 0;
        while ($tries < 6) {
            try {
                $this->connection = new \MongoClient($this->connection_string, $options);
                $this->db = $this->connection->{$this->dbname};
                return $this;
            } catch (\MongoConnectionException $e) {
                if ($tries < 5) {
                    $tries++;
                } else {
                    throw new \Exception("Unable to connect to MongoDB: {$e->getMessage()}", 1000, $e);
                }
            }
        }
    }

    /**
     * Create connection string
     *
     * @since v1.0.0
     */
    private function connection_string($config)
    {
        $this->host = trim($config->host);
        $this->port = trim($config->port);
        $this->user = trim($config->user);
        $this->pass = trim($config->pass);
        $this->dbname = trim($config->db);
        $this->query_safety = $config->querysafety;
        $this->options = $config->options;
        $dbhostflag = (bool) $config->dbflag;

        $connection_string = "mongodb://";

        if (empty($this->host)) {
            throw new \Exception("The Host must be set to connect to MongoDB", 1029, $e);
        }

        if (empty($this->dbname)) {
            throw new \Exception("The Database must be set to connect to MongoDB", 1030, $e);
        }

        if (!empty($this->user) && !empty($this->pass)) {
            $connection_string .= "{$this->user}:{$this->pass}@";
        }

        if (isset($this->port) && !empty($this->port)) {
            $connection_string .= "{$this->host}:{$this->port}";
        } else {
            $connection_string .= "{$this->host}";
        }

        if ($dbhostflag === TRUE) {
            $this->connection_string = trim($connection_string) . '/' . $this->dbname;
        } else {
            $this->connection_string = trim($connection_string);
        }
        var_dump($this->connection_string);
    }

    /**
     * Reset class variables
     *
     * Save last query in other vars
     * @since v1.0.0
     */
    protected function _clear()
    {
        $this->_selects = $this->selects;
        $this->_updates = $this->updates;
        $this->_wheres = $this->wheres;
        $this->_limit = $this->limit;
        $this->_offset = $this->offset;
        $this->_sorts = $this->sorts;
        $this->selects = array();
        $this->updates = array();
        $this->wheres = array();
        $this->limit = FALSE;
        $this->offset = FALSE;
        $this->sorts = array();
    }

    /**
     * Initializie where clause for the specified field
     *
     * @since v1.0.0
     */
    protected function _where_init($param)
    {
        if (!isset($this->wheres[$param])) {
            $this->wheres[$param] = array();
        }
    }

    /**
     * Initializie update clause for the specified method
     *
     * @since v1.0.0
     */
    protected function _update_init($method)
    {
        if (!isset($this->updates[$method])) {
            $this->updates[$method] = array();
        }
    }

    /**
     * Handler for exception
     *
     * @since v1.1.0
     */
    protected function _handle_exception($message, $as_object = TRUE)
    {
        if ($as_object) {
            $res = new stdClass();
            $res->has_error = TRUE;
            $res->error_message = $message;
        } else {
            $res = array(
                "has_error" => TRUE,
                "error_message" => $message
            );
        }
        return $res;
    }

}
