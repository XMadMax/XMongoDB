# Advanced operations

## Advanced Where

### Operators != < > <= >=
#### Where Not Equal
```php
$xmongodb->where_ne('myfield1','value1')
```
#### Where Lower Than
```php
$xmongodb->where_lt('myfield1','value1')
```
#### Where Lower Or Equal Than
```php
$xmongodb->where_lte('myfield1','value1')
```
#### Where Greather Than
```php
$xmongodb->where_gt('myfield1','value1')
```
#### Where Greather Or Equal Than
```php
$xmongodb->where_gte('myfield1','value1')
```
### Like
#### Normal Like
```php
$xmongodb->like('myfield1', 'value1', $flags, $enable_start_wildcard , $enable_end_wildcard)
```
Params:
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







## Reconnect to another mongoDB instance
After you have created the config class, you can reuse to another connection...
```php

// $congig is an object of a previous XMonfoDBConfig instance

$config->setDB('myotherdb','myuser','mypass');
$xmongodb_other = new XMongoDB($config);
```
