<?php

namespace XMongoDB\Examples;

use XMongoDB\lib\XMongoDB;
use XMongoDB\lib\XMongoDBConfig;

header('Content-Type: text/html; charset=utf-8');            
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', true);

require __DIR__."/../lib/XMongoDBDriver.php";
require __DIR__."/../lib/XMongoDBComm.php";
require __DIR__."/../lib/XMongoDBCursor.php";
require __DIR__."/../lib/XMongoDB.php";
require __DIR__."/../lib/XMongoDBConfig.php";

echo "<h1>Remember to load examples (/xmongodb/examples/insertExamples.js) in your mongodb instance</h1>";

class test
{
    protected $xmongoconfig;
    protected $xmongodb;
        
    public function __construct()
    {
        // Set MongoConfig
        $this->xmongoconfig = new XMongoDBConfig('ds036178.mongolab.com',36178,'xmongodb','xmadmax','asklpo6754',true);

        // Load Mongo connection
        $this->xmongodb = new XMongoDB($this->xmongoconfig);
        // Set debug mode
        $this->xmongodb->debug = true;
        
       var_dump($this->xmongodb); exit;
    }
    
    public function test1()
    {
        // SELECT * FROM collection WHERE awards.year = 2001
        $result = $this->xmongodb->where(array('awards.year' => 2001))->get('collection');

        // Show results & debug
        echo "<h2>RESULTS for award->year == 2001 </h2>";
        echo "Found ".$result->total_rows()." records<br />";
        var_dump($result->result());
        echo "<h2>DEBUG</h2>";
        var_dump($this->xmongodb->getDebug());
        echo "<hr>";

    }
    
    public function test2()
    {
        // Clear debug, if not, debug adds every query to the main debug array
        $this->xmongodb->clearDebug();

        // SELECT DISTINCT name.first FROM collection
        $result = $this->xmongodb->like('name.first','john')
                    ->distinct('collection','name');


        // Show results & debug
        echo "<h2>RESULTS for DISTINCT name.first like 'john'</h2>";
        var_dump($result);
        echo "<h2>DEBUG</h2>";
        var_dump($this->xmongodb->getDebug());
    }
}

$test = new test();

$test->test1();
$test->test2();
