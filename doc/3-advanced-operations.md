# Advanced operations

## Reconnect to another mongoDB instance
After you have created the config class, you can reuse to another connection...
```php

// $congig is an object of a previous XMonfoDBConfig instance

$config->setDB('myotherdb','myuser','mypass');
$xmongodb_other = new XMongoDB($config);
```
