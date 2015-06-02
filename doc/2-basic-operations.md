# Basic Operations
## Connect
```php
// Create the config class
$config = new XMongoDBConfig('localhost',27017,'mydb','myuser','mypass');

// Connect MongoDB
$xmongodb = new XMongoDB($config);
```
## Select
Select the fields you want to be obtanied from mongoDB collection
```php
$xmongodb->select(array('myfield1','myfield2','myfield3'));
```
## Where
```php
$xmongodb->where(array('myfield1' => 'value1', 'myfield2' => 'value2');
```
Important: each value must to match the type (numeric or string) of the value inserted in the mongodb collection.

See advanced where for more options...[Chapter 3 - Advanced operations](/doc/3-advanced-operations.md)
## Run Query
```php
$cursor = $xmongodb->get('mycollection');
```
The result is a MongoCursor object
## Manipulating cursors
Once you has obtained a cursor, you can limit, skip or order results:
```php
// Limit to 10 records max
$cursor->limit(10);

// Skip first 20 records
$cursor->skip(20);

// Order by name
$cursor->order_by(array('name' => 'asc'));
```

## Getting cursors 
Retrieve results and other info about the result query
```php
// Retrive an array of objects
$result = $cursor->result();

// Retrive an array of arrays
$result = $cursor->result_array();

// Get the total of rows that accomplish conditions
$totalrows = $cursor->total_rows();

// Get only num rows retrieved
$numrows = $cursor->num_rows();
```


