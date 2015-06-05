# Other Operations
### Get last inserted ID
```php
$xmongodb->insert_id();
```

### Add values to an existing set 
```php
$xmongodb->addToSet('myfield1',array('myvalue1','myvalue2');
```

### Increment the value of a field
#### Increment only one field
```php
$xmongodb->inc('myfield1',1)
    ->where(array('myfield2' => 'myvalue2'))
    ->update('collection');
```

#### Increment multiple fields
```php
$xmongodb->inc(array('myfield1','myfield2),1)
    ->where(array('myfield3' => 'myvalue3'))
    ->update('collection');
```
References: http://docs.mongodb.org/manual/reference/operator/update/inc/


