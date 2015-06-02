# Basic Operations
## Connect
```php
// Create the config class
$config = new XMongoDBConfig('localhost',27017,'mydb','myuser','mypass');
// Connect MongoDB
$xmongodb = new XMongoDB($config);
```
Now, you can select collection records
```php
$cursor = $xmongodb->where(array('myfield1' => 'myvalue1', 'myfield2' => 'myvalue2')->get('mycollection');
```
The result is a MongoCursor object, and can be manipulated:
```php
// Limit to 10 records max
$cursor->limit(10);
// Skip first 20 records
$cursor->skip(20);
// Order by name
$cursor->order_by(array('name' => 'asc'));
```

Retrieve result and other info about the result query
```php
// Make select
$cursor = $xmongodb->where(array('myfield1' => 'myvalue1', 'myfield2' => 'myvalue2')->get('mycollection');
// Retrive an array of objects
$result = $cursor->result();
// Retrive an array of arrays
$result = $cursor->result_array();
// Get the total of rows that accomplish conditions
$totalrows = $cursor->total_rows();
// Get only num rows retrieved
$numrows = $cursor->num_rows();
```
