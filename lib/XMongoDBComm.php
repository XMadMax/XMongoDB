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

namespace XMongoDB\lib;

use XMongoDB\lib\XMongoDBCursor;
use XMongoDB\lib\XMongoDBDriver;

/**
 * XMongoDBComm
 *
 * @since v1.0.0
 */
class XMongoDBComm extends XMongoDBDriver
{

    protected $config;

    /**
     * Construct a new XMongoDBComm
     *
     * @since v1.0.0
     */
    public function __construct(XMongoDBConfig $config)
    {
        $this->config = $config;
        parent::__construct($config);
    }

    /**
     *   Runs a MongoDB command (such as GeoNear).
     * 	See the MongoDB documentation for more usage scenarios:
     * 	http://dochub.mongodb.org/core/commands
     *   @usage : $this->xmongodb->command(array('geoNear'=>'buildings', 'near'=>array(53.228482, -0.547847), 'num' => 10, 'nearSphere'=>true));
     *   @since v1.0.0
     */
    public function command($query = array())
    {
        try {
            $run = $this->db->command($query);
            return $run;
        } catch (\MongoCursorException $e) {
            throw new \Exception("MongoDB command failed to execute: {$e->getMessage()}", 500, $e);
        }
    }

    /**
     *   Runs a MongoDB Aggregate.
     *  See the MongoDB documentation for more usage scenarios:
     *  http://docs.mongodb.org/manual/core/aggregation
     *   @usage : $this->xmongodb->aggregate('users', array(array('$project' => array('_id' => 1))));
     *   @since v1.0.0
     */
    public function aggregate($collection = "", $opt)
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection selected to insert into", 500, $e);
        }
        try {
            $c = $this->db->selectCollection($collection);
            return $c->aggregate($opt);
        } catch (\MongoException $e) {
            throw new \Exception("MongoDB failed: {$e->getMessage()}", 500, $e);
        }
    }

    /**
     * 	Ensure an index of the keys in a collection with optional parameters. To set values to descending order,
     * 	you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * @usage : $this->xmongodb->create_index($collection, array('first_name' => 'ASC', 'last_name' => -1), array('unique' => TRUE));
     * @since v1.0.0
     */
    public function create_index($collection = "", $keys = array(), $options = array())
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection specified to add index to", 500, $e);
        }
        if (empty($keys) || !is_array($keys)) {
            throw new \Exception("Index could not be created to MongoDB Collection because no keys were specified", 500);
        }
        foreach ($keys as $col => $val) {
            if ($val == -1 || $val === FALSE || strtolower($val) == 'desc') {
                $keys[$col] = -1;
            } else {
                $keys[$col] = 1;
            }
        }
        if ($this->db->{$collection}->createIndex($keys, $options) == TRUE) {
            $this->_clear();
            return $this;
        } else {
            throw new \Exception("An error occured when trying to add an index to MongoDB Collection", 500, $e);
        }
    }

    /**
     * 	Remove an index of the keys in a collection. To set values to descending order,
     * 	you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * @usage : $this->xmongodb->remove_index($collection, array('first_name' => 'ASC', 'last_name' => -1));
     * @since v1.0.0
     */
    public function remove_index($collection = "", $keys = array())
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection specified to remove index from", 500, $e);
        }
        if (empty($keys) || !is_array($keys)) {
            throw new \Exception("Index could not be removed from MongoDB Collection because no keys were specified", 500, $e);
        }
        if ($this->db->{$collection}->deleteIndex($keys, $options) == TRUE) {
            $this->_clear();
            return $this;
        } else {
            throw new \Exception("An error occured when trying to remove an index from MongoDB Collection", 500, $e);
        }
    }

    /**
     * 	Remove all indexes from a collection
     *
     * @since v1.0.0
     */
    public function remove_all_indexes($collection = "")
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection specified to remove all indexes from", 500, $e);
        }
        $this->db->{$collection}->deleteIndexes();
        $this->_clear();
        return $this;
    }

    /**
     * 	List all indexes in a collection
     *
     * @since v1.0.0
     */
    public function list_indexes($collection = "")
    {
        if (empty($collection)) {
            throw new \Exception("No Mongo collection specified to remove all indexes from", 500, $e);
        }
        return $this->db->{$collection}->getIndexInfo();
    }

    /**
     * 	Get mongo object from database reference using MongoDBRef
     *
     * @usage : $this->xmongodb->get_dbref($object);
     * @since v1.0.0
     */
    public function get_dbref($obj)
    {
        if (empty($obj) OR ! isset($obj)) {
            throw new \Exception('To use MongoDBRef::get() ala get_dbref() you must pass a valid reference object', 500, $e);
        }

        if ($this->config->return == 'object') {
            return (object) \MongoDBRef::get($this->db, $obj);
        } else {
            return (array) \MongoDBRef::get($this->db, $obj);
        }
    }

    /**
     * 	Create mongo dbref object to store later
     *
     * @usage : $this->xmongodb->create_dbref($collection, $id);
     * @since v1.0.0
     */
    public function create_dbref($collection = "", $id = "", $database = FALSE)
    {
        if (empty($collection)) {
            throw new \Exception("In order to retreive documents from MongoDB, a collection name must be passed", 500, $e);
        }
        if (empty($id) OR ! isset($id)) {
            throw new \Exception('To use MongoDBRef::create() ala create_dbref() you must pass a valid id field of the object which to link', 500, $e);
        }

        $db = $database ? $database : $this->db;

        if ($this->config->return == 'object') {
            return (object) \MongoDBRef::create($collection, $id, $db);
        } else {
            return (array) \MongoDBRef::get($this->db, $obj);
        }
    }

    /**
     * 	Get the documents where the value of a $field is greater than $x
     *  @since v1.0.0
     */
    public function where_gt($field = "", $x = "")
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is greater than or equal to $x
     *  @since v1.0.0
     */
    public function where_gte($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is less than $x
     *  @since v1.0.0
     */
    public function where_lt($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$lt'] = $x;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is less than or equal to $x
     *  @since v1.0.0
     */
    public function where_lte($field = "", $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$lte'] = $x;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is between $x and $y
     *  @since v1.0.0
     */
    public function where_between($field = "", $x, $y)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is between but not equal to $x and $y
     *  @since v1.0.0
     */
    public function where_between_ne($field = "", $x, $y)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return $this;
    }

    /**
     *  Get the documents where the value of a $field is not equal to $x
     *  @since v1.0.0
     */
    public function where_ne($field = '', $x)
    {
        $this->_where_init($field);
        $this->wheres[$field]['$ne'] = $x;
        return $this;
    }

    /**
     *  Get the documents nearest to an array of coordinates (collection must have a geospatial index)
     *  @since v1.0.0
     */
    function where_near($field = '', $co = array())
    {
        $this->__where_init($field);
        $this->where[$field]['$near'] = $co;
        return $this;
    }

    /**
     *  Increments the value of a field
     *  @since v1.0.0
     */
    public function inc($fields = array(), $value = 0)
    {
        $this->_update_init('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     *  Decrements the value of a field
     *  @since v1.0.0
     */
    public function dec($fields = array(), $value = 0)
    {
        $this->_update_init('$inc');
        if (is_string($fields)) {
            $this->updates['$inc'][$fields] = $value*-1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$inc'][$field] = $value*-1;
            }
        }
        return $this;
    }

    /**
     *  Unset the value of a field(s)
     *  @since v1.0.0
     */
    public function unset_field($fields)
    {
        $this->_update_init('$unset');
        if (is_string($fields)) {
            $this->updates['$unset'][$fields] = 1;
        } elseif (is_array($fields)) {
            foreach ($fields as $field) {
                $this->updates['$unset'][$field] = 1;
            }
        }
        return $this;
    }

    /**
     *  Adds value to the array only if its not in the array already
     *
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->addtoset('tags', 'php')->update('blog_posts');
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->addtoset('tags', array('php', 'codeigniter', 'mongodb'))->update('blog_posts');
     *   @since v1.0.0
     */
    public function addtoset($field, $values)
    {
        $this->_update_init('$addToSet');
        if (is_string($values)) {
            $this->updates['$addToSet'][$field] = $values;
        } elseif (is_array($values)) {
            $this->updates['$addToSet'][$field] = array('$each' => $values);
        }
        return $this;
    }

    /**
     * 	Pushes values into a field (field must be an array)
     *
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->push('comments', array('text'=>'Hello world'))->update('blog_posts');
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->push(array('comments' => array('text'=>'Hello world')), 'viewed_by' => array('Alex')->update('blog_posts');
     * @since v1.0.0
     */
    public function push($fields, $value = array())
    {
        $this->_update_init('$push');
        if (is_string($fields)) {
            $this->updates['$push'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$push'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * 	Pushes  ALL values into a field (field must be an array)
     *
     * @since v1.0.0
     */
    public function push_all($fields, $value = array())
    {
        $this->_update_init('$pushAll');
        if (is_string($fields)) {
            $this->updates['$pushAll'][$fields] = $value;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->updates['$pushAll'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     * 	Pops the last value from a field (field must be an array
     *
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->pop('comments')->update('blog_posts');
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->pop(array('comments', 'viewed_by'))->update('blog_posts');
     *   @since v1.0.0
     */
    public function pop($field)
    {
        $this->_update_init('$pop');
        if (is_string($field)) {
            $this->updates['$pop'][$field] = -1;
        } elseif (is_array($field)) {
            foreach ($field as $pop_field) {
                $this->updates['$pop'][$pop_field] = -1;
            }
        }
        return $this;
    }

    /**
     * Removes by an array by the value of a field
     *
     * 	@usage: $this->xmongodb->pull('comments', array('comment_id'=>123))->update('blog_posts');
     *  @since v1.0.0
     */
    public function pull($field = "", $value = array())
    {
        $this->_update_init('$pull');
        $this->updates['$pull'] = array($field => $value);
        return $this;
    }

    /**
     * Removes ALL by an array by the value of a field
     *
     *  @since v1.0.0
     */
    public function pull_all($field = "", $value = array())
    {
        $this->_update_init('$pullAll');
        $this->updates['$pullAll'] = array($field => $value);
        return $this;
    }

    /**
     * Rename a field
     *
     *  @since v1.0.0
     */
    public function rename_field($old, $new)
    {
        $this->_update_init('$rename');
        $this->updates['$rename'][] = array($old => $new);
        return $this;
    }

    public function distinct($collection = "", $field = "")
    {
        if (empty($collection)) {
            throw new \Exception("A collection name must to be specified", 1001, $e);
        }
        if ($this->debug) {
            $time_start = microtime(TRUE);
            $query = 'SELECT DISTINCT ' . $field . '
                        FROM ' . $collection . ' 
                            WHERE ' . http_build_query($this->wheres) . ' 
                        ';
        }

        $cursor = $this->db->selectCollection($collection);
        $result = $cursor->distinct($field, $this->wheres);

        if ($this->debug) {
            $time_end = microtime(TRUE);
            $time_total = number_format($time_end - $time_start, 6);
            $this->debugTrace[] = array('head' => "XMongoQuery ($time_total)", 'query' => $query);
        }

        return $result;
    }

    /**
     * Get the documents based upon the passed parameters
     *
     * @since v1.0.0
     */
    public function get($collection = "", $limit = FALSE, $offset = FALSE)
    {
        if (empty($collection)) {
            throw new \Exception("A collection name must to be specified", 1001, $e);
        }

        if ($this->debug) {
            $time_start = microtime(TRUE);
            $query = 'SELECT ' . (count($this->selects) > 0 ? implode(',', $this->selects) : '*') . '
                        FROM ' . $collection . ' 
                            WHERE ' . http_build_query($this->wheres) . ' 
                                ORDER BY ' . http_build_query($this->sorts) . ' 
                        ';
        }

        $cursor = $this->db->selectCollection($collection)->find($this->wheres, $this->selects);
        $XMongoDBCursor = new XMongoDBCursor($cursor);

        $this->limit = ($limit !== FALSE && is_numeric($limit)) ? $limit : $this->limit;
        if ($this->limit !== FALSE) {
            $XMongoDBCursor->limit($this->limit);
        }

        $this->offset = ($offset !== FALSE && is_numeric($offset)) ? $offset : $this->offset;
        if ($this->offset !== FALSE) {
            $XMongoDBCursor->skip($this->offset);
        }

        if (!empty($this->sorts) && count($this->sorts) > 0) {
            $XMongoDBCursor->sort($this->sorts);
        }

        if ($this->debug) {
            $query .= '        LIMIT ' . $this->limit . ' 
                                OFFSET ' . $this->offset . ' 
                            ';

            $time_end = microtime(TRUE);
            $time_total = number_format($time_end - $time_start, 6);
            $this->debugTrace[] = array('head' => "XMongoQuery ($time_total)", 'query' => $query);
        }

        $this->_clear();
        return $XMongoDBCursor;
    }

    /**
     * Get the documents based upon the passed parameters
     *
     * @since v1.0.0
     */
    public function get_where($collection = "", $where = array(), $limit = FALSE, $offset = FALSE)
    {
        return $this->where($where)->get($collection, $limit, $offset);
    }

    /**
     * Determine which fields to include (_id is always returned)
     *
     * @since v1.0.0
     */
    public function select($includes = array())
    {
        if (!is_array($includes)) {
            $includes = array();
        }
        if (!empty($includes)) {
            foreach ($includes as $col) {
                $this->selects[$col] = TRUE;
            }
        }
        return $this;
    }

    /**
     * Where clause:
     *
     * Passa an array of field=>value, every condition will be merged in AND statement
     * e.g.:
     * $this->xmongodb->where(array('foo'=> 'bar', 'user'=>'arny')->get("users")
     *
     * if you need more complex clause you can pass an array composed exactly like mongoDB needs, followed by a boolean TRUE parameter.
     * e.g.:
     * $where_clause = array(
     * 						'$or'=>array(
     * 							array("user"=>'arny'),
     * 							array("facebook.id"=>array('$gt'=>1,'$lt'=>5000)),
     * 							array('faceboo.usernamek'=>new MongoRegex("/^arny.$/"))
     * 	 					),
     * 						email"=>"a.arnodo@gmail.com"
     * 					);
     *
     *
     * $this->xmongodb->where($where_clause, TRUE)->get("users")
     *
     * @since v1.0.0
     *
     *
     */
    public function where($wheres = array(), $native = FALSE)
    {
        if ($native === TRUE && is_array($wheres)) {
            $this->wheres = $wheres;
        } elseif (is_array($wheres)) {
            foreach ($wheres as $where => $value) {
                $this->_where_init($where);
                $this->wheres[$where] = $value;
            }
        }
        return $this;
    }

    /**
     * Get the documents where the value of a $field may be something else
     *
     * @since v1.0.0
     */
    public function or_where($wheres = array())
    {
        $this->_where_init('$or');
        if (is_array($wheres) && count($wheres) > 0) {
            foreach ($wheres as $wh => $val) {
                $this->wheres['$or'][] = array($wh => $val);
            }
        }
        return $this;
    }

    /**
     * Get the documents where the value of a $field is in a given $in array().
     *
     * @since v1.0.0
     */
    public function where_in($field = "", $in = array())
    {
        $this->_where_init($field);
        $this->wheres[$field]['$in'] = $in;
        return $this;
    }

    /**
     * Get the documents where the value of a $field is not in a given $in array().
     *
     * @since v1.0.0
     */
    public function where_not_in($field = "", $in = array())
    {
        $this->_where_init($field);
        $this->wheres[$field]['$nin'] = $in;
        return $this;
    }

    /**
     *
     * 	Get the documents where the (string) value of a $field is like a value. The defaults
     * 	allow for a case-insensitive search.
     *
     *
     */
    public function like($field = "", $value = "", $flags = "i", $disable_start_wildcard = FALSE, $disable_end_wildcard = FALSE)
    {
        $field = (string) trim($field);
        $this->_where_init($field);
        $value = (string) trim($value);
        $value = quotemeta($value);


        $value = $this->foreignChars($value);

        if ($disable_start_wildcard === TRUE) {
            $value = "^" . $value;
        }
        if ($disable_end_wildcard === TRUE) {
            $value .= "$";
        }
        $regex = "/$value/$flags";
        $this->wheres[$field] = new \MongoRegex($regex);
        return $this;
    }

    /**
     * The same as the aboce but multiple instances are joined by OR:
     *
     * @since v1.0.0
     */
    public function or_like($field, $like = array(), $flags = "i")
    {
        $this->_where_init('$or');
        if (is_array($like) && count($like) > 0) {
            foreach ($like as $admitted) {
                $admitted = $this->foreignChars(quotemeta( (string) trim($admitted) ));
                $this->wheres['$or'][] = array($field => new \MongoRegex("/$admitted/$flags"));
            }
        } else {
            $like = $this->foreignChars(quotemeta( (string) trim($like) ));
            $this->wheres['$or'][] = array($field => new \MongoRegex("/$like/$flags"));
        }
        return $this;
    }

    /**
     * The same as the aboce but multiple instances are joined by NOT LIKE:
     *
     * @since v1.0.0
     */
    public function not_like($field, $like = array())
    {
        $this->_where_init($field);
        if (is_array($like) && count($like) > 0) {
            foreach ($like as $admitted) {
                $admitted = $this->foreignChars(quotemeta( (string) trim($admitted) ));
                $this->wheres[$field]['$nin'][] = new \MongoRegex("/$admitted/");
            }
        }
        return $this;
    }

    /**
     *
     * 	Sort the documents based on the parameters passed. To set values to descending order,
     * 	you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * 	@usage : $this->xmongodb->order_by(array('name' => 'ASC'))->get('users');
     *  @since v1.0.0
     */
    public function order_by($fields = array())
    {
        foreach ($fields as $field => $val) {
            if ($val == -1 || $val === FALSE || strtolower($val) === 'desc') {
                $this->sorts[$field] = -1;
            }
            if ($val == 1 || $val === TRUE || strtolower($val) === 'asc') {
                $this->sorts[$field] = 1;
            }
        }

        return $this;
    }

    /**
     *
     * 	Count all the documents in a collection
     *
     *  @usage : $this->xmongodb->count_all('users');
     *  @since v1.0.0
     */
    public function count_all($collection = "")
    {
        if (empty($collection)) {
            throw new \Exception("The collection name must be specified", 1001, $e);
        }

        $cursor = $this->db->selectCollection($collection)->find();
        $XMongoDBCursor = new XMongoDBCursor($cursor);
        $count = $XMongoDBCursor->count(TRUE);
        $this->_clear();
        return $count;
    }

    /**
     *
     * 	Count the documents based upon the passed parameters
     *
     *  @since v1.0.0
     */
    public function count_all_results($collection = "")
    {
        if (empty($collection)) {
            throw new \Exception("The collection name must be specified", 1001, $e);
        }

        $cursor = $this->db->selectCollection($collection)->find($this->wheres);
        $XMongoDBCursor = new XMongoDBCursor($cursor);
        if ($this->limit !== FALSE) {
            $XMongoDBCursor->limit($this->limit);
        }
        if ($this->offset !== FALSE) {
            $XMongoDBCursor->skip($this->offset);
        }
        $this->_clear();
        return $XMongoDBCursor->count(TRUE);
    }

    /**
     *
     * 	Insert a new document into the passed collection
     *
     *  @since v1.0.0
     */
    public function insert($collection = "", $insert = array())
    {
        if (empty($collection)) {
            throw new \Exception("The collection name must be specified", 1001, $e);
        }

        if (count($insert) == 0) {
            throw new \Exception("No values specified or not an array of values", 1002, $e);
        }
        $this->_inserted_id = FALSE;
        try {
            $query = $this->db->selectCollection($collection)->insert($insert, array("w" => $this->querysafety));
            if (isset($insert->_id)) {
                $this->_inserted_id = $insert->_id;
                return TRUE;
            } elseif (isset($insert['_id'])) {
                $this->_inserted_id = $insert['_id'];
                return TRUE;
            } else {
                return FALSE;
            }
        } catch (MongoException $e) {
            throw new \Exception("MongoException: Insert of data into MongoDB failed: {$e->getMessage()}", 1003, $e);
        } catch (MongoCursorException $e) {
            throw new \Exception("MongoCursorException: Insert of data into MongoDB failed: {$e->getMessage()}", 1004, $e);
        }
    }

    /**
     *
     * 	Insert a multiple new document into the passed collection
     *
     *  @since v1.0.0
     */
    public function insert_batch($collection = "", $insert = array())
    {
        if (empty($collection)) {
            throw new \Exception("A collection name must to be specified", 1001, $e);
        }
        if (count($insert) == 0) {
            throw new \Exception("Nothing to insert into Mongo collection or insert is not an array", 1002, $e);
        }
        try {
            $query = $this->db->selectCollection($collection)->batchInsert($insert, array("w" => $this->querysafety));
            if (is_array($query)) {
                return $query["err"] === NULL;
            } else {
                return $query;
            }
        } catch (MongoException $e) {
            throw new \Exception("MongoException: Insert of data into MongoDB failed: {$e->getMessage()}", 1005, $e);
        } catch (MongoCursorException $e) {
            throw new \Exception("MongoCursorException: Insert of data into MongoDB failed: {$e->getMessage()}", 1006, $e);
        } catch (MongoCursorTimeoutException $e) {
            throw new \Exception("MongoCursorTimeoutException: Insert of data into MongoDB failed: {$e->getMessage()}", 1007, $e);
        }
    }

    /**
     *
     * Sets a field to a value
     *
     * 	@usage: $this->xmongodb->where(array('blog_id'=>123))->set(array('posted'=>1)->update('users');
     *   @since v1.0.0
     */
    public function set($fields = array())
    {
        if (is_array($fields)) {
            $this->_update_init('$set');
            foreach ($fields as $field => $value) {
                $this->updates['$set'][$field] = $value;
            }
        }
        return $this;
    }

    /**
     *
     * Update a single document
     *
     *   @since v1.0.0
     */
    public function update($collection = "", $data = array(), $options = array())
    {
        if (empty($collection)) {
            throw new \Exception("The collection name must be specified", 1001, $e);
        }
        if (is_array($data) && count($data) > 0) {
            $this->_update_init('$set');
            $this->updates['$set'] += $data;
        }
        if (count($this->updates) == 0) {
            throw new \Exception("Nothing to update into Mongo collection or update is not an array", 1008, $e);
        }
        try {
            $options = array_merge(array("w" => $this->querysafety, 'multiple' => FALSE), $options);
            $this->db->selectCollection($collection)->update($this->wheres, $this->updates, $options);
            $this->_clear();
            return TRUE;
        } catch (MongoCursorException $e) {
            throw new \Exception("MongoCursorException: Update of data into MongoDB failed: {$e->getMessage()}", 1009, $e);
        } catch (MongoCursorException $e) {
            throw new \Exception("MongoCursorException: Update of data into MongoDB failed: {$e->getMessage()}", 1010, $e);
        } catch (MongoCursorTimeoutException $e) {
            throw new \Exception("MongoCursorTimeoutException: Update of data into MongoDB failed: {$e->getMessage()}", 1011, $e);
        }
    }

    /**
     *
     * Update more than one document
     *
     *   @since v1.0.0
     */
    public function update_batch($collection = "", $data = array())
    {
        return $this->update($collection, $data, array('multiple' => TRUE));
    }

    /**
     *
     * Delete document from the passed collection based upon certain criteria
     *
     *   @since v1.0.0
     */
    public function delete($collection = "", $options = array())
    {
        if (empty($collection)) {
            throw new \Exception("The collection name must be specified", 1001, $e);
        }
        try {
            $options = array_merge(array("w" => $this->querysafety), $options);
            $this->db->selectCollection($collection)->remove($this->wheres, $options);
            $this->_clear();
            return TRUE;
        } catch (MongoCursorException $e) {
            throw new \Exception("MongoCursorException: Delete of data into MongoDB failed: {$e->getMessage()}", 1012, $e);
        } catch (MongoCursorTimeoutException $e) {
            throw new \Exception("MongoCursorTimeoutException: Delete of data into MongoDB failed: {$e->getMessage()}", 1013, $e);
        }
    }

    /**
     *
     * Delete more than one document
     *
     *   @since v1.3.0
     */
    public function delete_one($collection = "", $options = array())
    {
        return $this->delete($collection, array('justOne' => FALSE));
    }

    /**
     *
     * Limit results
     *
     *   @since v1.1.0
     */
    public function limit($limit = FALSE)
    {
        if ($limit && is_numeric($limit)) {
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     *
     * Returns the last inserted document's id
     *
     *   @since v1.1.0
     */
    public function insert_id()
    {
        return $this->_inserted_id;
    }

}
