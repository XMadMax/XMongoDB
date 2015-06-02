# Advanced operations

## Advanced Where

### Operators !=, <, >, <=, >=
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

  - flags:
    * i = case insensitive (default)
    * m = multiline
    * x = can contain comments
    * l = locale
    * s = dotall, "." matches everything, including newlines
    * u = match unicode

  - enable_start_wildcard
    * If TRUE a starting line character "^" will be prepended, default FALSE

  - enable_end_wildcard
    * If TRUE an ending line character "$" will be appended, default FALSE







## Reconnect to another mongoDB instance
After you have created the config class, you can reuse to another connection...
```php

// $congig is an object of a previous XMonfoDBConfig instance

$config->setDB('myotherdb','myuser','mypass');
$xmongodb_other = new XMongoDB($config);
```
