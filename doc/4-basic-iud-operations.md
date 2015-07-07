# Basic Insert / Update / Delete Operations

## Insert Options
### Insert one document
If you are inserting only one document:

```php
$xmongodb->insert('collection', 
    array('field1' => 'value1',
          'field2' => 'value2',
          'field3' => 'value3'
        )
);
```

References: http://docs.mongodb.org/manual/reference/method/db.collection.insert/

### Insert multiple documents
If you are inserting a group of documents:

```php
$xmongodb->insert_batch('collection', array(
    0 => array('field1' => 'value11','field2' => 'value21','field3' => 'value31'), 
    1 => array('field1' => 'value12','field2' => 'value22','field3' => 'value32')
    )
);
```

References: http://docs.mongodb.org/manual/reference/method/db.collection.insert/

## Update Options
### Update a single document
Before an update, you must to specify a where condition:

```php
$xmongodb->where(array('field1'=>'value1'))->update('collection', 
        array('field2' => 'value2',
              'field3' => 'value3'
        ),
        $options
);
```
This method only will update first collection found in the where condition. 
Specify an unique key to be sure you update the correct document.

  - $options:
    * array('upsert' => true)     Create a new document if nothing found

References : http://docs.mongodb.org/manual/reference/method/db.collection.update/

### Update multiple documents

```php
$xmongodb->where(array('field1'=>'value1'))->update_batch('collection', 
        array('field2' => 'value2',
              'field3' => 'value3'
        )
);
```
References : http://docs.mongodb.org/manual/reference/method/db.collection.update/

### Update using SET


```php
$xmongodb->set(array('field2' => 'value2', 'field3' => 'value3'))
         ->where(array('field1'=>'value1'))
         ->update('collection'); 
```

References: http://docs.mongodb.org/manual/reference/operator/update/set/


## Delete Options
### Delete a single document

```php
$xmongodb->where(array('field1'=>'value1'))->delete_one('collection');
```

### Delete multiple documents
```php
$xmongodb->where(array('field1'=>'value1'))->delete('collection');
```

References: http://docs.mongodb.org/manual/reference/method/db.collection.remove/

## Reconnect to another mongoDB instance
After you have created the config class, you can reuse to another connection:

```php
// $congig is an object of a previous XMonfoDBConfig instance
$config->setDB('myotherdb','myuser','mypass');
$xmongodb_other = new XMongoDB($config);
```
