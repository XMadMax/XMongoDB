# Other Operations
### Get last inserted ID

```php
$xmongodb->insert_id();
```

### Add values to an existing set 

```php
$xmongodb->addToSet('myfield1',array('myvalue1','myvalue2');
```

References: http://docs.mongodb.org/manual/reference/operator/update/addToSet/

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
References: http://docs.mongodb.org/manual/reference/operator/update/dec/

### Decrement the value of a field
#### Decrement only one field

```php
$xmongodb->dec('myfield1',1)
    ->where(array('myfield2' => 'myvalue2'))
    ->update('collection');
```

#### Decrement multiple fields

```php
$xmongodb->dec(array('myfield1','myfield2),1)
    ->where(array('myfield3' => 'myvalue3'))
    ->update('collection');
```

Now, MongoDB uses $inc function, you must to specify positive values in the $xmongodb->dec, this function converts to negative.

References: http://docs.mongodb.org/manual/reference/operator/update/inc/


#### Pop an element of an array
Removes the first element in an array

```php
$xmongodb->pop('myarray1')
    ->where(array('myfield3' => 'myvalue3'))
    ->update('collection');
```

Removes the first element in all elements specified

```php
$xmongodb->pop(array('myarray1','myarray2')
    ->where(array('myfield3' => 'myvalue3'))
    ->update('collection');
```

References: http://docs.mongodb.org/manual/reference/operator/update/pop/

#### Pull an element of an array

Removes the element of an array that match the value

```php
$xmongodb->pull('myfield1', array('postalcode'=>'08080'))
    ->update('collection');
```

References: http://docs.mongodb.org/manual/reference/operator/update/pull/

#### PullAll elements of an array

The pullAll operator removes all instances of the specified values from an existing array. 
Unlike the pull operator that removes elements by specifying a query, pullAll removes elements that match the listed values.

```php
$xmongodb->pull('myfield1', array('country'=>34, 'city' => 'Barcelona'))
    ->update('collection');
```

References: http://docs.mongodb.org/manual/reference/operator/update/pullAll/

#### Agregations

Aggregations must to be done in array groups, one for each group (sort, project, group)


```php
        // Group by Country, order by Country
        $aggregate[] = 
            array(
                '$sort' => array('name',1)
        );
        $aggregate[] = 
            array(
                '$group' => array(
                    '_id' => array(
                        'Country' => '$country'
                        ),
                    'firstPerson'=> array ('$first','$name'},
                    'lastPerson' => array ('$last','$name'},
                    'count' => array('$sum',1)
                ),
        );
        $result = $xmongodb->aggregate('collection',$aggregate);
```

This will find a number of persons found by country, showing the first and the last name.

