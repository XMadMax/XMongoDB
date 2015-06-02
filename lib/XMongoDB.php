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

use XMongoDB\lib\XMongoDBConfig;
use XMongoDB\lib\XMongoDBCursor;
use XMongoDB\lib\XMongoDBComm;

/**
 * XMongoDB
 *
 * Methods to interact with MongoDB
 * @since v1.0
 */
class XMongoDB extends XMongoDBComm
{
    private $_inserted_id = FALSE;
    public $debug = FALSE;
    protected $debugTrace;

    /**
     * Construct new XMongo
     *
     * @since v1.0.0
     */
    public function __construct(XMongoDBConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * Fake close function
     *
     */
    public function close()
    {
        
    }

    public function getDebug()
    {
        return $this->debugTrace;
    }
    
    public function clearDebug()
    {
        $this->debugTrace = array();
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
            $this->debugTrace[] = array('head'=>"XMongoQuery ($time_total)",'query' => $query);
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
            $query = 'SELECT ' . (count($this->selects)>0?implode(',', $this->selects):'*') . '
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
            $this->debugTrace[] = array('head'=>"XMongoQuery ($time_total)",'query' => $query);
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
     * where clause:
     *
     * Passa an array of field=>value, every condition will be merged in AND statement
     * e.g.:
     * $this->cimongo->where(array('foo'=> 'bar', 'user'=>'arny')->get("users")
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
     * $this->cimongo->where($where_clause, TRUE)->get("users")
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
     * 	@param $flags
     * 	Allows for the typical regular expression flags:
     * 		i = case insensitive
     * 		m = multiline
     * 		x = can contain comments
     * 		l = locale
     * 		s = dotall, "." matches everything, including newlines
     * 		u = match unicode
     *
     * 	@param $enable_start_wildcard
     * 	If set to anything other than TRUE, a starting line character "^" will be prepended
     * 	to the search value, representing only searching for a value at the start of
     * 	a new line.
     *
     * 	@param $enable_end_wildcard
     * 	If set to anything other than TRUE, an ending line character "$" will be appended
     * 	to the search value, representing only searching for a value at the end of
     * 	a line.
     *
     * 	@usage : $this->cimongo->like('foo', 'bar', 'im', FALSE, TRUE);
     * 	@since v1.0.0
     *
     */
    public function like($field = "", $value = "", $flags = "i", $enable_start_wildcard = FALSE, $enable_end_wildcard = FALSE)
    {
        $field = (string) trim($field);
        $this->_where_init($field);
        $value = (string) trim($value);
        $value = quotemeta($value);


        $value = $this->foreignChars($value);

        if ($enable_start_wildcard === TRUE) {
            $value = "^" . $value;
        }
        if ($enable_end_wildcard === TRUE) {
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
                $this->wheres['$or'][] = array($field => new \MongoRegex("/$admitted/$flags"));
            }
        } else {
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
     * 	@usage : $this->cimongo->order_by(array('name' => 'ASC'))->get('users');
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
     *  @usage : $this->cimongo->count_all('users');
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
     * 	@usage: $this->cimongo->where(array('blog_id'=>123))->set(array('posted'=>1)->update('users');
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
            show_error("MongoCursorTimeoutException: Delete of data into MongoDB failed: {$e->getMessage()}", 1013, $e);
        }
    }

    /**
     *
     * Delete more than one document
     *
     *   @since v1.3.0
     */
    public function delete_batch($collection = "", $options = array())
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
    
    protected function foreignChars($str)
    {
        $str = utf8_encode(strtolower(utf8_decode($str)));

        $foreign_characters = array(
                '/ä|æ|ǽ/' => 'ae',
                '/ö|œ/' => 'oe',
                '/ü/' => 'ue',
                '/Ä/' => 'Ae',
                '/Ü/' => 'Ue',
                '/Ö/' => 'Oe',
                '/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ|Α|Ά|Ả|Ạ|Ầ|Ẫ|Ẩ|Ậ|Ằ|Ắ|Ẵ|Ẳ|Ặ|А/' => 'A',
                '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|α|ά|ả|ạ|ầ|ấ|ẫ|ẩ|ậ|ằ|ắ|ẵ|ẳ|ặ|а/' => 'a',
                '/Б/' => 'B',
                '/б/' => 'b',
                '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
                '/ç|ć|ĉ|ċ|č/' => 'c',
                '/Д/' => 'D',
                '/д/' => 'd',
                '/Ð|Ď|Đ|Δ/' => 'Dj',
                '/ð|ď|đ|δ/' => 'dj',
                '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Ε|Έ|Ẽ|Ẻ|Ẹ|Ề|Ế|Ễ|Ể|Ệ|Е|Э/' => 'E',
                '/è|é|ê|ë|ē|ĕ|ė|ę|ě|έ|ε|ẽ|ẻ|ẹ|ề|ế|ễ|ể|ệ|е|э/' => 'e',
                '/Ф/' => 'F',
                '/ф/' => 'f',
                '/Ĝ|Ğ|Ġ|Ģ|Γ|Г|Ґ/' => 'G',
                '/ĝ|ğ|ġ|ģ|γ|г|ґ/' => 'g',
                '/Ĥ|Ħ/' => 'H',
                '/ĥ|ħ/' => 'h',
                '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|Η|Ή|Ί|Ι|Ϊ|Ỉ|Ị|И|Ы/' => 'I',
                '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|η|ή|ί|ι|ϊ|ỉ|ị|и|ы|ї/' => 'i',
                '/Ĵ/' => 'J',
                '/ĵ/' => 'j',
                '/Ķ|Κ|К/' => 'K',
                '/ķ|κ|к/' => 'k',
                '/Ĺ|Ļ|Ľ|Ŀ|Ł|Λ|Л/' => 'L',
                '/ĺ|ļ|ľ|ŀ|ł|λ|л/' => 'l',
                '/М/' => 'M',
                '/м/' => 'm',
                '/Ñ|Ń|Ņ|Ň|Ν|Н/' => 'N',
                '/ñ|ń|ņ|ň|ŉ|ν|н/' => 'n',
                '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ο|Ό|Ω|Ώ|Ỏ|Ọ|Ồ|Ố|Ỗ|Ổ|Ộ|Ờ|Ớ|Ỡ|Ở|Ợ|О/' => 'O',
                '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ο|ό|ω|ώ|ỏ|ọ|ồ|ố|ỗ|ổ|ộ|ờ|ớ|ỡ|ở|ợ|о/' => 'o',
                '/П/' => 'P',
                '/п/' => 'p',
                '/Ŕ|Ŗ|Ř|Ρ|Р/' => 'R',
                '/ŕ|ŗ|ř|ρ|р/' => 'r',
                '/Ś|Ŝ|Ş|Ș|Š|Σ|С/' => 'S',
                '/ś|ŝ|ş|ș|š|ſ|σ|ς|с/' => 's',
                '/Ț|Ţ|Ť|Ŧ|τ|Т/' => 'T',
                '/ț|ţ|ť|ŧ|т/' => 't',
                '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|Ũ|Ủ|Ụ|Ừ|Ứ|Ữ|Ử|Ự|У/' => 'U',
                '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|υ|ύ|ϋ|ủ|ụ|ừ|ứ|ữ|ử|ự|у/' => 'u',
                '/Ý|Ÿ|Ŷ|Υ|Ύ|Ϋ|Ỳ|Ỹ|Ỷ|Ỵ|Й/' => 'Y',
                '/ý|ÿ|ŷ|ỳ|ỹ|ỷ|ỵ|й/' => 'y',
                '/В/' => 'V',
                '/в/' => 'v',
                '/Ŵ/' => 'W',
                '/ŵ/' => 'w',
                '/Ź|Ż|Ž|Ζ|З/' => 'Z',
                '/ź|ż|ž|ζ|з/' => 'z',
                '/Æ|Ǽ/' => 'AE',
                '/ß/' => 'ss',
                '/Ĳ/' => 'IJ',
                '/ĳ/' => 'ij',
                '/Œ/' => 'OE',
                '/ƒ/' => 'f',
                '/ξ/' => 'ks',
                '/π/' => 'p',
                '/β/' => 'v',
                '/μ/' => 'm',
                '/ψ/' => 'ps',
                '/Ё/' => 'Yo',
                '/ё/' => 'yo',
                '/Є/' => 'Ye',
                '/є/' => 'ye',
                '/Ї/' => 'Yi',
                '/Ж/' => 'Zh',
                '/ж/' => 'zh',
                '/Х/' => 'Kh',
                '/х/' => 'kh',
                '/Ц/' => 'Ts',
                '/ц/' => 'ts',
                '/Ч/' => 'Ch',
                '/ч/' => 'ch',
                '/Ш/' => 'Sh',
                '/ш/' => 'sh',
                '/Щ/' => 'Shch',
                '/щ/' => 'shch',
                '/Ъ|ъ|Ь|ь/' => '',
                '/Ю/' => 'Yu',
                '/ю/' => 'yu',
                '/Я/' => 'Ya',
                '/я/' => 'ya'
        );        
        
        $str = utf8_encode(preg_replace(array_keys($foreign_characters), array_values($foreign_characters), $str));
       
        $ACCENT_STRINGS = 'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ';

        $NO_ACCENT_STRINGS = 'sozsozyyuaaaaaaaceeeeeiiiiidnoooooouuuuysaaaaaaaceeeeeiiiiionoooooouuuuyy';

        $from = str_split(utf8_decode($ACCENT_STRINGS));
        $to = str_split(strtolower($NO_ACCENT_STRINGS));

        $text = utf8_decode($str);

        $regex = array();

        foreach ($to as $key => $value)
        {
            if (isset($regex[$value]))
            {
                $regex[$value] .= $from[$key];
            } else
            {
                $regex[$value] = $value;
            }
        }

        foreach ($regex as $rg_key => $rg)
        {
            $text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
        }

        foreach ($regex as $rg_key => $rg)
        {
            $text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
        }

        return utf8_encode($str);
        
        
    }

}
