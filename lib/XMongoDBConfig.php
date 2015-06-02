<?php
/**
 * XMongoDB Library
 *
 * A query builder library to interact with MongoDB database.
 * http://www.mongodb.org
 *
 * @package		MongoDB
 * @author		Xavier Perez | xperez@4amics.com
 * @copyright           Copyright (c) 2015, Xavier Perez.
 * @license		http://www.opensource.org/licenses/mit-license.php
 * @link
 * @version		Version 1.0.0
 */

namespace XMongoDB\lib;

class XMongoDBConfig
{
    /**
     * Host
     * 
     * Hostname, or IP
    */
    public $host;
    
    /**
     * Port, normally, 27017
    */
    public $port;
    
    /**
     * DB name
    */
    public $db;
    
    /**
     * MongoDB user if enabled security
    */
    public $user;

    /**
     * MongoDB pass if enabled security
    */
    public $pass;

    /**
     * Query safety, is the WriteConcern on insert,update or remove
     * 
     * http://php.net/manual/en/mongo.writeconcerns.php
    */
    public $querysafety=0;
    
    /**
     * Options for MongoClient
     * 
     * http://php.net/manual/es/mongoclient.construct.php
     */
    public $options = array();
    
    /**
     * Return object or array in MonfoDBRef
     * 
     * http://php.net/manual/en/class.mongodbref.php
    */
    public $return='object';
    
    public function __construct($host, $port=27017, $db='', $user='', $pass='', $dbflag = false, $querysafety=0, $return = 'object', $options=array())
    {
        $this->host         = $host;
        $this->port         = $port;
        $this->user         = $user;
        $this->pass         = $pass;
        $this->db           = $db;
        $this->dbflag       = $dbflag;
        $this->querysafety  = $querysafety;
        $this->options      = $options;
        $this->return       = 'object';
        
        return $this;
    }
    
    public function setHost($host,$port)
    {
        $this->host = $host;
        $this->port = $port;
    }
    
    public function setDB($db, $user='', $pass='', $dbflag=false)
    {
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbflag = $dbflag;
    }
    
    public function setQuerySafety($querysafety=0)
    {
        $this->querysafety = $querysafety;
    }
    
    public function setReturn($return='object')
    {
        $this->return = $return;
    }
    
    public function setOptions($options = array())
    {
        $this->options = $options;
    }
}


