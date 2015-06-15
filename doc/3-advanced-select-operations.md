# Advanced Select Operations

## Where Options

### Operators !=, <, >, <=, >=

References: http://docs.mongodb.org/manual/reference/operator/query-comparison/

#### Where Not Equal
```php
$xmongodb->where_ne('myfield1','value1');
```
#### Where Lower Than
```php
$xmongodb->where_lt('myfield1','value1');
```
#### Where Lower Or Equal Than
```php
$xmongodb->where_lte('myfield1','value1');
```
#### Where Greather Than
```php
$xmongodb->where_gt('myfield1','value1');
```
#### Where Greather Or Equal Than
```php
$xmongodb->where_gte('myfield1','value1');
```
### Where In
Finds in 'myfield1' a set of possible values
```php
$xmongodb->where_in('myfield1', array('value1','value2','value3'));
```

### Where Not In
Finds in 'myfield1' where a set of possible values is not found
```php
$xmongodb->where_not_in('myfield1', array('value1','value2','value3'));
```

### Where Between
Finds in 'myfield1' between two values (including equal)
```php
$xmongodb->where_between('myfield1', $x, $y);
```
For char dates
```php
$xmongodb->where_between('mydate1', '2015-04-29 00:00:00', '2015-04-29 23:59:59');
```
For ISODates, must to be converted to ISODate 
```php
$xmongodb->where_between('mydate1', 'ISODate("2015-04-29T00:00:00.000Z")', 'ISODate("2015-04-29T23:59:59.000Z")');
```


### Where Not Between
Finds in 'myfield1' between two values (not including equal)
```php
$xmongodb->where_between_ne('myfield1', $x, $y);
```

## Like Options

References: http://docs.mongodb.org/manual/reference/operator/query/regex/

#### Normal Like
```php
$xmongodb->like('myfield1', 'value1', $flags, $disable_start_wildcard , $disable_end_wildcard);
```
Params:

  - flags:
    * i = case insensitive (default)
    * m = multiline
    * x = can contain comments
    * l = locale
    * s = dotall, "." matches everything, including newlines
    * u = match unicode

  - disable_start_wildcard
    * If TRUE a starting line character "^" will be prepended, default FALSE

  - disable_end_wildcard
    * If TRUE an ending line character "$" will be appended, default FALSE

#### Not Like
```php
$xmongodb->not_like('myfield1', array('value1','value2','value3'));
```
Finds in 'myfield1' that not contains 'value1', 'value2' neither 'value3'

## Other Options

### Distinct
Distinct can only select only one unique field or subdocument. 
If the field is a subdocument will return all different subdocuments.

Returns an array of distinct values.

```php
$xmongodb->where(array('myfield1'=>'myvalue1'))->distinct('myfield2');
// OR
$xmongodb->distinct('myfield2',array('myfield1'=>'myvalue1'));
```
Return an array of differentes 'myfield2' found.

References: http://docs.mongodb.org/manual/reference/operator/query/regex/

### Count
Count all documents in a collection
```php
$xmongodb->count('collection');
```
Returns an integer

### Count All Results
Count all documents in a collection using where, limit and offset
```php
$xmongodb->where(array('myfield1'=>'myvalue1'))->count_all('collection');
```
Returns an integer

References: http://docs.mongodb.org/manual/reference/method/db.collection.count/


