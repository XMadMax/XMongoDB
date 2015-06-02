# XMongoDB

A PHP ORM library to handle opperations with MongoDB

  - Select, Distinct, Where, OrderBy, Limit, Offset, Insert, Update, Delete, Create, Pop, Pull, Push, Aggregate, Indexes, Commands, AddtoSet, Inc, Dec
  - All commands like ORM, example:  
        $result = $XMongodDB->->where(array('email' => 'email@myemail.com))->get('customers');
  - Where:
    * Between
    * Greather than
    * Lower than
    * Greather or equal than
    * Lower or equal than
    * Not equal
    * Equal
    * Near
    * Like
    * Not Like
    * All can be mixed with AND/OR
  - OrderBy ASC / DESC
  - Limit and Offset

Getting Started Guide
---------------------

  * [Chapter 1 - Installation](/doc/1-installation.md)
  * [Chapter 2 - Basic operations](/doc/2-basic-operations.md)
  * [Chapter 3 - Advanced operations](/doc/3-advanced-operations.md)
  * [Chapter 4 - Execptions](/doc/10-exceptions.md)


